
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_returns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vehicle_usage_id')
                ->unique()
                ->constrained('vehicle_usages')
                ->cascadeOnDelete();

            $table->dateTime('actual_return_datetime');

            $table->unsignedInteger('odometer_end');

            $table->text('vehicle_condition');

            $table->text('return_notes')->nullable();

            $table->foreignId('recorded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_returns');
    }
};
