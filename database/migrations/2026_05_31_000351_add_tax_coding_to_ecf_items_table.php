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
        Schema::table('ecf_items', function (Blueprint $table) {
            $table->string('billing_indicator')->default('1')->after('subtotal');
            $table->json('additional_taxes')->nullable()->after('billing_indicator');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecf_items', function (Blueprint $table) {
            //
        });
    }
};
