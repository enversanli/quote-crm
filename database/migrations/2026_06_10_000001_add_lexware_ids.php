<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('lexware_contact_id')->nullable()->after('notes');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->string('lexware_quotation_id')->nullable()->after('notes');
            $table->string('lexware_invoice_id')->nullable()->after('lexware_quotation_id');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('lexware_contact_id');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['lexware_quotation_id', 'lexware_invoice_id']);
        });
    }
};
