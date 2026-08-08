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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropForeign(['tax_id']);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('tax_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->uuid('tax_id')->nullable()->change();

            $table->foreign('tax_id')
                ->references('id')
                ->on('taxes')
                ->cascadeOnDelete();
        });
    }
};
