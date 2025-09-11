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
        Schema::create('asset_register_rows', function (Blueprint $table) {
            $table->id();
            // which client and which heading set this data belongs to
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('register_id')->constrained('asset_registers')->cascadeOnDelete();

            // optional: if you want to track who uploaded it / which file
            $table->string('source_filename')->nullable();

            // dynamic key/value pairs following the headings
            $table->json('data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_register_rows');
    }
};
