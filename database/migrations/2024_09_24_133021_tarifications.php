<?php

use App\Enums\TarificationStatus;
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
        Schema::create('tarifications', function (Blueprint $table) {
            $table->id();
            $table->decimal('prix',8,2);
            $table->string('saison',255);
            $table->enum('status', TarificationStatus::values())->default(TarificationStatus::AVAILABLE);
            $table->foreignId('types_chambres_id')->constrained('types_chambres')->onDelete('cascade');
            $table->date('date_deb');
            $table->date('date_fin');
            $table->foreignId('users_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifications');
    }
};
