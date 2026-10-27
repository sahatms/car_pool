<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::create('vehicles', function (Blueprint $table) {
        $table->id();

        $table->enum('vehicle_type', [
            '2_wheels',
            '4_wheels',
            '6_wheels',
        ]);

        $table->string('vehicle_category');
        $table->string('brand');
        $table->string('license_plate')->unique();
        $table->string('registered_owner');

        $table->enum('ownership_status', [
            'company_owned',
            'rented',
            'borrowed',
        ]);

        $table->enum('status', [
            'active',
            'inactive',
        ])->default('active');

        $table->string('photo')->nullable();

        $table->timestamps();
        $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
