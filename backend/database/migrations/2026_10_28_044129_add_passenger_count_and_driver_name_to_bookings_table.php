<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedSmallInteger('passenger_count')
                ->nullable()
                ->after('purpose');

            $table->string('driver_name', 150)
                ->nullable()
                ->after('passenger_count');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'passenger_count',
                'driver_name',
            ]);
        });
    }
};
