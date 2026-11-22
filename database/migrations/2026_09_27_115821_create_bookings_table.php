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
            $table->uuid('group_id')->index();
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('restaurant_id')
                ->constrained('restaurants')
                ->restrictOnDelete();
            $table->foreignId('table_id')
                ->constrained('restaurant_tables')
                ->restrictOnDelete();
            $table->foreignId('slot_id')
                ->constrained('time_slots')
                ->restrictOnDelete();
            $table->date('booking_date');
            $table->string('status', 20)->default('pending');
            $table->decimal('total_price', 10, 2)->default(0);
            $table->text('comment')->nullable();
            $table->text('admin_comment')->nullable();
            $table->timestampTz('approved_at')->nullable();
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestampsTz();

            $table->index(
                ['table_id', 'slot_id', 'booking_date', 'status'],
                'bookings_conflict_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
