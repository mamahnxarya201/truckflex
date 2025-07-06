<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_xx_xx_create_ledgers_factual_table.php
    public function up(): void
    {
        Schema::create('ledger_factual', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_rack_id')->nullable()->constrained('racks')->nullOnDelete();
            $table->foreignId('to_rack_id')->nullable()->constrained('racks')->nullOnDelete();
            $table->decimal('quantity');
            $table->enum('movement_type', ['inbound', 'outbound', 'transfer', 'adjustment', 'return']);
            $table->foreignId('source_id')->constrained('ledger_virtual')->cascadeOnDelete();
            $table->string('source_type')->default('ledger_virtual');
            $table->foreignId('noted_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('log_time')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->text('note')->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->softDeletesDatetime();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger_factual');
    }
};
