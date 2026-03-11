<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\QueueEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class OfficeQueueReportsPdfController extends Controller
{
    public function __invoke(Request $request, ?string $office = null)
    {
        $officeSlug = $office ?? 'hrmo';
        $officeModel = $request->attributes->get('office') ?? Office::where('slug', $officeSlug)->firstOrFail();

        if (!in_array($officeModel->slug, ['hrmo', 'business-permits', 'bplo'], true)) {
            abort(404, 'Queue reports PDF is only available for HRMO and BPLO.');
        }

        $reportOfficeIds = collect([$officeModel->id]);
        $reportScopeLabel = $officeModel->name;
        if ($request->user()?->isSuperAdmin()) {
            $reportOfficeIds = Office::query()
                ->activePublicQueue()
                ->pluck('id');
            $reportScopeLabel = 'All Offices';
        }

        $manilaNow = now('Asia/Manila');
        $dbTimezone = (string) config('app.timezone', 'UTC');

        $dailyStartManila = $manilaNow->copy()->startOfDay()->subDays(6);
        $dailyEndManila = $manilaNow->copy()->endOfDay();

        $dailyEntries = QueueEntry::whereIn('office_id', $reportOfficeIds)
            ->whereBetween('created_at', [
                $dailyStartManila->copy()->setTimezone($dbTimezone),
                $dailyEndManila->copy()->setTimezone($dbTimezone),
            ])
            ->get(['created_at']);

        $dailyCountMap = $dailyEntries
            ->groupBy(fn (QueueEntry $entry) => $entry->created_at->copy()->setTimezone('Asia/Manila')->format('Y-m-d'))
            ->map(fn ($entries) => $entries->count());

        $dailyCounts = collect(range(0, 6))->map(function (int $offset) use ($dailyStartManila, $dailyCountMap) {
            $day = $dailyStartManila->copy()->addDays($offset);
            $dayKey = $day->format('Y-m-d');

            return [
                'date' => $dayKey,
                'total_tickets' => (int) ($dailyCountMap->get($dayKey, 0)),
            ];
        })
            ->filter(fn (array $row) => $row['total_tickets'] > 0)
            ->values()
            ->all();

        $weeklyStartManila = $manilaNow->copy()->startOfWeek()->subWeeks(4);
        $weeklyEndManila = $manilaNow->copy()->endOfWeek();

        $weeklyEntries = QueueEntry::whereIn('office_id', $reportOfficeIds)
            ->whereBetween('created_at', [
                $weeklyStartManila->copy()->setTimezone($dbTimezone),
                $weeklyEndManila->copy()->setTimezone($dbTimezone),
            ])
            ->get(['created_at']);

        $weeklyCountMap = $weeklyEntries
            ->groupBy(fn (QueueEntry $entry) => $entry->created_at->copy()->setTimezone('Asia/Manila')->format('oW'))
            ->map(fn ($entries) => $entries->count());

        $weeklyCounts = collect(range(0, 4))->map(function (int $offset) use ($weeklyStartManila, $weeklyCountMap) {
            $weekStart = $weeklyStartManila->copy()->addWeeks($offset);
            $weekKey = $weekStart->format('oW');

            return [
                'week' => $weekKey,
                'total_tickets' => (int) ($weeklyCountMap->get($weekKey, 0)),
            ];
        })
            ->filter(fn (array $row) => $row['total_tickets'] > 0)
            ->values()
            ->all();

        $servedCount = QueueEntry::whereIn('office_id', $reportOfficeIds)
            ->where('status', QueueEntry::STATUS_COMPLETED)
            ->count();

        $skippedCount = QueueEntry::whereIn('office_id', $reportOfficeIds)
            ->where('status', QueueEntry::STATUS_NOT_SERVED)
            ->count();

        $processedEntries = QueueEntry::whereIn('office_id', $reportOfficeIds)
            ->where('status', QueueEntry::STATUS_COMPLETED)
            ->whereNotNull('called_at')
            ->whereNotNull('served_at')
            ->get(['called_at', 'served_at']);

        $processedCount = $processedEntries->count();
        $averageSeconds = 0;

        if ($processedCount > 0) {
            $totalSeconds = $processedEntries->sum(function (QueueEntry $entry) {
                return max(0, $entry->called_at->diffInSeconds($entry->served_at));
            });

            $averageSeconds = (int) round($totalSeconds / $processedCount);
        }

        $hours = intdiv($averageSeconds, 3600);
        $minutes = intdiv($averageSeconds % 3600, 60);
        $seconds = $averageSeconds % 60;
        $averageProcessingTime = sprintf('%02dh %02dm %02ds', $hours, $minutes, $seconds);

        $filename = sprintf(
            'queue-reports-%s-%s.pdf',
            $request->user()?->isSuperAdmin() ? 'municipality-services' : $officeModel->slug,
            $manilaNow->format('Ymd-His')
        );

        $pdf = Pdf::loadView('office.queue-reports-pdf', [
            'office' => $officeModel,
            'reportScopeLabel' => $reportScopeLabel,
            'generatedAt' => $manilaNow,
            'dailyCounts' => $dailyCounts,
            'weeklyCounts' => $weeklyCounts,
            'servedCount' => $servedCount,
            'skippedCount' => $skippedCount,
            'averageProcessingTime' => $averageProcessingTime,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }
}
