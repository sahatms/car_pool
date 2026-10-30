<?php

namespace App\Console\Commands;

use App\Models\VehicleTaxRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckVehicleTaxReminders extends Command
{
    protected $signature = 'app:check-vehicle-tax-reminders';

    protected $description = 'Memeriksa jatuh tempo pajak kendaraan dan KIR dalam 30 hari';

    public function handle(): int
    {
        $today = Carbon::today();
        $deadline = Carbon::today()->addDays(30);

        $records = VehicleTaxRecord::with('vehicle')
            ->whereNull('paid_date')
            ->whereBetween('due_date', [$today, $deadline])
            ->orderBy('due_date')
            ->get();

        if ($records->isEmpty()) {
            $this->info('Tidak ada pajak kendaraan atau KIR yang jatuh tempo dalam 30 hari ke depan.');

            return self::SUCCESS;
        }

        $this->info('Ditemukan ' . $records->count() . ' data yang perlu diperhatikan:');
        $this->newLine();

        foreach ($records as $record) {
            $vehicle = $record->vehicle;
            $plate = $vehicle?->license_plate ?? 'Nomor polisi tidak tersedia';
            $type = $record->tax_type === 'KIR'
                ? 'KIR'
                : 'Pajak kendaraan';

            $daysLeft = $today->diffInDays(
                Carbon::parse($record->due_date),
                false
            );

            $this->line('Kendaraan: ' . $plate);
            $this->line('Jenis: ' . $type);
            $this->line('Jatuh tempo: ' . Carbon::parse($record->due_date)->format('d-m-Y'));
            $this->line('Sisa waktu: ' . $daysLeft . ' hari');
            $this->line('Status: Belum dibayar');
            $this->line(str_repeat('-', 40));
        }

        return self::SUCCESS;
    }
}
