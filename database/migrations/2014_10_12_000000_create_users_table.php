<?php

use App\Enums\UserRoles;
use App\Enums\UserStatus;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nom',255);
            $table->string('prenom',255);
            $table->string('email')->unique();
            $table->string('adresse',255)->nullable();
            $table->string('tel');
            $table->string('picture',255);
            $table->string('password');
            $table->enum('role', UserRoles::values())->default(UserRoles::CLIENT->value);
            $table->enum('status', UserStatus::values())->default(UserStatus::EMAIL_CONFIRMATION_PENDING->value);
            $table->unsignedBigInteger('hotels_id')->nullable();
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
        Schema::dropIfExists('users');
    }
};
