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
        Schema::create('ecf_annulments', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // Tipo de e-CF
            $table->unsignedBigInteger('start_sequence');
            $table->unsignedBigInteger('end_sequence');
            $table->unsignedInteger('quantity');
            $table->string('reason');
            $table->string('status')->default('Pending');
            $table->string('xml_path')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecf_annulments');
    }
};
