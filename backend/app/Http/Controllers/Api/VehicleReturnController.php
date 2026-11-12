<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VehicleUsage;
use App\Models\VehicleReturn;
use App\Models\Booking;
use App\Services\VehicleOdometerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VehicleReturnController extends Controller
{
    public function store(
        Request $request,
        string $id,
        VehicleOdometerService $odometerService
    ) {
        $validated = $request->validate([
            'actual_return_datetime' => ['required', 'date'],
            'odometer_end' => ['required', 'integer', 'min:0'],
            'vehicle_condition' => ['required', 'in:baik,rusak_ringan,rusak_berat'],
            'return_notes' => ['nullable', 'string'],
        ]);

        $vehicleReturn = DB::transaction(function () use (
            $validated,
            $id,
            $request,
            $odometerService
        ) {

            $usage = VehicleUsage::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            $booking = Booking::whereKey($usage->booking_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($booking->status !== 'pending') {
                throw ValidationException::withMessages([
                    'booking' => [
                        'Pengembalian hanya dapat dicatat untuk booking yang masih pending.',
                    ],
                ]);
            }

            if (VehicleReturn::where('vehicle_usage_id', $usage->id)->exists()) {
                throw ValidationException::withMessages([
                    'vehicle_usage' => [
                        'Pengembalian untuk penggunaan kendaraan ini sudah pernah dicatat.',
                    ],
                ]);
            }

            if (
                $usage->actual_start_datetime === null ||
                $usage->odometer_start === null
            ) {
                throw ValidationException::withMessages([
                    'vehicle_usage' => [
                        'Data waktu mulai dan kilometer awal belum lengkap.',
                    ],
                ]);
            }

            $vehicle = $booking->vehicle()
                ->lockForUpdate()
                ->firstOrFail();

            $latestOdometer = $odometerService->getLatestOdometer($vehicle);

            if (
                $validated['odometer_end'] < $usage->odometer_start ||
                (
                    $latestOdometer !== null &&
                    $validated['odometer_end'] < $latestOdometer
                )
            ) {
                throw ValidationException::withMessages([
                    'odometer_end' => [
                        "Kilometer akhir tidak boleh lebih kecil dari kilometer awal maupun catatan odometer terakhir ({$latestOdometer} km).",
                    ],
                ]);
            }

            $actualReturn = \Carbon\Carbon::parse(
                $validated['actual_return_datetime']
            );

            if ($actualReturn->lt($usage->actual_start_datetime)) {
                throw ValidationException::withMessages([
                    'actual_return_datetime' => [
                        'Waktu pengembalian tidak boleh lebih awal daripada waktu mulai penggunaan.',
                    ],
                ]);
            }

            $vehicleReturn = VehicleReturn::create([
                'vehicle_usage_id' => $usage->id,
                'actual_return_datetime' => $validated['actual_return_datetime'],
                'odometer_end' => $validated['odometer_end'],
                'vehicle_condition' => $validated['vehicle_condition'],
                'return_notes' => $validated['return_notes'] ?? null,
                'recorded_by' => $request->user()->id,
            ]);

            $vehicle->update([
                'condition_status' => $validated['vehicle_condition'],
            ]);

            // Otomatis menyelesaikan booking setelah kendaraan dikembalikan.
            $booking->update([
                'status' => 'completed',
            ]);

            $maintenanceInProgress = $vehicle->maintenances()
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->exists();

            $otherActiveBooking = Booking::where('vehicle_id', $vehicle->id)
                ->whereIn('status', ['pending', 'approved'])
                ->exists();

            if (
                $maintenanceInProgress ||
                $vehicle->condition_status !== 'baik'
            ) {
                $vehicleStatus = 'inactive';
            } elseif ($otherActiveBooking) {
                $vehicleStatus = 'booked';
            } else {
                $vehicleStatus = 'active';
            }

            $vehicle->update([
                'status' => $vehicleStatus,
            ]);

            return $vehicleReturn;
        });

        return response()->json([
            'message' => 'Data pengembalian kendaraan berhasil dicatat dan booking telah diselesaikan.',
            'data' => $vehicleReturn->load('vehicleUsage.booking'),
        ], 201);
    }
}
