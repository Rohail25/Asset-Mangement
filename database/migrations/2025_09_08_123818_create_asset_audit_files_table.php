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
        Schema::create('asset_audit_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('auditor_id')->nullable()->constrained('auditors')->nullOnDelete();

            // 'csv' for uploads; 'manual_day' for end-of-day
            $table->enum('type', ['csv', 'manual_day'])->default('csv');
            $table->string('label')->nullable();        // e.g., "Day 1 - John"
            $table->string('source_filename')->nullable();
            $table->unsignedInteger('rows_count')->default(0);

            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_audit_files');
    }
};
