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
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_account_id')->nullable()->after('id');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->string('stripe_product_id')->nullable()->after('id');
        });

        Schema::table('course_formats', function (Blueprint $table) {
            $table->string('stripe_price_id')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('stripe_account_id');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('stripe_product_id');
        });

        Schema::table('course_formats', function (Blueprint $table) {
            $table->dropColumn('stripe_price_id');
        });
    }
};
