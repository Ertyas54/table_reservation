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
        Schema::create('restaurant_ratings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->unsignedTinyInteger('rating');
            $t->timestampsTz();

            $t->unique(['user_id', 'restaurant_id']);
            $t->index(['restaurant_id', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_ratings');
    }
};
