<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $windowCounts = [
            'hrmo' => 5,
            'treasury' => 5,
            'business-permits' => 5,
            'bplo' => 5,
            'menro' => 7,
            'mswdo' => 7,
        ];

        foreach ($windowCounts as $slug => $windowCount) {
            DB::table('offices')
                ->where('slug', $slug)
                ->update(['service_window_count' => $windowCount]);
        }
    }

    public function down(): void
    {
        $windowCounts = [
            'hrmo' => 1,
            'treasury' => 8,
            'business-permits' => 1,
            'bplo' => 1,
            'menro' => 1,
            'mswdo' => 1,
        ];

        foreach ($windowCounts as $slug => $windowCount) {
            DB::table('offices')
                ->where('slug', $slug)
                ->update(['service_window_count' => $windowCount]);
        }
    }
};
