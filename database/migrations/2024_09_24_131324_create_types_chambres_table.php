<?php

use App\Enums\TypeChambreStatus;
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
        Schema::create('types_chambres', function (Blueprint $table) {
            $table->id();
            $table->string('name',255)->unique();
            $table->string('capacity');
            $table->enum('status', TypeChambreStatus::values())->default(TypeChambreStatus::AVAILABLE);
            $table->string('features')->nullable();
            $table->foreignId('hotel_id')->constrained('hotels')->onDelete('cascade');
            $table->foreignId('users_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('types_chambres');
    }
};
