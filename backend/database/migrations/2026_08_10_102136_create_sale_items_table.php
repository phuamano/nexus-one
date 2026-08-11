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
        Schema::create('sale_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignUuid('sale_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignUuid('product_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignUuid('tax_id')
                ->constrained()
                ->restrictOnDelete();

            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('tax_amount', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('total', 12, 2);

            $table->timestamps();

            $table->index([
                'company_id',
                'sale_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
