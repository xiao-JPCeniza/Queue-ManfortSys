<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('window_number', 20)->nullable()->after('office_id');
            $table->unique(['office_id', 'window_number'], 'users_office_window_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_office_window_unique');
            $table->dropColumn('window_number');
        });
    }
};
