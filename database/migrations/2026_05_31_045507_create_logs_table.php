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
        Schema::create('logs', static function (Blueprint $table): void {
            $table->id();
            $table->string('model', 50);
            $table->string('record_id')->nullable(); // Cambiado a string para soportar UUIDs si fuera necesario
            $table->json('changes')->nullable();
            $table->string('user_id')->nullable();
            $table->string('module', 100)->nullable();
            $table->string('action', 32);
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
