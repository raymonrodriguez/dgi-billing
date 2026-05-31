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
        Schema::create('contingencies', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->string('reason'); // e.g., Fallas en comunicaciones, Desastres naturales...
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->string('dgii_track_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contingencies', function (Blueprint $table) {
            //
        });
    }
};
