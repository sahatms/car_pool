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
    Schema::create('bookings', function (Blueprint $table) {
        $table->id();

        $table->string('booking_code')->unique();

        $table->foreignId('borrower_id')
            ->constrained('borrowers')
            ->restrictOnDelete();

        $table->foreignId('vehicle_id')
            ->constrained('vehicles')
            ->restrictOnDelete();

        $table->dateTime('start_datetime');
        $table->dateTime('end_datetime');

        $table->string('destination');
        $table->text('purpose');

        $table->enum('status', [
            'pending',
            'approved',
            'rejected',
            'cancelled',
            'completed',
        ])->default('pending');

        $table->text('admin_notes')->nullable();
        $table->timestamp('document_printed_at')->nullable();

        $table->foreignId('processed_by')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete();

        $table->timestamps();
        $table->softDeletes();

        $table->index(['vehicle_id', 'status']);
        $table->index(['start_datetime', 'end_datetime']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
