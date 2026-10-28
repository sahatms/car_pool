
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_maintenance', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vehicle_id')
                ->constrained('vehicles')
                ->restrictOnDelete();

            $table->enum('maintenance_type', [
                'routine_service',
                'repair',
                'spare_part_replacement',
                'other',
            ]);

            $table->date('start_date');
            $table->date('end_date')->nullable();

            $table->unsignedInteger('odometer')->nullable();
            $table->string('workshop_name')->nullable();
            $table->decimal('cost', 12, 2)->nullable();

            $table->enum('status', [
                'scheduled',
                'in_progress',
                'completed',
                'cancelled',
            ])->default('scheduled');

            $table->text('description')->nullable();
            $table->string('receipt_path')->nullable();

            $table->foreignId('recorded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['vehicle_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenance');
    }
};
