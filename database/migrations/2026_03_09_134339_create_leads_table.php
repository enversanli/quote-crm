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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('rating')->nullable();
            $table->string('reviews_count')->nullable();
            $table->string('address')->nullable();
            $table->string('address_2')->nullable();
            $table->string('email')->nullable();
            $table->string('status_now')->nullable();
            $table->string('website')->nullable();
            $table->string('mobile')->nullable();
            $table->string('phone')->nullable();
            $table->string('google_maps_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
