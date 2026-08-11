<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_payables', function (Blueprint $table) {

            $table->uuid('id')->primary();

            $table->foreignUuid('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignUuid('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();

            $table->foreignUuid('purchase_id')
                ->constrained('purchases')
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
                'supplier_id',
                'status',
            ]);

            $table->index([
                'company_id',
                'due_date',
            ]);

            $table->unique([
                'company_id',
                'purchase_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_payables');
    }
};
