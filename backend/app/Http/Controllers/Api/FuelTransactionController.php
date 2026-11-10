<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FuelTransaction;
use App\Models\Vehicle;
use App\Services\VehicleOdometerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class FuelTransactionController extends Controller
{
    /**
     * Menampilkan daftar transaksi BBM.
     */
    public function index(Request $request)
    {
        $query = FuelTransaction::with([
            'vehicle',
            'recordedBy',
        ]);

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }

        if ($request->filled('start_date')) {
            $query->whereDate(
                'transaction_date',
                '>=',
                $request->start_date
            );
        }

        if ($request->filled('end_date')) {
            $query->whereDate(
                'transaction_date',
                '<=',
                $request->end_date
            );
        }

        $transactions = $query
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(10);

        return response()->json($transactions);
    }

    /**
     * Menampilkan detail satu transaksi BBM.
     */
    public function show(string $id)
    {
        $transaction = FuelTransaction::with([
            'vehicle',
            'recordedBy',
        ])->findOrFail($id);

        return response()->json([
            'data' => $transaction,
        ]);
    }

    /**
     * Menambahkan transaksi BBM.
     */
    public function store(
        Request $request,
        VehicleOdometerService $odometerService
    ) {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'transaction_date' => ['required', 'date'],
            'fuel_type' => ['required', 'string', 'max:100'],
            'liters' => ['required', 'numeric', 'gt:0'],
            'price_per_liter' => ['required', 'numeric', 'gt:0'],
            'odometer' => ['nullable', 'integer', 'min:0'],
            'provider' => ['nullable', 'string', 'max:255'],
            'receipt' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
            'notes' => ['nullable', 'string'],
        ]);

        $receiptPath = null;

        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')
                ->store('fuel-receipts', 'public');
        }

        try {
            $transaction = DB::transaction(function () use (
                $validated,
                $request,
                $receiptPath,
                $odometerService
            ) {
                $vehicle = Vehicle::whereKey($validated['vehicle_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (($validated['odometer'] ?? null) !== null) {
                    $latestOdometer = $odometerService
                        ->getLatestOdometer($vehicle);

                    if (
                        $latestOdometer !== null &&
                        $validated['odometer'] < $latestOdometer
                    ) {
                        throw ValidationException::withMessages([
                            'odometer' => [
                                "Odometer transaksi BBM tidak boleh lebih kecil dari catatan terakhir ({$latestOdometer} km).",
                            ],
                        ]);
                    }
                }

                $totalCost = round(
                    $validated['liters'] *
                    $validated['price_per_liter'],
                    2
                );

                return FuelTransaction::create([
                    'vehicle_id' => $vehicle->id,
                    'transaction_date' => $validated['transaction_date'],
                    'fuel_type' => $validated['fuel_type'],
                    'liters' => $validated['liters'],
                    'price_per_liter' => $validated['price_per_liter'],
                    'total_cost' => $totalCost,
                    'odometer' => $validated['odometer'] ?? null,
                    'provider' => $validated['provider'] ?? null,
                    'receipt_path' => $receiptPath,
                    'recorded_by' => $request->user()->id,
                    'notes' => $validated['notes'] ?? null,
                ]);
            });
        } catch (Throwable $e) {
            if ($receiptPath !== null) {
                Storage::disk('public')->delete($receiptPath);
            }

            throw $e;
        }

        return response()->json([
            'message' => 'Transaksi BBM berhasil dicatat.',
            'data' => $transaction->load([
                'vehicle',
                'recordedBy',
            ]),
        ], 201);
    }

    
    /**
     * Mengubah transaksi BBM.
     */
    public function update(
        Request $request,
        string $id,
        VehicleOdometerService $odometerService
    ) {
        $validated = $request->validate([
            'vehicle_id' => ['sometimes', 'required', 'exists:vehicles,id'],
            'transaction_date' => ['sometimes', 'required', 'date'],
            'fuel_type' => ['sometimes', 'required', 'string', 'max:100'],
            'liters' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'price_per_liter' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'odometer' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'provider' => ['sometimes', 'nullable', 'string', 'max:255'],
            'receipt' => [
                'sometimes',
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
            'notes' => ['sometimes', 'nullable', 'string'],
        ]);

        $newReceiptPath = null;

        if ($request->hasFile('receipt')) {
            $newReceiptPath = $request->file('receipt')
                ->store('fuel-receipts', 'public');
        }

        $oldReceiptPath = null;

        try {
            $transaction = DB::transaction(function () use (
                $validated,
                $id,
                $request,
                $newReceiptPath,
                $odometerService,
                &$oldReceiptPath
            ) {
                $transaction = FuelTransaction::whereKey($id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $oldReceiptPath = $transaction->receipt_path;

                $vehicleId = $validated['vehicle_id']
                    ?? $transaction->vehicle_id;

                $vehicle = Vehicle::whereKey($vehicleId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $odometer = array_key_exists('odometer', $validated)
                    ? $validated['odometer']
                    : $transaction->odometer;

                if ($odometer !== null) {
                    $latestOdometer = $odometerService
                        ->getLatestOdometer($vehicle);

                    // Jika kendaraan tidak berubah, abaikan odometer
                    // transaksi ini sendiri saat menghitung batas minimum.
                    if (
                        $vehicleId == $transaction->vehicle_id &&
                        $transaction->odometer !== null &&
                        $latestOdometer === (int) $transaction->odometer
                    ) {
                        $latestOdometer = $this->getLatestOdometerExcept(
                            $vehicle,
                            $transaction->id
                        );
                    }

                    if (
                        $latestOdometer !== null &&
                        $odometer < $latestOdometer
                    ) {
                        throw ValidationException::withMessages([
                            'odometer' => [
                                "Odometer tidak boleh lebih kecil dari catatan aktivitas lainnya ({$latestOdometer} km).",
                            ],
                        ]);
                    }
                }

                $liters = $validated['liters']
                    ?? $transaction->liters;

                $pricePerLiter = $validated['price_per_liter']
                    ?? $transaction->price_per_liter;

                $transaction->fill([
                    'vehicle_id' => $vehicleId,
                    'transaction_date' => $validated['transaction_date']
                        ?? $transaction->transaction_date,
                    'fuel_type' => $validated['fuel_type']
                        ?? $transaction->fuel_type,
                    'liters' => $liters,
                    'price_per_liter' => $pricePerLiter,
                    'total_cost' => round(
                        (float) $liters * (float) $pricePerLiter,
                        2
                    ),
                    'odometer' => $odometer,
                    'provider' => $validated['provider']
                        ?? $transaction->provider,
                    'notes' => $validated['notes']
                        ?? $transaction->notes,
                ]);

                if ($newReceiptPath !== null) {
                    $transaction->receipt_path = $newReceiptPath;
                }

                $transaction->save();

                return $transaction;
            });
        } catch (Throwable $e) {
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
            'message' => 'Transaksi BBM berhasil diperbarui.',
            'data' => $transaction->load([
                'vehicle',
                'recordedBy',
            ]),
        ]);
    }

    /**
     * Menghapus transaksi BBM.
     */
    public function destroy(string $id)
    {
        $transaction = FuelTransaction::findOrFail($id);

        DB::transaction(function () use ($transaction) {
            $transaction->delete();
        });

        if ($transaction->receipt_path !== null) {
            Storage::disk('public')->delete(
                $transaction->receipt_path
            );
        }

        return response()->json([
            'message' => 'Transaksi BBM berhasil dihapus.',
        ]);
    }

    /**
     * Mendapatkan odometer tertinggi selain transaksi tertentu.
     */
    private function getLatestOdometerExcept(
        Vehicle $vehicle,
        int $transactionId
    ): ?int {
        $usageOdometer = \App\Models\VehicleUsage::whereHas(
            'booking',
            fn ($query) => $query->where('vehicle_id', $vehicle->id)
        )->max('odometer_start');

        $returnOdometer = \App\Models\VehicleReturn::whereHas(
            'vehicleUsage.booking',
            fn ($query) => $query->where('vehicle_id', $vehicle->id)
        )->max('odometer_end');

        $fuelOdometer = FuelTransaction::where('vehicle_id', $vehicle->id)
            ->where('id', '!=', $transactionId)
            ->max('odometer');

        $readings = array_filter(
            [$usageOdometer, $returnOdometer, $fuelOdometer],
            fn ($value) => $value !== null
        );

        return empty($readings) ? null : (int) max($readings);
    }
}
