
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vehicle_id')
                ->constrained('vehicles')
                ->restrictOnDelete();

            $table->date('transaction_date');

            $table->string('fuel_type');

            $table->decimal('liters', 10, 2);

            $table->decimal('price_per_liter', 12, 2);

            $table->decimal('total_cost', 12, 2);

            $table->unsignedInteger('odometer')->nullable();

            $table->string('provider')->nullable();

            $table->string('receipt_path')->nullable();

            $table->foreignId('recorded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['vehicle_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_transactions');
    }
};
