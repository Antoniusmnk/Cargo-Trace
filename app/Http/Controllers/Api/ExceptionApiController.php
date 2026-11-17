<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\EtaService;
use App\Services\ExceptionService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExceptionApiController extends Controller
{
    public function __construct(
        protected ExceptionService $exceptionService,
        protected EtaService $etaService
    ) {
    }

    /**
     * Display a listing of exceptions for a specific shipment.
     */
    public function indexForShipment(Request $request, Shipment $shipment): JsonResponse
    {
        if ($request->user()->cannot('view', $shipment)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Anda tidak memiliki izin untuk melihat kendala kargo ini.',
            ], 403);
        }

        $exceptions = $this->exceptionService->getExceptionsForShipment($shipment);

        return response()->json([
            'success' => true,
            'message' => 'Daftar kendala pengiriman kargo berhasil diambil.',
            'data' => [
                'shipment_id' => $shipment->id,
                'shipment_number' => $shipment->shipment_number,
                'current_status' => $shipment->current_status,
                'is_delayed' => $shipment->is_delayed,
                'delay_hours' => $shipment->delay_hours,
                'delay_severity' => $shipment->delay_severity,
                'exceptions' => $exceptions,
            ],
        ], 200);
    }

    /**
     * Display a global listing of exceptions across all shipments (Admin/Operator only).
     */
    public function indexAll(Request $request): JsonResponse
    {
        if ($request->user()->isCustomer()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Customer tidak diizinkan mengakses ringkasan kendala operasional global.',
            ], 403);
        }

        $query = ShipmentException::with(['shipment.carrier', 'reporter'])->latest('detected_at');

        if ($request->filled('status')) {
            $statuses = explode(',', (string) $request->status);
            $query->whereIn('status', $statuses);
        }

        if ($request->filled('exception_type')) {
            $query->where('exception_type', $request->exception_type);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        $perPage = (int) $request->input('per_page', 15);
        $exceptions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar seluruh kendala operasional berhasil diambil.',
            'data' => $exceptions->items(),
            'meta' => [
                'current_page' => $exceptions->currentPage(),
                'last_page' => $exceptions->lastPage(),
                'per_page' => $exceptions->perPage(),
                'total' => $exceptions->total(),
            ],
            'links' => [
                'first' => $exceptions->url(1),
                'last' => $exceptions->url($exceptions->lastPage()),
                'prev' => $exceptions->previousPageUrl(),
                'next' => $exceptions->nextPageUrl(),
            ],
        ], 200);
    }

    /**
     * Store a new disruption exception for a shipment and recalculate ETA impact.
     */
    public function store(Request $request, Shipment $shipment): JsonResponse
    {
        if ($request->user()->isCustomer()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Customer tidak diizinkan melaporkan kendala kargo.',
            ], 403);
        }

        $validated = $request->validate([
            'exception_type' => [
                'required',
                'string',
                'in:CUSTOMS_HOLD,WEATHER_DELAY,PORT_CONGESTION,DAMAGED,MISROUTED,DOCUMENTATION_ISSUE',
            ],
            'severity' => ['required', 'string', 'in:LOW,MEDIUM,HIGH'],
            'detected_at' => ['nullable', 'date'],
            'impact_hours' => ['nullable', 'integer', 'min:0'],
            'description' => ['required', 'string', 'max:1000'],
        ], [
            'exception_type.required' => 'Jenis kendala kargo wajib dipilih.',
            'exception_type.in' => 'Jenis kendala kargo tidak valid.',
            'severity.required' => 'Tingkat keparahan kendala wajib dipilih.',
            'description.required' => 'Rincian deskripsi kendala wajib diisi.',
        ]);

        $exception = $this->exceptionService->createException($shipment, $validated, $request->user());

        $shipment->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Kendala kargo berhasil dicatat dan dampak jadwal diperhitungkan.',
            'data' => [
                'exception' => $exception,
                'shipment' => [
                    'id' => $shipment->id,
                    'shipment_number' => $shipment->shipment_number,
                    'estimated_arrival' => $shipment->estimated_arrival,
                    'is_delayed' => $shipment->is_delayed,
                    'delay_hours' => $shipment->delay_hours,
                    'delay_severity' => $shipment->delay_severity,
                    'delay_status_text' => $shipment->delay_status_text,
                ],
            ],
        ], 201);
    }

    /**
     * Update the investigation or resolution status of a shipment exception.
     */
    public function update(Request $request, ShipmentException $exception): JsonResponse
    {
        if ($request->user()->isCustomer()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Customer tidak diizinkan mengubah status kendala kargo.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:OPEN,INVESTIGATING,RESOLVED'],
            'resolution_notes' => ['required_if:status,RESOLVED', 'nullable', 'string', 'max:1000'],
        ], [
            'status.required' => 'Status baru kendala kargo wajib dipilih.',
            'resolution_notes.required_if' => 'Catatan resolusi wajib diisi saat menyelesaikan kendala (RESOLVED).',
        ]);

        try {
            $updated = $this->exceptionService->updateStatus(
                $exception,
                $validated['status'],
                $validated['resolution_notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Status kendala kargo berhasil diperbarui.',
                'data' => $updated->load(['shipment', 'reporter']),
            ], 200);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Recalculate and update the estimated arrival time (ETA) for a shipment.
     */
    public function recalculateEta(Request $request, Shipment $shipment): JsonResponse
    {
        if ($request->user()->isCustomer()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Customer tidak diizinkan mengubah ETA operasional.',
            ], 403);
        }

        $request->validate([
            'new_estimated_arrival' => ['nullable', 'date'],
        ]);

        if ($request->filled('new_estimated_arrival')) {
            $newEta = Carbon::parse($request->new_estimated_arrival);
            $updated = $this->etaService->updateShipmentEta($shipment, $newEta, 'Manual operator ETA override');
        } else {
            $updated = $this->etaService->recalculateShipmentDelay($shipment);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kalkulasi ulang jadwal kedatangan kargo (ETA) berhasil.',
            'data' => [
                'shipment_id' => $updated->id,
                'shipment_number' => $updated->shipment_number,
                'expected_arrival' => $updated->expected_arrival,
                'estimated_arrival' => $updated->estimated_arrival,
                'is_delayed' => $updated->is_delayed,
                'delay_hours' => $updated->delay_hours,
                'delay_severity' => $updated->delay_severity,
                'delay_status_text' => $updated->delay_status_text,
            ],
        ], 200);
    }
}
