<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\EtaService;
use App\Services\ExceptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShipmentExceptionWebController extends Controller
{
    public function __construct(
        protected ExceptionService $exceptionService,
        protected EtaService $etaService
    ) {
    }

    /**
     * Display centralized Exception & Disruption Control Center for Operators/Admins.
     */
    public function index(Request $request): View
    {
        if ($request->user()->isCustomer()) {
            abort(403, 'Akses Ditolak: Customer tidak diizinkan mengakses Exception Center.');
        }

        $query = ShipmentException::with(['shipment.carrier', 'reporter'])->latest('detected_at');

        if ($request->filled('status') && $request->status !== 'ALL') {
            $query->where('status', $request->status);
        }

        if ($request->filled('exception_type')) {
            $query->where('exception_type', $request->exception_type);
        }

        $exceptions = $query->paginate(15)->withQueryString();

        $openCount = ShipmentException::whereIn('status', ['OPEN', 'INVESTIGATING'])->count();
        $resolvedCount = ShipmentException::where('status', 'RESOLVED')->count();
        $totalCount = ShipmentException::count();

        return view('dashboard.exceptions', compact('exceptions', 'openCount', 'resolvedCount', 'totalCount'));
    }

    /**
     * Store a new exception report for a specific shipment.
     */
    public function store(Request $request, Shipment $shipment): RedirectResponse
    {
        if ($request->user()->isCustomer()) {
            abort(403, 'Akses Ditolak: Customer tidak diizinkan melaporkan kendala kargo.');
        }

        $validated = $request->validate([
            'exception_type' => [
                'required',
                'string',
                'in:CUSTOMS_HOLD,WEATHER_DELAY,PORT_CONGESTION,DAMAGED,MISROUTED,DOCUMENTATION_ISSUE',
            ],
            'severity' => ['required', 'string', 'in:LOW,MEDIUM,HIGH'],
            'impact_hours' => ['nullable', 'integer', 'min:0'],
            'description' => ['required', 'string', 'max:1000'],
        ], [
            'exception_type.required' => 'Jenis kendala kargo wajib dipilih.',
            'severity.required' => 'Tingkat keparahan kendala wajib dipilih.',
            'description.required' => 'Rincian deskripsi kendala wajib diisi.',
        ]);

        $this->exceptionService->createException($shipment, $validated, $request->user());

        return redirect()->back()->with(
            'success',
            'Kendala kargo berhasil dilaporkan dan jadwal ETA telah disesuaikan otomatis.'
        );
    }

    /**
     * Resolve an active disruption exception with resolution notes.
     */
    public function resolve(Request $request, ShipmentException $exception): RedirectResponse
    {
        if ($request->user()->isCustomer()) {
            abort(403, 'Akses Ditolak: Customer tidak diizinkan mengubah status kendala.');
        }

        $validated = $request->validate([
            'resolution_notes' => ['required', 'string', 'max:1000'],
        ], [
            'resolution_notes.required' => 'Catatan solusi atau resolusi wajib diisi sebelum menyelesaikan kendala.',
        ]);

        $this->exceptionService->updateStatus($exception, 'RESOLVED', $validated['resolution_notes']);

        return redirect()->back()->with(
            'success',
            'Kendala kargo telah berhasil diselesaikan (Resolved).'
        );
    }
}
