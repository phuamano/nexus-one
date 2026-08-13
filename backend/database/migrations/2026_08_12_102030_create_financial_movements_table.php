<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignUuid('financial_account_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignUuid('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('type');
            $table->string('direction');

            $table->decimal('amount', 15, 2);

            $table->date('movement_date');

            $table->string('reference')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index([
                'company_id',
                'financial_account_id',
                'movement_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_movements');
    }
};
