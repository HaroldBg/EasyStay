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
        // 2024_09_23_000003_add_foreign_keys_to_users_and_hotels_tables.php
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('hotels_id')->references('id')->on('hotels')->onDelete('cascade');
        });

        Schema::table('hotels', function (Blueprint $table) {
            $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_and_hotels_tables', function (Blueprint $table) {
            //
        });
    }
};
