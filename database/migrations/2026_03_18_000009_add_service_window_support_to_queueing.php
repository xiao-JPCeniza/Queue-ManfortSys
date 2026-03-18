<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->unsignedSmallInteger('service_window_count')
                ->default(1)
                ->after('next_number');
        });

        Schema::table('queue_entries', function (Blueprint $table) {
            $table->unsignedSmallInteger('service_window_number')
                ->nullable()
                ->after('status');

            $table->index(['office_id', 'status', 'service_window_number'], 'queue_entries_office_status_window_index');
        });
    }

    public function down(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            $table->dropIndex('queue_entries_office_status_window_index');
            $table->dropColumn('service_window_number');
        });

        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn('service_window_count');
        });
    }
};
