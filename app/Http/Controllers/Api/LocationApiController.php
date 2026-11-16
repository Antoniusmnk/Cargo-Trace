<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationApiController extends Controller
{
    /**
     * Display a listing of locations.
     */
    public function index(Request $request): JsonResponse
    {
        $type = $request->query('type');

        $query = Location::query();

        if ($type) {
            $query->where('type', $type);
        }

        $locations = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar lokasi logistik berhasil diambil.',
            'data' => $locations,
        ], 200);
    }

    /**
     * Store a newly created location.
     */
    public function store(Request $request): JsonResponse
    {
        if (! ($request->user()->isAdmin() || $request->user()->isOperator())) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Anda tidak memiliki izin untuk mengelola lokasi.',
            ], 403);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:locations,code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'in:port,airport,warehouse,hub'],
            'address' => ['nullable', 'string'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ], [
            'code.required' => 'Kode lokasi wajib diisi.',
            'code.unique' => 'Kode lokasi sudah terdaftar di sistem.',
            'name.required' => 'Nama lokasi wajib diisi.',
            'city.required' => 'Kota lokasi wajib diisi.',
            'country.required' => 'Negara lokasi wajib diisi.',
            'latitude.required' => 'Koordinat latitude wajib diisi.',
            'longitude.required' => 'Koordinat longitude wajib diisi.',
        ]);

        $location = Location::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Lokasi berhasil ditambahkan.',
            'data' => $location,
        ], 201);
    }
}
