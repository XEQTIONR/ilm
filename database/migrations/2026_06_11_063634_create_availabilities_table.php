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
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('instructors');
            $table->timestamp('start_at')->default(now());
            $table->timestamp('end_at')->nullable();
            $table->string('timezone')->default('UTC');
            $table->json('mon')->nullable();
            $table->json('tue')->nullable();
            $table->json('wed')->nullable();
            $table->json('thu')->nullable();
            $table->json('fri')->nullable();
            $table->json('sat')->nullable();
            $table->json('sun')->nullable();
            $table->json('exceptions')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};
