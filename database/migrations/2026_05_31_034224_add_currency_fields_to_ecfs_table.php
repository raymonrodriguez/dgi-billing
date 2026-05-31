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
        Schema::table('ecfs', function (Blueprint $table) {
            $table->string('currency')->default('DOP')->after('type');
            $table->decimal('exchange_rate', 10, 4)->default(1.0000)->after('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecfs', function (Blueprint $table) {
            //
        });
    }
};
