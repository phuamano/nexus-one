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
        Schema::create('inventory_movements', function (Blueprint $table) {

            $table->uuid('id')->primary();

            $table->foreignUuid('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignUuid('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignUuid('warehouse_id')
                ->constrained()
                ->cascadeOnDelete();


            $table->string('type');
            // purchase, sale, adjustment, transfer, return


            $table->string('direction');
            // in, out


            $table->decimal('quantity', 12, 3);


            /*
             * Referencia al documento origen:
             * Purchase, Sale, Transfer, etc.
             */
            $table->nullableMorphs('reference');


            $table->text('notes')->nullable();


            $table->foreignUuid('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();


            $table->timestamps();


            $table->index([
                'company_id',
                'product_id',
                'warehouse_id'
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
