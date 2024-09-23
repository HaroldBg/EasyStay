<?php

use App\Enums\DemandeSatus;
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
        Schema::create('demandes', function (Blueprint $table) {
            $table->id();
            $table->string('motif',255)->nullable();
            $table->string('nom',255);
            $table->string('email');
            $table->string('adresse',255);
            $table->enum('status', DemandeSatus::values())->default(DemandeSatus::PENDING);
            $table->foreignId('users_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('demandes');
    }
};
