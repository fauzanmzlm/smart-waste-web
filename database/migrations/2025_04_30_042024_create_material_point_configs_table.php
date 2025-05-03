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
        Schema::create('material_point_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')->constrained('recycling_centers')->onDelete('cascade');
            $table->foreignId('material_id')->constrained()->onDelete('cascade');
            $table->integer('points')->default(1);
            $table->boolean('is_enabled')->default(true);
            $table->float('multiplier')->default(1.0);
            $table->timestamps();

            // Unique constraint for center-material combination
            $table->unique(['center_id', 'material_id']);
        });

        Schema::create('bonus_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')->constrained('recycling_centers')->onDelete('cascade');
            $table->boolean('consecutive_days_enabled')->default(false);
            $table->float('consecutive_days_bonus')->default(0.5);
            $table->integer('max_consecutive_days')->default(5);
            $table->timestamps();

            // Each center can have only one bonus config
            $table->unique('center_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_point_configs');
    }
};
