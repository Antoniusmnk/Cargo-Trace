<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InvalidStatusTransitionException;
use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Services\TrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackingApiController extends Controller
{
    public function __construct(
        protected TrackingService $trackingService
    ) {
    }

    /**
     * Display the tracking events timeline for a shipment.
     */
    public function index(Request $request, Shipment $shipment): JsonResponse
    {
        if ($request->user()->cannot('view', $shipment)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Anda tidak memiliki izin untuk melihat riwayat pelacakan kargo ini.',
            ], 403);
        }

        $shipment->loadMissing(['carrier', 'origin', 'destination']);
        $timeline = $this->trackingService->getShipmentTimeline($shipment);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat perjalanan kargo berhasil diambil.',
            'data' => [
                'shipment_id' => $shipment->id,
                'shipment_number' => $shipment->shipment_number,
                'current_status' => $shipment->current_status,
                'is_delayed' => $shipment->is_delayed,
                'carrier' => $shipment->carrier?->name,
                'origin' => $shipment->origin?->name,
                'destination' => $shipment->destination?->name,
                'timeline' => $timeline,
            ],
        ], 200);
    }

    /**
     * Store a new tracking event checkpoint for a shipment.
     */
    public function store(Request $request, Shipment $shipment): JsonResponse
    {
        if ($request->user()->isCustomer()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Customer tidak diizinkan menambahkan tracking event.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:CREATED,BOOKED,PICKED_UP,IN_TRANSIT,DELIVERED'],
            'event_time' => ['required', 'date'],
            'location_name' => ['required', 'string', 'max:255'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'description' => ['required', 'string', 'max:1000'],
        ], [
            'status.required' => 'Status milestone kargo wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid dalam state machine logistik.',
            'event_time.required' => 'Waktu kejadian checkpoint wajib diisi.',
            'location_name.required' => 'Nama titik lokasi atau pelabuhan wajib diisi.',
            'description.required' => 'Rincian aktivitas checkpoint wajib diisi.',
        ]);

        try {
            $event = $this->trackingService->addTrackingEvent($shipment, $validated, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Tracking event berhasil dicatat dan status kargo diperbarui.',
                'data' => $event->load(['creator', 'location']),
            ], 201);
        } catch (InvalidStatusTransitionException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
