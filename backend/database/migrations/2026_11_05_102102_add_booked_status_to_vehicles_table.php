<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE vehicles
            MODIFY status ENUM('active', 'booked', 'inactive')
            NOT NULL DEFAULT 'active'
        ");
    }

    public function down(): void
    {
        $hasBookedVehicles = DB::table('vehicles')
            ->where('status', 'booked')
            ->exists();

        if ($hasBookedVehicles) {
            throw new RuntimeException(
                'Tidak dapat menghapus status booked karena masih ada kendaraan yang sedang booked.'
            );
        }

        DB::statement("
            ALTER TABLE vehicles
            MODIFY status ENUM('active', 'inactive')
            NOT NULL DEFAULT 'active'
        ");
    }
};