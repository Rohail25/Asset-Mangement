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
        Schema::create('asset_audit_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('file_id')->nullable()->constrained('asset_audit_files')->nullOnDelete(); // which upload/day this row belongs to
            $table->foreignId('auditor_id')->nullable()->constrained('auditors')->nullOnDelete();

            // key/value pairs under the field 'name's defined in asset_audit_fields
            $table->json('data');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_audit_rows');
    }
};
