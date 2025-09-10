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
        Schema::create('asset_audit_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();

            // machine key (no spaces), e.g. serial_number
            $table->string('name');
            // human label and description
            $table->string('label');
            $table->text('description')->nullable();

            // text | number | date | textarea | dropdown | checkbox
            $table->string('type')->default('text');

            // Required flag, and if scanning is enabled
            $table->boolean('required')->default(false);
            $table->boolean('scan_enabled')->default(false);

            // Ordering of fields in the capture UI
            $table->unsignedSmallInteger('order_index')->default(0);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_audit_fields');
    }
};
