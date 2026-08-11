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
        Schema::create('sales', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignUuid('customer_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignUuid('warehouse_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignUuid('user_id')
                ->constrained()
                ->restrictOnDelete();

            $table->dateTime('sale_date');
            $table->string('reference')->nullable();
            $table->string('status');

            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index([
                'company_id',
                'sale_date',
            ]);

            $table->index([
                'company_id',
                'status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
