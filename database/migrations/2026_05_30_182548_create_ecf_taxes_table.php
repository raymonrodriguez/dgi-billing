<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ecf_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecf_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // e.g., ITBIS, ISC...
            $table->decimal('rate', 5, 2);
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecf_taxes', function (Blueprint $table) {
            //
        });
    }
};
