<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\Borrower;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

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
            // Identitas peminjam
            'full_name' => ['required', 'string', 'max:150'],
            'employee_number' => ['nullable', 'string', 'max:50'],
            'department' => ['required', 'string', 'max:100'],
            'phone_number' => ['required', 'string', 'max:20'],
            'selfie' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            // Data booking
            'vehicle_id' => [
                'required',
                'integer',
                'exists:vehicles,id',
            ],
            'start_datetime' => [
                'required',
                'date',
                'after_or_equal:now',
            ],
            'end_datetime' => [
                'required',
                'date',
                'after:start_datetime',
            ],
            'destination' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'max:1000'],
            'passenger_count' => ['required', 'integer', 'min:1', 'max:100'],
            'driver_name' => ['required', 'string', 'max:150'],
        ]);

        // Simpan selfie ke storage Laravel.
        $selfiePath = $request->file('selfie')
            ->store('borrowers/selfies', 'public');

        try {
            $booking = DB::transaction(function () use (
                $validated,
                $selfiePath
            ) {
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

                // Cek benturan jadwal booking.
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
                    ->whereDate(
                        'start_date',
                        '<=',
                        substr($end, 0, 10)
                    )
                    ->where(function ($query) use ($start) {
                        $query->whereNull('end_date')
                            ->orWhereDate(
                                'end_date',
                                '>=',
                                substr($start, 0, 10)
                            );
                    })
                    ->exists();

                if ($maintenanceConflict) {
                    throw ValidationException::withMessages([
                        'vehicle_id' => [
                            'Kendaraan sedang dijadwalkan untuk perawatan pada tanggal tersebut.',
                        ],
                    ]);
                }

                // Buat data peminjam secara otomatis.
                $borrower = Borrower::create([
                    'full_name' => $validated['full_name'],
                    'employee_number' =>
                        $validated['employee_number'] ?? null,
                    'department' => $validated['department'],
                    'phone_number' => $validated['phone_number'],
                    'selfie_path' => $selfiePath,
                ]);

                // Buat booking menggunakan ID peminjam yang baru dibuat.
                $booking = Booking::create([
                    'booking_code' => 'BK-' . now()->format('YmdHis')
                        . '-' . Str::upper(Str::random(5)),
                    'borrower_id' => $borrower->id,
                    'vehicle_id' => $validated['vehicle_id'],
                    'start_datetime' => $start,
                    'end_datetime' => $end,
                    'destination' => $validated['destination'],
                    'purpose' => $validated['purpose'],
                    'passenger_count' => $validated['passenger_count'],
                    'driver_name' => $validated['driver_name'],
                    'status' => 'pending',
                ]);

                $booking->download_token = Str::random(64);
                $booking->save();

                return $booking;

            });
        } catch (\Throwable $e) {
            // Hapus selfie jika transaksi database gagal.
            Storage::disk('public')->delete($selfiePath);

            throw $e;
        }

        $bookingData = $booking->load(['borrower', 'vehicle'])
            ->makeHidden(['download_token']);

        return response()->json([
            'message' => 'Pengajuan booking berhasil dibuat.',
            'data' => $bookingData,
            'download_url' => route('bookings.pdf', [
                'id' => $booking->id,
                'token' => $booking->download_token,
            ]),
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

    public function downloadPdf(string $id, string $token)
    {
        $booking = Booking::with(['borrower', 'vehicle'])
            ->where('id', $id)
            ->where('download_token', $token)
            ->firstOrFail();

        $pdf = Pdf::loadView('pdf.model-c', [
            'booking' => $booking,
        ])->setPaper('a4', 'portrait');

        return $pdf->download(
            'Model-C-' . $booking->booking_code . '.pdf'
        );
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
