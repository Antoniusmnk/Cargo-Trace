<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\InvalidStatusTransitionException;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Shipment;
use App\Services\TrackingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShipmentTrackingWebController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected TrackingService $trackingService
    ) {
    }

    /**
     * Display the visual tracking timeline for a shipment.
     */
    public function show(Request $request, Shipment $shipment): View
    {
        $this->authorize('view', $shipment);

        $shipment->loadMissing([
            'carrier',
            'origin',
            'destination',
            'customer',
            'items',
            'trackingEvents.creator',
            'trackingEvents.location',
            'exceptions.reporter',
        ]);

        $timeline = $this->trackingService->getShipmentTimeline($shipment);

        $allowedNextStatuses = TrackingService::ALLOWED_TRANSITIONS[$shipment->current_status] ?? [];
        if ($shipment->current_status === 'IN_TRANSIT' && ! in_array('IN_TRANSIT', $allowedNextStatuses, true)) {
            $allowedNextStatuses[] = 'IN_TRANSIT';
        }

        $locations = Location::orderBy('name')->get();

        return view('dashboard.tracking-timeline', compact('shipment', 'timeline', 'allowedNextStatuses', 'locations'));
    }

    /**
     * Store a new tracking checkpoint event from the web interface.
     */
    public function addCheckpoint(Request $request, Shipment $shipment): RedirectResponse
    {
        if ($request->user()->isCustomer()) {
            abort(403, 'Akses Ditolak: Customer tidak diizinkan menambahkan tracking checkpoint.');
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
            'status.required' => 'Status checkpoint kargo wajib dipilih.',
            'status.in' => 'Status checkpoint tidak valid.',
            'event_time.required' => 'Waktu kejadian checkpoint wajib diisi.',
            'location_name.required' => 'Nama lokasi checkpoint wajib diisi.',
            'description.required' => 'Rincian deskripsi checkpoint wajib diisi.',
        ]);

        try {
            $this->trackingService->addTrackingEvent($shipment, $validated, $request->user());

            return redirect()->route('shipments.tracking.show', $shipment)
                ->with('success', 'Checkpoint pelacakan baru berhasil dicatat dan status kargo diperbarui.');
        } catch (InvalidStatusTransitionException $e) {
            return back()->withInput()->withErrors(['transition_error' => $e->getMessage()]);
        }
    }
}
