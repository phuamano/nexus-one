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
        Schema::create('account_receivables', function (Blueprint $table) {

            $table->uuid('id')->primary();

            $table->foreignUuid('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignUuid('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();

            $table->foreignUuid('sale_id')
                ->constrained('sales')
                ->restrictOnDelete();

            $table->date('issue_date');

            $table->date('due_date')
                ->nullable();

            $table->decimal('amount', 12, 2);

            $table->decimal('paid_amount', 12, 2)
                ->default(0);

            $table->decimal('balance', 12, 2);

            $table->string('status');

            $table->text('notes')
                ->nullable();

            $table->timestamps();

            $table->index([
                'company_id',
                'customer_id',
                'status',
            ]);

            $table->index([
                'company_id',
                'due_date',
            ]);

            $table->unique([
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
        Schema::dropIfExists('account_receivables');
    }
};
