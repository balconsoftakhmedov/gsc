<div>
    <div class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Date Range</label>
            <select wire:model.live="lookbackDays" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="3">Last 3 Days</option>
                <option value="7">Last 7 Days</option>
                <option value="14">Last 14 Days</option>
                <option value="30">Last 30 Days</option>
                <option value="90">Last 90 Days</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Search Query</label>
            <input type="text" wire:model.live.debounce.300ms="searchQuery" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
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
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Query
                    </th>
                    <th wire:click="sortBy('sum_clicks')" scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors">
                        Total Clicks
                        @if($sortField === 'sum_clicks')
                            {!! $sortDirection === 'asc' ? '&#8593;' : '&#8595;' !!}
                        @endif
                    </th>
                    <th wire:click="sortBy('sum_impressions')" scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors">
                        Total Impressions
                        @if($sortField === 'sum_impressions')
                            {!! $sortDirection === 'asc' ? '&#8593;' : '&#8595;' !!}
                        @endif
                    </th>
                    <th wire:click="sortBy('mean_ctr')" scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors">
                        Avg CTR
                        @if($sortField === 'mean_ctr')
                            {!! $sortDirection === 'asc' ? '&#8593;' : '&#8595;' !!}
                        @endif
                    </th>
                    <th wire:click="sortBy('mean_position')" scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors">
                        Avg Position
                        @if($sortField === 'mean_position')
                            {!! $sortDirection === 'asc' ? '&#8593;' : '&#8595;' !!}
                        @endif
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @php
                    $renderDelta = function($current, $previous, $field) {
                        // Format the previous value based on the field type
                        $formattedPrev = $previous;
                        if ($field === 'mean_ctr' || $field === 'avg_ctr') {
                            $formattedPrev = number_format($previous * 100, 2) . '%';
                        } elseif ($field === 'mean_position' || $field === 'avg_position') {
                            $formattedPrev = number_format($previous, 1);
                        } else {
                            $formattedPrev = number_format($previous);
                        }

                        if ($previous == 0) {
                            if ($current > 0) {
                                return '<div class="text-[10px] leading-tight mt-1 text-gray-400">prev: 0 <span class="text-green-600">(+100%)</span></div>';
                            }
                            return null;
                        }

                        $diff = (($current - $previous) / $previous) * 100;
                        $color = $diff > 0 ? 'text-green-600' : 'text-red-600';
                        $sign = $diff > 0 ? '+' : '';
                        
                        if ($field === 'mean_position' || $field === 'avg_position') {
                            $color = $diff < 0 ? 'text-green-600' : 'text-red-600';
                        }

                        $percentHtml = $diff != 0 
                            ? '<span class="'.$color.'">('.$sign.round($diff).'%)</span>' 
                            : '<span class="text-gray-400">(0%)</span>';

                        return '<div class="text-[10px] leading-tight mt-1 text-gray-400">prev: ' . $formattedPrev . ' ' . $percentHtml . '</div>';
                    };
                @endphp
                @forelse($keywords as $keyword)
                    @php
                        $data = $aggregatedData->get($keyword->id);
                        $prev = $comparisonData->get($keyword->id);
                    @endphp
                    @if($data)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900 break-all">
                            <a href="{{ route('keywords.show', $keyword) }}" class="font-medium text-indigo-600 hover:underline">
                                {{ $keyword->query }}
                            </a>
                            @if($keyword->is_branded)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 ml-2">Brand</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                            {{ number_format($data->sum_clicks) }}
                            {!! $renderDelta($data->sum_clicks, $prev->sum_clicks ?? 0, 'sum_clicks') !!}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            {{ number_format($data->sum_impressions) }}
                            {!! $renderDelta($data->sum_impressions, $prev->sum_impressions ?? 0, 'sum_impressions') !!}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            {{ number_format($data->mean_ctr * 100, 2) }}%
                            {!! $renderDelta($data->mean_ctr, $prev->mean_ctr ?? 0, 'mean_ctr') !!}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            {{ number_format($data->mean_position, 1) }}
                            {!! $renderDelta($data->mean_position, $prev->mean_position ?? 0, 'mean_position') !!}
                        </td>
                    </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No keywords found for this criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $keywords->links() }}
    </div>
</div>
