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
        Schema::create('waste_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('waste_type_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('recyclable')->default(true);
            $table->json('disposal_instructions')->nullable();
            $table->text('restrictions')->nullable();
            $table->text('alternatives')->nullable();
            $table->integer('points')->default(0);
            $table->json('ocean_impact_factors')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_items');
    }
};
