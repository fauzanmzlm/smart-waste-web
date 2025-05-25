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
        Schema::create('recycling_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('center_id')->constrained('recycling_centers')->onDelete('cascade');
            $table->foreignId('waste_item_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('waste_name')->nullable();
            $table->decimal('quantity', 8, 2)->nullable();  // Assuming quantity can have decimals
            $table->string('unit')->nullable();
            $table->string('image')->nullable();  // Image field can be nullable
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recycling_histories');
    }
};
