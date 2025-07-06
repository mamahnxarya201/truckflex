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
    // database/migrations/xxxx_xx_xx_create_ledgers_virtual_table.php
    public function up(): void
    {
        Schema::create('ledger_virtual', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('to_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->decimal('quantity');
            $table->enum('movement_type', ['inbound', 'outbound', 'transfer', 'adjustment', 'return']);
            $table->foreignId('source_id')->constrained('deliveries')->cascadeOnDelete();
            $table->string('source_type')->default('deliveries');
            $table->foreignId('planned_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('planned_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->text('note')->nullable();

            $table->softDeletesDatetime();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger_virtual');
    }
};
