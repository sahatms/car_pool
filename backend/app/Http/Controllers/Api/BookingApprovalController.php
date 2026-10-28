<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingApprovalController extends Controller
{
    public function directorDecision(Request $request, string $id)
    {
        $validated = $request->validate([
            'director_decision' => [
                'required',
                'in:approved,rejected',
            ],
            'director_decision_date' => [
                'required',
                'date',
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $booking = DB::transaction(function () use ($id, $validated) {
            $booking = Booking::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($booking->status !== 'pending') {
                throw ValidationException::withMessages([
                    'booking' => [
                        'Booking ini sudah tidak berstatus pending.',
                    ],
                ]);
            }

            $approval = BookingApproval::firstOrCreate(
                ['booking_id' => $booking->id]
            );

            if ($approval->director_decision !== null) {
                throw ValidationException::withMessages([
                    'director_decision' => [
                        'Keputusan Direktur sudah pernah dicatat.',
                    ],
                ]);
            }

            $approval->update([
                'director_decision' => $validated['director_decision'],
                'director_decision_date' =>
                    $validated['director_decision_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($validated['director_decision'] === 'rejected') {
                $booking->update([
                    'status' => 'rejected',
                    'admin_notes' => $validated['notes'] ?? null,
                ]);
            }

            return $booking;
        });

        return response()->json([
            'message' => 'Keputusan Direktur berhasil dicatat.',
            'data' => $booking->fresh()->load('approval'),
        ]);
    }

    public function adminDecision(Request $request, string $id)
    {
        $validated = $request->validate([
            'admin_decision' => [
                'required',
                'in:approved,rejected',
            ],
            'admin_decision_date' => [
                'required',
                'date',
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $booking = DB::transaction(function () use (
            $id,
            $validated,
            $request
        ) {
            $booking = Booking::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($booking->status !== 'pending') {
                throw ValidationException::withMessages([
                    'booking' => [
                        'Booking ini sudah tidak berstatus pending.',
                    ],
                ]);
            }

            $approval = BookingApproval::where(
                'booking_id',
                $booking->id
            )->first();

            if (!$approval ||
                $approval->director_decision !== 'approved') {
                throw ValidationException::withMessages([
                    'director_decision' => [
                        'Persetujuan Direktur harus dicatat terlebih dahulu.',
                    ],
                ]);
            }

            if ($approval->admin_decision !== null) {
                throw ValidationException::withMessages([
                    'admin_decision' => [
                        'Keputusan admin sudah pernah dicatat.',
                    ],
                ]);
            }

            $approval->update([
                'admin_id' => $request->user()->id,
                'admin_decision' => $validated['admin_decision'],
                'admin_decision_date' =>
                    $validated['admin_decision_date'],
                'notes' => $validated['notes'] ?? $approval->notes,
            ]);

            $booking->update([
                'status' => $validated['admin_decision'],
                'admin_notes' => $validated['notes'] ?? null,
                'processed_by' => $request->user()->id,
            ]);

            return $booking;
        });

        return response()->json([
            'message' => 'Keputusan admin berhasil dicatat.',
            'data' => $booking->fresh()->load('approval'),
        ]);
    }
}
