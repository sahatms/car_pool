
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_tax_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vehicle_id')
                ->constrained('vehicles')
                ->restrictOnDelete();

            $table->string('tax_type');
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->decimal('amount', 12, 2)->nullable();

            $table->string('payment_proof_path')->nullable();

            $table->foreignId('recorded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['vehicle_id', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_tax_records');
    }
};
