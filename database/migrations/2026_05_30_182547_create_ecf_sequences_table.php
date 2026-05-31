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
        Schema::create('ecf_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // e.g., 31, 32, 33, 34, 41, 43, 44, 45, 46, 47
            $table->string('description')->nullable();
            $table->unsignedBigInteger('start_range');
            $table->unsignedBigInteger('end_range');
            $table->unsignedBigInteger('current_sequence');
            $table->dateTime('expiration_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecf_sequences', function (Blueprint $table) {
            //
        });
    }
};
