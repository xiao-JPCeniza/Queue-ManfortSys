<?php

use App\Models\Office;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('offices')
            ->whereIn('slug', ['business-permits', 'bplo'])
            ->update([
                'service_window_count' => count(Office::BPLO_DEFAULT_SERVICE_WINDOW_LABELS),
                'service_window_labels' => json_encode(Office::BPLO_DEFAULT_SERVICE_WINDOW_LABELS, JSON_THROW_ON_ERROR),
            ]);
    }

    public function down(): void
    {
        DB::table('offices')
            ->whereIn('slug', ['business-permits', 'bplo'])
            ->update([
                'service_window_count' => 5,
                'service_window_labels' => null,
            ]);
    }
};
