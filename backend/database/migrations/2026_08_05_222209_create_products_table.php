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
        Schema::create('products', function (Blueprint $table) {

            $table->uuid('id')->primary();

            $table->foreignUuid('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignUuid('product_category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignUuid('brand_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignUuid('unit_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();


            $table->string('name');

            // Código interno de la empresa
            $table->string('sku')
                ->nullable();

            // Código de barras
            $table->string('barcode')
                ->nullable();


            // Datos comerciales
            $table->text('description')
                ->nullable();

            // Costos y precios
            $table->decimal('cost', 12, 2)
                ->default(0);

            $table->decimal('price', 12, 2)
                ->default(0);


            // Inventario
            $table->decimal('stock_min', 12, 2)
                ->default(0);


            // Estado
            $table->boolean('is_active')
                ->default(true);


            $table->timestamps();


            $table->unique([
                'company_id',
                'sku'
            ]);

            $table->unique([
                'company_id',
                'barcode'
            ]);

            $table->index('company_id');
            $table->index('product_category_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
