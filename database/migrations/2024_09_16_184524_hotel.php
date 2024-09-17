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
        Schema::create('hotel', function (Blueprint $table) {
            $table->id();
            $table->string('nom',255);
            $table->string('email')->unique();
            $table->string('adresse',255)->nullable();
            $table->string('tel');
            $table->string('logo',255);
            $table->integer('etoile');
            $table->enum('statut', array_column(HotelStatus::cases(),'value'));
            $table->timestamp('createdAt');
            $table->timestamp('updateAt')->useCurrentOnUpdate()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('hotel');
    }
};
