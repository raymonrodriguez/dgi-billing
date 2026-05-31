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
            $table->string('modified_ncf')->nullable()->after('encf');
            $table->string('exemption_id')->nullable()->after('modified_ncf');
            $table->string('income_type')->nullable()->default('01')->after('exemption_id');
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
