<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_lines', function (Blueprint $table) {
            $table->boolean('include_in_total')->default(true)->after('discount_value');
        });
    }

    public function down(): void
    {
        Schema::table('quote_lines', function (Blueprint $table) {
            $table->dropColumn('include_in_total');
        });
    }
};
