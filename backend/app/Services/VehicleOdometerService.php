<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleUsage;
use App\Models\VehicleReturn;
use App\Models\FuelTransaction;

class VehicleOdometerService
{
    /**
     * Mendapatkan odometer tertinggi yang pernah tercatat
     * dari seluruh aktivitas kendaraan.
     */
    public function getLatestOdometer(Vehicle $vehicle): ?int
    {
        // Odometer awal penggunaan kendaraan.
        $usageOdometer = VehicleUsage::whereHas(
            'booking',
            function ($query) use ($vehicle) {
                $query->where('vehicle_id', $vehicle->id);
            }
        )->max('odometer_start');

        // Odometer akhir pengembalian kendaraan.
        $returnOdometer = VehicleReturn::whereHas(
            'vehicleUsage.booking',
            function ($query) use ($vehicle) {
                $query->where('vehicle_id', $vehicle->id);
            }
        )->max('odometer_end');

        // Odometer saat transaksi BBM.
        $fuelOdometer = FuelTransaction::where(
            'vehicle_id',
            $vehicle->id
        )->max('odometer');

        $readings = array_filter(
            [
                $usageOdometer,
                $returnOdometer,
                $fuelOdometer,
            ],
            fn ($value) => $value !== null
        );

        if (empty($readings)) {
            return null;
        }

        return (int) max($readings);
    }
}
