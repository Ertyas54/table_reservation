<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $t) {
            $t->decimal('rating_avg', 3, 2)->default(0)->after('is_active');
            $t->unsignedInteger('ratings_count')->default(0)->after('rating_avg');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $t) {
            $t->dropColumn(['rating_avg', 'ratings_count']);
        });
    }
};
