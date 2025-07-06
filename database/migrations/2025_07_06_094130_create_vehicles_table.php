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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_type_id')->constrained()->cascadeOnDelete();
            $table->string('license_plate')->unique();
            $table->string('chassis_number')->nullable();
            $table->string('engine_number')->nullable();
            $table->integer('year')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_available')->default(true);
            $table->dateTime('last_maintenance_at')->nullable();
            $table->unsignedInteger('current_km')->default(0);
            $table->text('note')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->softDeletesDatetime();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
