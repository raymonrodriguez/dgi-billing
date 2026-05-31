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
        Schema::create('received_ecfs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->string('rnc_emisor');
            $table->string('encf');
            $table->decimal('total_amount', 15, 2);
            $table->string('commercial_approval_status')->default('Pending');
            $table->string('received_xml_path');
            $table->boolean('arecf_sent')->default(false); // Acuse de Recibo
            $table->boolean('acecf_sent')->default(false); // Aprobación Comercial
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('received_ecfs');
    }
};
