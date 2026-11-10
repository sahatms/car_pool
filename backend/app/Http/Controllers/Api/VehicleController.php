<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::latest()->get();

        return response()->json([
            'message' => 'Daftar kendaraan berhasil diambil.',
            'data' => $vehicles,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_type' => [
                'required',
                Rule::in(['2_wheels', '4_wheels', '6_wheels']),
            ],
            'vehicle_category' => ['required', 'string', 'max:100'],
            'brand' => ['required', 'string', 'max:100'],
            'license_plate' => [
                'required',
                'string',
                'max:20',
                'unique:vehicles,license_plate',
            ],
            'registered_owner' => ['required', 'string', 'max:150'],
            'ownership_status' => [
                'required',
                Rule::in(['company_owned', 'rented', 'borrowed']),
            ],
            'photo' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['status'] = 'active';

        $vehicle = Vehicle::create($validated);

        return response()->json([
            'message' => 'Kendaraan berhasil ditambahkan.',
            'data' => $vehicle,
        ], 201);
    }

    public function show(string $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        return response()->json([
            'message' => 'Detail kendaraan berhasil diambil.',
            'data' => $vehicle,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $validated = $request->validate([
            'vehicle_type' => [
                'sometimes',
                Rule::in(['2_wheels', '4_wheels', '6_wheels']),
            ],
            'vehicle_category' => ['sometimes', 'required', 'string', 'max:100'],
            'brand' => ['sometimes', 'required', 'string', 'max:100'],
            'license_plate' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('vehicles', 'license_plate')->ignore($vehicle->id),
            ],
            'registered_owner' => ['sometimes', 'required', 'string', 'max:150'],
            'ownership_status' => [
                'sometimes',
                Rule::in(['company_owned', 'rented', 'borrowed']),
            ],
            'status' => [
                'sometimes',
                Rule::in(['active', 'inactive']),
            ],
            'photo' => ['nullable', 'string', 'max:255'],
        ]);

        if (isset($validated['status'])) {

            if ($vehicle->status === 'booked') {
                throw ValidationException::withMessages([
                    'status' => [
                        'Status kendaraan tidak dapat diubah karena kendaraan sedang dipinjam.',
                    ],
                ]);
            }

            if ($validated['status'] === 'active') {
                $maintenanceInProgress = $vehicle->maintenances()
                    ->whereIn('status', ['scheduled', 'in_progress'])
                    ->exists();

                if ($maintenanceInProgress) {
                    throw ValidationException::withMessages([
                        'status' => [
                            'Kendaraan tidak dapat diaktifkan karena masih memiliki jadwal atau proses maintenance.',
                        ],
                    ]);
                }
            }
        }

        $vehicle->update($validated);

        return response()->json([
            'message' => 'Kendaraan berhasil diperbarui.',
            'data' => $vehicle->fresh(),
        ]);
    }

    public function destroy(string $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $hasBookings = Booking::where('vehicle_id', $vehicle->id)
            ->exists();

        if ($hasBookings) {
            throw ValidationException::withMessages([
                'vehicle' => [
                    'Kendaraan tidak dapat dihapus karena sudah memiliki riwayat booking.',
                ],
            ]);
        }

        if ($vehicle->maintenances()->exists()) {
            throw ValidationException::withMessages([
                'vehicle' => [
                    'Kendaraan tidak dapat dihapus karena sudah memiliki riwayat maintenance.',
                ],
            ]);
        }

        $vehicle->delete();

        return response()->json([
            'message' => 'Kendaraan berhasil dihapus.',
        ]);
    }
}
