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
        Schema::create('ecfs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('encf')->unique();
            $table->string('type'); // e.g., 31, 32...
            $table->decimal('total_amount', 15, 2);
            $table->decimal('total_tax', 15, 2);
            $table->string('dgii_status')->default('Pendiente');
            $table->string('commercial_approval_status')->nullable();
            $table->string('track_id')->nullable();
            $table->string('security_code')->nullable();
            $table->string('signed_xml_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->json('dgii_response')->nullable();
            $table->json('dgii_messages')->nullable();
            $table->dateTime('issued_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecfs');
    }
};
