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
        Schema::create('partner_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('partner_name');
            $table->foreignId('location_id')->constrained()->restrictOnDelete();
            $table->string('identity_card_number')->nullable();
            $table->string('selfie_image')->nullable();
            $table->string('front_identity_card_image')->nullable();
            $table->string('back_identity_card_image')->nullable();
            $table->boolean('is_legit')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_profiles');
    }
};
