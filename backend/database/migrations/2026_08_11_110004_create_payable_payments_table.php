<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payable_payments', function (Blueprint $table) {

            $table->uuid('id')->primary();

            $table->foreignUuid('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignUuid('account_payable_id')
                ->constrained('account_payables')
                ->cascadeOnDelete();

            $table->foreignUuid('user_id')
                ->constrained()
                ->restrictOnDelete();

            $table->date('payment_date');

            $table->decimal('amount', 12, 2);

            $table->string('method');

            $table->string('reference')
                ->nullable();

            $table->text('notes')
                ->nullable();

            $table->timestamps();

            $table->index([
                'company_id',
                'account_payable_id',
                'payment_date',
            ]);

            $table->index([
                'company_id',
                'user_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payable_payments');
    }
};
