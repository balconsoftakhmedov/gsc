<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            GSC Sync Logs
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @php
                $syncMessage = '';
                if (request()->has('trigger_sync')) {
                    \Illuminate\Support\Facades\Artisan::call('seo:sync-gsc');
                    $syncMessage = 'Sync completed successfully!';
                }
            @endphp

            <div class="bg-white p-6 rounded-lg shadow-sm border mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Manual Data Sync</h3>
                    <p class="text-sm text-gray-500">Trigger a manual fetch of data from Google Search Console.</p>
                    @if($syncMessage)
                        <div class="mt-2 text-sm text-green-600 font-bold">{{ $syncMessage }}</div>
                    @endif
                </div>

                <div class="flex-shrink-0">
                    <a href="?trigger_sync=1" 
                       class="inline-flex items-center px-4 py-2 border border-black text-xs font-bold rounded-lg shadow-sm bg-green-500 hover:bg-green-600 transition-all"
                       style="color: black !important;">
                        <svg class="w-3 h-3 mr-1 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        <span style="color: black !important;">SYNC DATA NOW</span>
                    </a>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @php
                    $syncRuns = \App\Models\SyncRun::with('domain')->orderBy('started_at', 'desc')->paginate(50);
                @endphp

                <div class="overflow-x-auto border rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Started At</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Domain</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Fetched</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ins/Upd</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($syncRuns as $run)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $run->started_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $run->domain->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $run->target_date->format('Y-m-d') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($run->status === 'completed')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Success</span>
                                        @elseif($run->status === 'failed')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800" title="{{ $run->error_message }}">Failed</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Running</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($run->rows_fetched) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ $run->rows_inserted }} / {{ $run->rows_updated }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($run->finished_at)
                                            {{ max(0, $run->finished_at->diffInSeconds($run->started_at)) }}s
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @if($run->status === 'failed' && $run->error_message)
                                    <tr class="bg-red-50">
                                        <td colspan="7" class="px-6 py-2 text-xs text-red-600">
                                            <strong>Error:</strong> {{ $run->error_message }}
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No sync runs recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $syncRuns->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
