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
        Schema::create('racks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rack_level_id')->constrained('rack_levels')->cascadeOnDelete();
            $table->foreignId('rack_block_id')->constrained('rack_blocks')->cascadeOnDelete();
            $table->string('code');
            $table->decimal('capacity_kg', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('racks');
    }
};
