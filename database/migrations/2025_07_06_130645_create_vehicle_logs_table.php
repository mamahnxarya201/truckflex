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
    Schema::create('vehicle_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
        $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('delivery_id')->nullable()->constrained()->nullOnDelete();
        $table->enum('log_type', ['trip', 'fuel', 'maintenance', 'incident']);
        $table->string('title'); // judul singkat
        $table->longText('note')->nullable(); // catatan bebas
        $table->timestamp('log_time')->default(now());
        $table->boolean('is_resolved')->default(true);

        $table->softDeletesDatetime();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_logs');
    }
};
