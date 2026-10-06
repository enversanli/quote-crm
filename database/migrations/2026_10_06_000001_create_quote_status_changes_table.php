<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_status_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('comment')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        // Starting point for existing quotes: their current status, no earlier history known.
        $now = now();

        DB::table('quotes')->select(['id', 'status'])->orderBy('id')->each(function ($quote) use ($now) {
            DB::table('quote_status_changes')->insert([
                'quote_id'    => $quote->id,
                'from_status' => null,
                'to_status'   => $quote->status ?? 'draft',
                'comment'     => 'Status when history tracking started.',
                'user_id'     => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_status_changes');
    }
};
