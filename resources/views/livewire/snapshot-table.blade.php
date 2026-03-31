<div>
    <div class="mb-4 grid grid-cols-1 md:grid-cols-6 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Query</label>
            <input type="text" wire:model.live.debounce.300ms="searchQuery" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Page URL</label>
            <input type="text" wire:model.live.debounce.300ms="searchPage" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Min Impressions</label>
            <input type="number" wire:model.live.debounce.300ms="minImpressions" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Min Clicks</label>
            <input type="number" wire:model.live.debounce.300ms="minClicks" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Position Between</label>
            <div class="flex items-center space-x-2">
                <input type="number" wire:model.live.debounce.300ms="minPosition" placeholder="Min" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <span>-</span>
                <input type="number" wire:model.live.debounce.300ms="maxPosition" placeholder="Max" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
        </div>
        <div class="flex items-end">
            <button wire:click="resetFilters" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Clear Filters
            </button>
        </div>
    </div>

    <div class="overflow-x-auto border rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Query / Page</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Clicks</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Impressions</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">CTR</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Position <br/><span class="text-[10px] normal-case text-gray-400">(GSC Avg)</span></th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Insights</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($analytics as $row)
                    @php
                        $prev = $previousData->get($row->query_id . '_' . $row->page_id);
                        $posDelta = $prev ? $prev->position - $row->position : 0; // Positive means rank improved (lower number)
                        $ctrDelta = $prev ? $row->ctr - $prev->ctr : 0;
                        $clicksDelta = $prev ? $row->clicks - $prev->clicks : 0;
                        $impDelta = $prev ? $row->impressions - $prev->impressions : 0;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900 max-w-xs break-all">
                            <div class="font-medium text-indigo-600 mb-1">
                                <a href="https://google.com/search?q={{ urlencode($row->seoQuery->query) }}" target="_blank" class="hover:underline">
                                    {{ $row->seoQuery->query }}
                                </a>
                                @if($row->seoQuery->is_branded)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 ml-2">Brand</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500 truncate" title="{{ $row->page->url }}">
                                <a href="{{ $row->page->url }}" target="_blank" class="hover:underline">{{ str_replace(['https://', 'http://'], '', $row->page->url) }}</a>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            <span class="font-medium">{{ number_format($row->clicks) }}</span>
                            @if($prev && $clicksDelta != 0)
                                <div class="text-xs {{ $clicksDelta > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $clicksDelta > 0 ? '↑' : '↓' }} {{ abs($clicksDelta) }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            <span class="font-medium">{{ number_format($row->impressions) }}</span>
                            @if($prev && $impDelta != 0)
                                <div class="text-xs {{ $impDelta > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $impDelta > 0 ? '↑' : '↓' }} {{ number_format(abs($impDelta)) }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            <span class="font-medium">{{ number_format($row->ctr * 100, 2) }}%</span>
                            @if($prev && $ctrDelta != 0)
                                <div class="text-xs {{ $ctrDelta > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $ctrDelta > 0 ? '↑' : '↓' }} {{ number_format(abs($ctrDelta) * 100, 2) }}%
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            <span class="font-medium">{{ number_format($row->position, 1) }}</span>
                            @if($prev && $posDelta != 0)
                                <div class="text-xs {{ $posDelta > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $posDelta > 0 ? '↑' : '↓' }} {{ number_format(abs($posDelta), 1) }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            @if($row->impressions >= 20 && $row->position >= 8 && $row->position <= 30 && $row->clicks < 5)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Quick Win</span>
                            @elseif($row->impressions >= 100 && $row->ctr < 0.02 && $row->position < 15)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Weak Snippet</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No data available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $analytics->links() }}
    </div>
</div>
