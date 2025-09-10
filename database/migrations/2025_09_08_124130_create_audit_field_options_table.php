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
        Schema::create('audit_field_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_id')->constrained('asset_audit_fields')->cascadeOnDelete();
            $table->string('value');
            $table->unsignedSmallInteger('order_index')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_field_options');
    }
};
