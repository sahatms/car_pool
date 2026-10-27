<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['borrower', 'vehicle'])
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Daftar booking berhasil diambil.',
            'data' => $bookings,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'borrower_id' => ['required', 'integer', 'exists:borrowers,id'],
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'start_datetime' => ['required', 'date', 'after_or_equal:now'],
            'end_datetime' => ['required', 'date', 'after:start_datetime'],
            'destination' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'max:1000'],
        ]);

        $booking = DB::transaction(function () use ($validated) {
            $vehicle = Vehicle::whereKey($validated['vehicle_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($vehicle->status !== 'active') {
                throw ValidationException::withMessages([
                    'vehicle_id' => [
                        'Kendaraan tidak aktif dan tidak dapat dipinjam.',
                    ],
                ]);
            }

            $start = $validated['start_datetime'];
            $end = $validated['end_datetime'];

            $hasConflict = Booking::where(
                    'vehicle_id',
                    $validated['vehicle_id']
                )
                ->whereIn('status', ['pending', 'approved'])
                ->where('start_datetime', '<', $end)
                ->where('end_datetime', '>', $start)
                ->exists();

            if ($hasConflict) {
                throw ValidationException::withMessages([
                    'vehicle_id' => [
                        'Kendaraan sudah memiliki booking pada rentang waktu tersebut.',
                    ],
                ]);
            }

            // Cek jadwal perawatan kendaraan.
            $maintenanceConflict = DB::table('vehicle_maintenance')
                ->where('vehicle_id', $validated['vehicle_id'])
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->whereDate('start_date', '<=', substr($end, 0, 10))
                ->where(function ($query) use ($start) {
                    $query->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', substr($start, 0, 10));
                })
                ->exists();

            if ($maintenanceConflict) {
                throw ValidationException::withMessages([
                    'vehicle_id' => [
                        'Kendaraan sedang dijadwalkan untuk perawatan pada tanggal tersebut.',
                    ],
                ]);
            }

            return Booking::create([
                'booking_code' => 'BK-' . now()->format('YmdHis')
                    . '-' . Str::upper(Str::random(5)),
                'borrower_id' => $validated['borrower_id'],
                'vehicle_id' => $validated['vehicle_id'],
                'start_datetime' => $start,
                'end_datetime' => $end,
                'destination' => $validated['destination'],
                'purpose' => $validated['purpose'],
                'status' => 'pending',
            ]);
        });

        return response()->json([
            'message' => 'Pengajuan booking berhasil dibuat.',
            'data' => $booking->load(['borrower', 'vehicle']),
        ], 201);
    }

    public function show(string $id)
    {
        $booking = Booking::with(['borrower', 'vehicle'])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Detail booking berhasil diambil.',
            'data' => $booking,
        ]);
    }

    public function updateStatus(Request $request, string $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $booking = DB::transaction(function () use ($id, $validated, $request) {
        $booking = Booking::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($booking->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => [
                    'Hanya peminjaman berstatus pending yang dapat diproses.'
                    ],
                ]);
            }

            $booking->update([
                'status' => $validated['status'],
                'admin_notes' => $validated['admin_notes'] ?? null,
                'processed_by' => $request->user()->id,
            ]);

        return $booking;
    });

    return response()->json([
        'message' => $validated['status'] === 'approved'
            ? 'Peminjaman berhasil disetujui.'
            : 'Peminjaman berhasil ditolak.',
        'data' => $booking->fresh()->load(['borrower', 'vehicle']),
    ]);
    }
}
