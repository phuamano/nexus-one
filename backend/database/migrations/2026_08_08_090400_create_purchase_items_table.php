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
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignUuid('product_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignUuid('tax_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_cost', 15, 4);
            $table->decimal('tax_amount', 15, 4);
            $table->decimal('subtotal', 15, 4);
            $table->decimal('total', 15, 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
