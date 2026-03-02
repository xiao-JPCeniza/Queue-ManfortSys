<div>
    <h1 class="text-2xl font-bold text-slate-800 mb-6">Queue Master Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        @foreach($offices as $office)
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-semibold text-slate-800">{{ $office->name }}</h3>
                        <p class="text-sm text-slate-500">{{ $office->prefix }} • Next #{{ $office->next_number }}</p>
                        <p class="text-sm text-emerald-600 font-medium mt-1">{{ $office->waiting_count }} waiting</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('queue-master.office', $office->slug) }}" class="text-sm text-emerald-600 hover:underline">Manage</a>
                        <button wire:click="resetNumbering({{ $office->id }})" wire:confirm="Reset queue numbering for {{ $office->name }} to 1?"
                                class="text-sm text-amber-600 hover:underline">Reset #</button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <h2 class="px-4 py-3 font-semibold text-slate-800 border-b border-slate-200">Recent queue activity</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="text-left px-4 py-2">Office</th>
                        <th class="text-left px-4 py-2">Queue #</th>
                        <th class="text-left px-4 py-2">Status</th>
                        <th class="text-left px-4 py-2">Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentEntries as $entry)
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-2">{{ $entry->office->name }}</td>
                            <td class="px-4 py-2 font-medium">{{ $entry->queue_number }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-0.5 rounded text-xs
                                    @if($entry->status === 'waiting') bg-amber-100 text-amber-800
                                    @elseif($entry->status === 'serving') bg-blue-100 text-blue-800
                                    @else bg-slate-100 text-slate-600 @endif">
                                    {{ $entry->status }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-slate-500">{{ $entry->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500">No active queue entries.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
