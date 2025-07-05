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
        Schema::create('rack_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('height_cm');
            $table->decimal('max_load_kg', 10, 2)->nullable();
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
        Schema::dropIfExists('rack_levels');
    }
};
