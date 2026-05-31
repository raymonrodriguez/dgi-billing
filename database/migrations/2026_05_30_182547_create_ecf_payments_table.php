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
        Schema::create('ecf_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecf_id')->constrained()->cascadeOnDelete();
            $table->string('method'); // e.g., 01 (Cash), 02 (Check), 03 (Credit Card)...
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecf_payments', function (Blueprint $table) {
            //
        });
    }
};
