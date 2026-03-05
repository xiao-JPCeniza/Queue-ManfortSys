<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->unsignedBigInteger('tickets_accommodated_total')->default(0)->after('next_number');
        });

        $totals = DB::table('queue_entries')
            ->select('office_id', DB::raw('COUNT(*) as total'))
            ->groupBy('office_id')
            ->get();

        foreach ($totals as $row) {
            DB::table('offices')
                ->where('id', $row->office_id)
                ->update(['tickets_accommodated_total' => (int) $row->total]);
        }
    }

    public function down(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn('tickets_accommodated_total');
        });
    }
};
