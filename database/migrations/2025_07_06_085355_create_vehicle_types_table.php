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
        Schema::create('vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('brand');
            $table->decimal('max_weight_kg', 8, 3);
            $table->decimal('truck_weight_kg', 8, 3);
            $table->decimal('fuel_capacity', 8, 3);
            $table->decimal('fuel_consumption', 8, 3); // Per Kilometer
            $table->string('license_type_required')->default('B1');

            $table->softDeletesDatetime();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_types');
    }
};
