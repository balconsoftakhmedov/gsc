<div>
    <div class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Date Range</label>
            <select wire:model.live="lookbackDays" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="7">Last 7 Days</option>
                <option value="14">Last 14 Days</option>
                <option value="30">Last 30 Days</option>
                <option value="90">Last 90 Days</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Search Page</label>
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
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Page URL</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Clicks</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Impressions</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Avg CTR</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Position</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($pages as $page)
                    @php
                        $data = $aggregatedData->get($page->id);
                    @endphp
                    @if($data)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900 break-all">
                            <a href="{{ route('pages.show', $page) }}" class="font-medium text-indigo-600 hover:underline">
                                {{ str_replace(['https://', 'http://'], '', $page->url) }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                            {{ number_format($data->sum_clicks) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            {{ number_format($data->sum_impressions) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            {{ number_format($data->mean_ctr * 100, 2) }}%
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            {{ number_format($data->mean_position, 1) }}
                        </td>
                    </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No pages found for this criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $pages->links() }}
    </div>
</div>
