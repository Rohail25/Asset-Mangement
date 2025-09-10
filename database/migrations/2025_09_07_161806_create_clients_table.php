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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            // Basic client profile
            $table->string('name');                // e.g., primary contact name
            $table->string('email')->nullable();
            $table->string('contact')->nullable(); // phone or alt contact
            $table->string('company_name');

            // Audit timeline + status
            $table->date('audit_start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('audit_status', ['Planning', 'Proposal', 'In Progress', 'Completed'])
                ->default('Planning');

            // Assigned lead auditor (optional)
            $table->unsignedBigInteger('auditor_id')->nullable();
            $table->foreign('auditor_id')->references('id')->on('auditors')->cascadeOnDelete();
            $table->string('role')->default('client'); // 'client'
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
