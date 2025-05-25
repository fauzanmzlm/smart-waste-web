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
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')->constrained('recycling_centers')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('category');
            $table->integer('points_cost');
            $table->integer('quantity')->nullable();
            $table->timestamp('expiry_date')->nullable();
            $table->string('image')->nullable();
            $table->string('fake_image_url')->nullable();
            $table->text('terms')->nullable();
            $table->text('redemption_instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};
