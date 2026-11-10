<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\VehicleUsage;
use App\Services\VehicleOdometerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class VehicleUsageController extends Controller
{
    public function store(
    Request $request,
    string $id,
    VehicleOdometerService $odometerService
    )
    {
        $validated = $request->validate([
            'actual_start_datetime' => ['required', 'date'],
            'odometer_start' => ['required', 'integer', 'min:0'],
            'usage_notes' => ['nullable', 'string'],
        ]);

        $usage = DB::transaction(function () use (
            $validated,
            $id,
            $request,
            $odometerService
        ) {

            $booking = Booking::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($booking->status !== 'pending') {
                throw ValidationException::withMessages([
                    'booking' => [
                        'Penggunaan kendaraan hanya dapat dicatat untuk booking yang masih pending dan belum ditolak.',
                    ],
                ]);
            }

            $vehicle = Vehicle::whereKey($booking->vehicle_id)
                ->lockForUpdate()
                ->firstOrFail();

            $latestOdometer = $odometerService->getLatestOdometer($vehicle);

            if (
                $latestOdometer !== null &&
                $validated['odometer_start'] < $latestOdometer
            ) {
                throw ValidationException::withMessages([
                    'odometer_start' => [
                        "Odometer awal tidak boleh lebih kecil dari catatan terakhir ({$latestOdometer} km).",
                    ],
                ]);
            }

            $actualStart = Carbon::parse($validated['actual_start_datetime']);

            if (
                $actualStart->lt($booking->start_datetime) ||
                $actualStart->gt($booking->end_datetime)
            ) {
                throw ValidationException::withMessages([
                    'actual_start_datetime' => [
                        'Waktu mulai penggunaan harus berada dalam rentang jadwal booking.',
                    ],
                ]);
            }

            if (VehicleUsage::where('booking_id', $booking->id)->exists()) {
                throw ValidationException::withMessages([
                    'booking' => [
                        'Data penggunaan untuk booking ini sudah pernah dicatat.',
                    ],
                ]);
            }

            return VehicleUsage::create([
                'booking_id' => $booking->id,
                'actual_start_datetime' => $validated['actual_start_datetime'],
                'odometer_start' => $validated['odometer_start'],
                'usage_notes' => $validated['usage_notes'] ?? null,
                'recorded_by' => $request->user()->id,
            ]);
        });

        return response()->json([
            'message' => 'Data penggunaan kendaraan berhasil dicatat.',
            'data' => $usage->load('booking'),
        ], 201);
    }
}
