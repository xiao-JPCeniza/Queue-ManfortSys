<?php

use App\Models\Office;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            $table->string('service_key', 100)
                ->nullable()
                ->after('client_type');

            $table->index(['office_id', 'service_key'], 'queue_entries_office_service_key_index');
        });

        DB::table('offices')
            ->where('slug', 'treasury')
            ->update([
                'service_window_count' => count(Office::TREASURY_DEFAULT_SERVICE_WINDOW_LABELS),
                'service_window_labels' => json_encode(Office::TREASURY_DEFAULT_SERVICE_WINDOW_LABELS, JSON_THROW_ON_ERROR),
            ]);
    }

    public function down(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            $table->dropIndex('queue_entries_office_service_key_index');
            $table->dropColumn('service_key');
        });
    }
};
