<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VehicleMaintenance;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VehicleMaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = VehicleMaintenance::with('vehicle')
            ->orderByDesc('start_date');

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('maintenance_type')) {
            $query->where(
                'maintenance_type',
                $request->maintenance_type
            );
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => [
                'required',
                'integer',
                'exists:vehicles,id',
            ],
            'maintenance_type' => [
                'required',
                Rule::in([
                    'routine_service',
                    'repair',
                    'spare_part_replacement',
                    'other',
                ]),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
            'odometer' => ['nullable', 'integer', 'min:0'],
            'workshop_name' => ['nullable', 'string', 'max:255'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'status' => [
                'sometimes',
                Rule::in([
                    'scheduled',
                    'in_progress',
                    'completed',
                    'cancelled',
                ]),
            ],
            'description' => ['nullable', 'string'],
            'receipt' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
        ]);

        $validated['status'] = $validated['status'] ?? 'scheduled';

        $receiptPath = null;

        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')
                ->store('vehicle-maintenance-receipts', 'public');

            $validated['receipt_path'] = $receiptPath;
        }

        unset($validated['receipt']);

        try {
            $maintenance = DB::transaction(function () use (
                $validated,
                $request
            ) {
                $vehicle = Vehicle::whereKey($validated['vehicle_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($vehicle->status === 'booked') {
                    throw ValidationException::withMessages([
                        'vehicle_id' => [
                            'Maintenance tidak dapat dicatat karena kendaraan sedang dipinjam.',
                        ],
                    ]);
                }

                $validated['recorded_by'] = $request->user()->id;

                $maintenance = VehicleMaintenance::create($validated);

                if (in_array($validated['status'], [
                    'scheduled',
                    'in_progress',
                ])) {
                    $vehicle->update([
                        'status' => 'inactive',
                    ]);
                }

                return $maintenance;
            });
        } catch (\Throwable $e) {
            if ($receiptPath !== null) {
                Storage::disk('public')->delete($receiptPath);
            }

            throw $e;
        }

        return response()->json([
            'message' => 'Data maintenance berhasil ditambahkan.',
            'data' => $maintenance->load('vehicle'),
        ], 201);
    }

    public function show(string $id)
    {
        $maintenance = VehicleMaintenance::with('vehicle')
            ->findOrFail($id);

        return response()->json([
            'data' => $maintenance,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $maintenance = VehicleMaintenance::findOrFail($id);

        $validated = $request->validate([
            'vehicle_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:vehicles,id',
            ],
            'maintenance_type' => [
                'sometimes',
                'required',
                Rule::in([
                    'routine_service',
                    'repair',
                    'spare_part_replacement',
                    'other',
                ]),
            ],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => [
                'sometimes',
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
            'odometer' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'workshop_name' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],
            'cost' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'status' => [
                'sometimes',
                Rule::in([
                    'scheduled',
                    'in_progress',
                    'completed',
                    'cancelled',
                ]),
            ],
            'description' => ['sometimes', 'nullable', 'string'],
            'receipt' => [
                'sometimes',
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
        ]);

        $oldReceiptPath = $maintenance->receipt_path;
        $newReceiptPath = null;

        if ($request->hasFile('receipt')) {
            $newReceiptPath = $request->file('receipt')
                ->store('vehicle-maintenance-receipts', 'public');

            $validated['receipt_path'] = $newReceiptPath;
        }

        unset($validated['receipt']);

        try {
            $updatedMaintenance = DB::transaction(function () use (
                $maintenance,
                $validated
            ) {
                $maintenance = VehicleMaintenance::whereKey($maintenance->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $oldVehicleId = $maintenance->vehicle_id;
                $newVehicleId = $validated['vehicle_id'] ?? $oldVehicleId;

                $vehicles = Vehicle::whereIn(
                    'id',
                    array_unique([$oldVehicleId, $newVehicleId])
                )
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $oldVehicle = $vehicles->get($oldVehicleId);
                $newVehicle = $vehicles->get($newVehicleId);

                if (!$oldVehicle || !$newVehicle) {
                    throw ValidationException::withMessages([
                        'vehicle_id' => ['Kendaraan tidak ditemukan.'],
                    ]);
                }

                $newStatus = $validated['status'] ?? $maintenance->status;

                if (
                    $newVehicleId !== $oldVehicleId &&
                    in_array($newStatus, ['scheduled', 'in_progress']) &&
                    $newVehicle->status === 'booked'
                ) {
                    throw ValidationException::withMessages([
                        'vehicle_id' => [
                            'Maintenance tidak dapat dipindahkan ke kendaraan yang sedang dipinjam.',
                        ],
                    ]);
                }

                $maintenance->update($validated);

                foreach ($vehicles as $vehicle) {
                    $hasActiveMaintenance = VehicleMaintenance::where(
                        'vehicle_id',
                        $vehicle->id
                    )
                        ->whereIn('status', ['scheduled', 'in_progress'])
                        ->exists();

                    if ($vehicle->status === 'booked') {
                        continue;
                    }

                    $vehicle->update([
                        'status' => $hasActiveMaintenance
                            ? 'inactive'
                            : (
                                $vehicle->status === 'inactive' &&
                                $vehicle->id === $oldVehicleId &&
                                !in_array($newStatus, ['completed', 'cancelled'])
                                    ? 'inactive'
                                    : 'active'
                            ),
                    ]);
                }

                return $maintenance->fresh()->load('vehicle');
            });
        } catch (\Throwable $e) {
            if ($newReceiptPath !== null) {
                Storage::disk('public')->delete($newReceiptPath);
            }

            throw $e;
        }

        if (
            $newReceiptPath !== null &&
            $oldReceiptPath !== null
        ) {
            Storage::disk('public')->delete($oldReceiptPath);
        }

        return response()->json([
            'message' => 'Data maintenance berhasil diperbarui.',
            'data' => $updatedMaintenance,
        ]);
    }

    public function destroy(string $id)
    {
        $maintenance = VehicleMaintenance::findOrFail($id);

        $receiptPath = $maintenance->receipt_path;

        DB::transaction(function () use ($maintenance) {

            $maintenance = VehicleMaintenance::whereKey($maintenance->id)
                ->lockForUpdate()
                ->firstOrFail();

            $vehicle = Vehicle::whereKey($maintenance->vehicle_id)
                ->lockForUpdate()
                ->firstOrFail();

            $maintenance->delete();

            $hasActiveMaintenance = VehicleMaintenance::where(
                'vehicle_id',
                $vehicle->id
            )
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->exists();

            if ($vehicle->status !== 'booked') {
                $vehicle->update([
                    'status' => $hasActiveMaintenance
                        ? 'inactive'
                        : 'active',
                ]);
            }
        });

        if ($receiptPath !== null) {
            Storage::disk('public')->delete($receiptPath);
        }

        return response()->json([
            'message' => 'Data maintenance berhasil dihapus.',
        ]);
    }
}
