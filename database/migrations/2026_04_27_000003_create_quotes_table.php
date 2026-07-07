<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('quote_number')->unique();

            // Linked customer (optional)
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();

            // Linked business / company (optional)
            $table->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();

            // Manual customer fields (used when no customer_id is selected)
            $table->string('customer_first_name')->nullable();
            $table->string('customer_last_name')->nullable();
            $table->string('customer_company')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();

            // Event details
            $table->date('event_date')->nullable();
            $table->time('event_start_time')->nullable();
            $table->time('event_end_time')->nullable();

            // Venue pricing
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('hours', 8, 2)->nullable();
            $table->decimal('venue_subtotal', 10, 2)->default(0);

            // Quote metadata
            $table->string('status')->default('draft');
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};