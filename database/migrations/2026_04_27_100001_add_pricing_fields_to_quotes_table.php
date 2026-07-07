<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            // VAT
            $table->decimal('vat_rate', 5, 2)->default(19.00)->after('venue_subtotal');

            // Global discount (applied on the combined net subtotal)
            $table->string('discount_type')->nullable()->after('vat_rate');   // percentage | fixed
            $table->decimal('discount_value', 10, 2)->nullable()->after('discount_type');

            // Venue-specific discount (applied on hourly_rate × hours)
            $table->string('venue_discount_type')->nullable()->after('discount_value');
            $table->decimal('venue_discount_value', 10, 2)->nullable()->after('venue_discount_type');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn([
                'vat_rate',
                'discount_type',
                'discount_value',
                'venue_discount_type',
                'venue_discount_value',
            ]);
        });
    }
};