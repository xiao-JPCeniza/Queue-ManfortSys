<?php

use App\Models\QueueEntry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            $table->string('office_name', 150)
                ->nullable()
                ->after('office_id');
            $table->string('service_label', 150)
                ->nullable()
                ->after('service_key');
            $table->string('service_window_label', 150)
                ->nullable()
                ->after('service_window_number');
        });

        QueueEntry::query()
            ->with('office')
            ->chunkById(100, function ($entries): void {
                foreach ($entries as $entry) {
                    $entry->syncContextSnapshotAttributes();

                    if ($entry->isDirty(['office_name', 'service_label', 'service_window_label'])) {
                        $entry->saveQuietly();
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            $table->dropColumn([
                'office_name',
                'service_label',
                'service_window_label',
            ]);
        });
    }
};
