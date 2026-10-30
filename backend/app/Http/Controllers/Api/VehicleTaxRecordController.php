<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VehicleTaxRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class VehicleTaxRecordController extends Controller
{
    private function addReminderInformation($records)
    {
        return $records->map(function ($record) {
            $today = Carbon::today();
            $dueDate = Carbon::parse($record->due_date)->startOfDay();

            if ($record->paid_date !== null) {
                $record->payment_status = 'Lunas';
                $record->days_remaining = null;
                $record->reminder_status = 'Lunas';
            } else {
                $days = (int) $today->diffInDays($dueDate, false);

                $record->payment_status = 'Belum dibayar';
                $record->days_remaining = $days;

                if ($days > 0) {
                    $record->reminder_status = $days . ' hari lagi';
                } elseif ($days === 0) {
                    $record->reminder_status = 'Hari ini';
                } else {
                    $record->reminder_status = 'Terlambat ' . abs($days) . ' hari';
                }
            }

            return $record;
        });
    }

    public function index(Request $request)
    {
        $query = VehicleTaxRecord::with('vehicle')
            ->orderBy('due_date');

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('tax_type')) {
            $query->where('tax_type', $request->tax_type);
        }

        if ($request->boolean('upcoming')) {
            $query->whereNull('paid_date')
                ->whereBetween('due_date', [
                    now()->toDateString(),
                    now()->addDays(30)->toDateString(),
                ]);
        }

        $records = $query->paginate(15);

        $records->setCollection(
            $this->addReminderInformation($records->getCollection())
        );

        return response()->json($records);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => [
                'required',
                'integer',
                'exists:vehicles,id',
            ],
            'tax_type' => [
                'required',
                'string',
                'max:255',
                Rule::in(['pajak_kendaraan', 'KIR']),
            ],
            'due_date' => ['required', 'date'],
            'paid_date' => ['nullable', 'date'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'payment_proof' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
            'notes' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('payment_proof')) {
            $validated['payment_proof_path'] =
                $request->file('payment_proof')
                    ->store('vehicle-tax-proofs', 'public');
        }

        unset($validated['payment_proof']);

        $validated['recorded_by'] = $request->user()->id;

        $record = VehicleTaxRecord::create($validated)->load('vehicle');

        $data = $this->addReminderInformation(
            collect([$record])
        )->first();

        return response()->json([
            'message' => 'Catatan Samsat/KIR berhasil ditambahkan.',
            'data' => $data,
        ], 201);
    }

    public function show(string $id)
    {
        $record = VehicleTaxRecord::with('vehicle')->findOrFail($id);

        $data = $this->addReminderInformation(
            collect([$record])
        )->first();

        return response()->json(['data' => $data]);
    }

    public function update(Request $request, string $id)
    {
        $record = VehicleTaxRecord::findOrFail($id);

        $validated = $request->validate([
            'vehicle_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:vehicles,id',
            ],
            'tax_type' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::in(['pajak_kendaraan', 'KIR']),
            ],
            'due_date' => ['sometimes', 'required', 'date'],
            'paid_date' => ['sometimes', 'nullable', 'date'],
            'amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'payment_proof' => [
                'sometimes',
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
            'notes' => ['sometimes', 'nullable', 'string'],
        ]);

        if ($request->hasFile('payment_proof')) {
            if ($record->payment_proof_path) {
                Storage::disk('public')->delete(
                    $record->payment_proof_path
                );
            }

            $validated['payment_proof_path'] =
                $request->file('payment_proof')
                    ->store('vehicle-tax-proofs', 'public');
        }

        unset($validated['payment_proof']);

        $record->update($validated);

        $data = $this->addReminderInformation(
            collect([$record->fresh()->load('vehicle')])
        )->first();

        return response()->json([
            'message' => 'Catatan Samsat/KIR berhasil diperbarui.',
            'data' => $data,
        ]);
    }

    public function destroy(string $id)
    {
        $record = VehicleTaxRecord::findOrFail($id);

        if ($record->payment_proof_path) {
            Storage::disk('public')->delete(
                $record->payment_proof_path
            );
        }

        $record->delete();

        return response()->json([
            'message' => 'Catatan Samsat/KIR berhasil dihapus.',
        ]);
    }
}
