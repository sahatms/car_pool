<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VehicleMaintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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

        if ($request->hasFile('receipt')) {
            $validated['receipt_path'] = $request
                ->file('receipt')
                ->store('vehicle-maintenance-receipts', 'public');
        }

        unset($validated['receipt']);

        $validated['recorded_by'] = $request->user()->id;

        $maintenance = VehicleMaintenance::create($validated);

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

        if ($request->hasFile('receipt')) {
            if ($maintenance->receipt_path) {
                Storage::disk('public')->delete(
                    $maintenance->receipt_path
                );
            }

            $validated['receipt_path'] = $request
                ->file('receipt')
                ->store('vehicle-maintenance-receipts', 'public');
        }

        unset($validated['receipt']);

        $maintenance->update($validated);

        return response()->json([
            'message' => 'Data maintenance berhasil diperbarui.',
            'data' => $maintenance->fresh()->load('vehicle'),
        ]);
    }

    public function destroy(string $id)
    {
        $maintenance = VehicleMaintenance::findOrFail($id);

        if ($maintenance->receipt_path) {
            Storage::disk('public')->delete(
                $maintenance->receipt_path
            );
        }

        $maintenance->delete();

        return response()->json([
            'message' => 'Data maintenance berhasil dihapus.',
        ]);
    }
}
