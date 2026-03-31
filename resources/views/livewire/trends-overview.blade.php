<div class="space-y-6">
    <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border">
        <div>
            <h3 class="text-lg font-medium text-gray-900">Growth Analysis</h3>
            <p class="text-sm text-gray-500">Comparing current period against previous period.</p>
        </div>
        <select wire:model.live="lookbackDays" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="3">Last 3 Days</option>
            <option value="7">Last 7 Days</option>
            <option value="14">Last 14 Days</option>
            <option value="30">Last 30 Days</option>
        </select>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Keyword Gainers -->
        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden border">
            <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Trending Keywords (Impression Growth)</h3>
            </div>
            <div class="flow-root">
                <ul role="list" class="divide-y divide-gray-200">
                    @forelse($keywordGainers as $gainer)
                        @php $q = $queries->get($gainer['query_id']); @endphp
                        @if($q)
                        <li class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-medium text-indigo-600 truncate">
                                    {{ $q->query }}
                                </div>
                                <div class="ml-2 flex-shrink-0 flex">
                                    <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        +{{ number_format($gainer['imp_gain']) }} imps
                                    </p>
                                </div>
                            </div>
                            <div class="mt-2 flex justify-between">
                                <div class="sm:flex">
                                    <p class="flex items-center text-xs text-gray-500">
                                        Position: {{ number_format($gainer['position'], 1) }}
                                        @if($gainer['pos_change'] > 0)
                                            <span class="text-green-600 ml-1">↑ {{ number_format($gainer['pos_change'], 1) }}</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="text-xs text-gray-400">
                                    @if($gainer['is_new'])
                                        <span class="text-blue-600 font-bold uppercase">New Keyword</span>
                                    @endif
                                </div>
                            </div>
                        </li>
                        @endif
                    @empty
                        <li class="px-4 py-8 text-center text-gray-500">No trending keywords found.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Page Gainers -->
        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden border">
            <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Trending Pages (Click Growth)</h3>
            </div>
            <div class="flow-root">
                <ul role="list" class="divide-y divide-gray-200">
                    @forelse($pageGainers as $gainer)
                        @php $p = $pages->get($gainer['page_id']); @endphp
                        @if($p)
                        <li class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-medium text-indigo-600 truncate max-w-[250px]" title="{{ $p->url }}">
                                    {{ $p->path }}
                                </div>
                                <div class="ml-2 flex-shrink-0 flex">
                                    <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        +{{ number_format($gainer['click_gain']) }} clicks
                                    </p>
                                </div>
                            </div>
                            <div class="mt-2 flex justify-between">
                                <div class="text-xs text-gray-500">
                                    Total Clicks: {{ number_format($gainer['clicks']) }}
                                </div>
                                <div class="text-xs text-gray-400">
                                    Imps Gain: +{{ number_format($gainer['imp_gain']) }}
                                </div>
                            </div>
                        </li>
                        @endif
                    @empty
                        <li class="px-4 py-8 text-center text-gray-500">No trending pages found.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
