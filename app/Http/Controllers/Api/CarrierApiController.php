<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarrierApiController extends Controller
{
    /**
     * Display a listing of active carriers.
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');

        $query = Carrier::query();

        if ($status) {
            $query->where('status', $status);
        } else {
            $query->active();
        }

        $carriers = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar carrier berhasil diambil.',
            'data' => $carriers,
        ], 200);
    }

    /**
     * Store a newly created carrier.
     */
    public function store(Request $request): JsonResponse
    {
        if (! ($request->user()->isAdmin() || $request->user()->isOperator())) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Anda tidak memiliki izin untuk mengelola carrier.',
            ], 403);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:carriers,code'],
            'name' => ['required', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'in:active,inactive'],
        ], [
            'code.required' => 'Kode carrier wajib diisi.',
            'code.unique' => 'Kode carrier sudah terdaftar di sistem.',
            'name.required' => 'Nama carrier wajib diisi.',
            'contact_email.email' => 'Format email kontak tidak valid.',
        ]);

        $carrier = Carrier::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Carrier berhasil ditambahkan.',
            'data' => $carrier,
        ], 201);
    }
}
