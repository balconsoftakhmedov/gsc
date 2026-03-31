<div>
    <div class="mb-4 flex justify-between items-center">
        <div class="text-sm text-gray-600">
            Showing keywords with > 50 impressions and average position > 5 (High potential for growth).
        </div>
        <select wire:model.live="lookbackDays" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="3">Last 3 Days</option>
            <option value="7">Last 7 Days</option>
            <option value="30">Last 30 Days</option>
            <option value="90">Last 90 Days</option>
        </select>
    </div>

    <div class="overflow-x-auto border rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keyword</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Impressions</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Current Pos</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">CTR</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Opportunity</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($keywords as $keyword)
                    @php
                        $data = $aggregatedData->get($keyword->id);
                    @endphp
                    @if($data)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900 break-all">
                            <a href="{{ route('keywords.show', $keyword) }}" class="font-medium text-indigo-600 hover:underline">
                                {{ $keyword->query }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                            {{ number_format($data->sum_impressions) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            {{ number_format($data->mean_position, 1) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            {{ number_format($data->mean_ctr * 100, 2) }}%
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                            @if($data->mean_position > 10)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Move to Page 1</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Top 10 Push</span>
                            @endif
                        </td>
                    </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No opportunities found for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $keywords->links() }}
    </div>
</div>
