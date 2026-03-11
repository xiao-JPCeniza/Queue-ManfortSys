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
            $table->boolean('show_in_public_queue')->default(false)->after('is_active');
        });

        DB::table('offices')
            ->whereIn('slug', [
                'hrmo',
                'treasury',
                'accounting',
                'civil-registry',
                'business-permits',
                'assessors-office',
                'mho',
                'mswdo',
            ])
            ->update(['show_in_public_queue' => true]);
    }

    public function down(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn('show_in_public_queue');
        });
    }
};
