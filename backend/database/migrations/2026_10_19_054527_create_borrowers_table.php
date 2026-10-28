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
    Schema::create('borrowers', function (Blueprint $table) {
        $table->id();
        $table->string('full_name');
        $table->string('employee_number')->nullable();
        $table->string('department');
        $table->string('phone_number');
        $table->string('selfie_path');
        $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrowers');
    }
};
