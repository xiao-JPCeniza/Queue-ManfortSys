<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->string('queue_number', 50); // e.g. MISO-001, LDRRMO-012
            $table->string('status', 20)->default('waiting'); // waiting, serving, completed, cancelled
            $table->foreignId('served_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->timestamps();

            $table->index(['office_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_entries');
    }
};
