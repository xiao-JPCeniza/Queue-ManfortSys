<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            $table->string('client_type', 30)
                ->default('regular')
                ->after('queue_number');
        });

        DB::table('queue_entries')
            ->whereNull('client_type')
            ->update(['client_type' => 'regular']);
    }

    public function down(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            $table->dropColumn('client_type');
        });
    }
};
