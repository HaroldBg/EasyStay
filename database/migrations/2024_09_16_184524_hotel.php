<?php

use App\Enums\HotelStatus;
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
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('nom',255);
            $table->string('email')->unique();
            $table->string('adresse',255);
            $table->string('tel')->nullable();
            $table->string('logo',255)->default('images/blank_profile.jpeg');
            $table->integer('etoile')->nullable();
            $table->enum('status', HotelStatus::values())->default(HotelStatus::PENDING->value);
            $table->unsignedBigInteger('users_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('hotels');
    }
};
