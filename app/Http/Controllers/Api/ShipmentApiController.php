<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Services\ShipmentService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ShipmentApiController extends Controller
{
    public function __construct(
        protected ShipmentService $shipmentService
    ) {
    }

    /**
     * Display a paginated listing of shipments scoped by user role.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 10);
        $shipments = $this->shipmentService->getPaginatedShipmentsForUser($request->user(), $request->all(), $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar pengiriman kargo berhasil diambil.',
            'data' => $shipments->items(),
            'meta' => [
                'current_page' => $shipments->currentPage(),
                'last_page' => $shipments->lastPage(),
                'per_page' => $shipments->perPage(),
                'total' => $shipments->total(),
            ],
            'links' => [
                'first' => $shipments->url(1),
                'last' => $shipments->url($shipments->lastPage()),
                'prev' => $shipments->previousPageUrl(),
                'next' => $shipments->nextPageUrl(),
            ],
        ], 200);
    }

    /**
     * Store a newly created shipment and its manifest items.
     */
    public function store(Request $request): JsonResponse
    {
        if (Gate::forUser($request->user())->denies('create', Shipment::class)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Anda tidak memiliki izin untuk membuat shipment baru.',
            ], 403);
        }

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:users,id'],
            'carrier_id' => ['required', 'exists:carriers,id'],
            'origin_id' => ['required', 'exists:locations,id'],
            'destination_id' => ['required', 'exists:locations,id', 'different:origin_id'],
            'cargo_type' => ['required', 'string', 'max:255'],
            'expected_departure' => ['required', 'date'],
            'expected_arrival' => ['required', 'date', 'after:expected_departure'],
            'estimated_arrival' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.weight_kg' => ['required', 'numeric', 'min:0'],
            'items.*.volume_cbm' => ['required', 'numeric', 'min:0'],
            'items.*.package_type' => ['required', 'string', 'max:100'],
            'items.*.hs_code' => ['nullable', 'string', 'max:50'],
        ], [
            'customer_id.required' => 'Customer pemilik kargo wajib dipilih.',
            'customer_id.exists' => 'Customer yang dipilih tidak terdaftar di sistem.',
            'carrier_id.required' => 'Carrier operator kargo wajib dipilih.',
            'carrier_id.exists' => 'Carrier yang dipilih tidak terdaftar di sistem.',
            'origin_id.required' => 'Titik asal pengiriman wajib dipilih.',
            'destination_id.required' => 'Titik tujuan pengiriman wajib dipilih.',
            'destination_id.different' => 'Pelabuhan tujuan harus berbeda dengan pelabuhan asal.',
            'cargo_type.required' => 'Tipe muatan kargo wajib diisi.',
            'expected_departure.required' => 'Waktu keberangkatan wajib diisi.',
            'expected_arrival.required' => 'Waktu perkiraan tiba wajib diisi.',
            'expected_arrival.after' => 'Waktu perkiraan tiba harus setelah waktu keberangkatan.',
            'items.required' => 'Minimal harus menyertakan 1 item kargo.',
            'items.min' => 'Minimal harus menyertakan 1 item kargo.',
        ]);

        $items = $validated['items'];
        unset($validated['items']);

        $shipment = $this->shipmentService->createShipment($validated, $items);

        return response()->json([
            'success' => true,
            'message' => 'Pengiriman kargo berhasil dibuat.',
            'data' => $shipment,
        ], 201);
    }

    /**
     * Display the specified shipment.
     */
    public function show(Request $request, string|int $id): JsonResponse
    {
        $shipment = Shipment::with(['items', 'carrier', 'origin', 'destination', 'customer'])->find($id);

        if (! $shipment) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengiriman kargo tidak ditemukan.',
            ], 404);
        }

        if (Gate::forUser($request->user())->denies('view', $shipment)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Anda tidak memiliki izin untuk melihat pengiriman ini.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $shipment,
        ], 200);
    }

    /**
     * Update the specified shipment.
     */
    public function update(Request $request, string|int $id): JsonResponse
    {
        $shipment = Shipment::find($id);

        if (! $shipment) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengiriman kargo tidak ditemukan.',
            ], 404);
        }

        if (Gate::forUser($request->user())->denies('update', $shipment)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Anda tidak memiliki izin untuk memperbarui pengiriman ini atau kargo sudah berstatus DELIVERED.',
            ], 403);
        }

        $validated = $request->validate([
            'carrier_id' => ['nullable', 'exists:carriers,id'],
            'origin_id' => ['nullable', 'exists:locations,id'],
            'destination_id' => ['nullable', 'exists:locations,id'],
            'cargo_type' => ['nullable', 'string', 'max:255'],
            'expected_departure' => ['nullable', 'date'],
            'expected_arrival' => ['nullable', 'date'],
            'estimated_arrival' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $updated = $this->shipmentService->updateShipment(
                $shipment,
                array_filter($validated, fn ($val) => ! is_null($val))
            );

            return response()->json([
                'success' => true,
                'message' => 'Data pengiriman kargo berhasil diperbarui.',
                'data' => $updated,
            ], 200);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified shipment from storage.
     */
    public function destroy(Request $request, string|int $id): JsonResponse
    {
        $shipment = Shipment::find($id);

        if (! $shipment) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengiriman kargo tidak ditemukan.',
            ], 404);
        }

        if (Gate::forUser($request->user())->denies('delete', $shipment)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Anda tidak memiliki izin untuk menghapus pengiriman ini.',
            ], 403);
        }

        try {
            $this->shipmentService->deleteShipment($shipment);

            return response()->json([
                'success' => true,
                'message' => 'Pengiriman kargo berhasil dihapus dari sistem.',
            ], 200);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
