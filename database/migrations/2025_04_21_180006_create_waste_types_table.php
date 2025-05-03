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
        Schema::create('waste_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon');
            $table->string('color');
            $table->text('description');
            $table->text('tips');
            $table->string('class');
            $table->timestamps();
        });

        Schema::create('recycling_center_waste_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recycling_center_id')->constrained()->onDelete('cascade');
            $table->foreignId('waste_type_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Use a shorter, custom constraint name
            $table->unique(['recycling_center_id', 'waste_type_id'], 'unique_center_waste_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_types');
        Schema::dropIfExists('recycling_center_waste_type');
    }
};
