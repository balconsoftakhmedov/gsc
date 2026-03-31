<div class="space-y-10" x-data="{ tooltip: '', x: 0, y: 0 }">
    <!-- Floating Info Modal -->
    <div x-show="tooltip" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="fixed z-[100] px-3 py-2 text-xs font-bold text-indigo-700 bg-white border-2 border-indigo-500 rounded-lg shadow-2xl max-w-xs break-all pointer-events-none"
         :style="`left: ${x + 15}px; top: ${y + 15}px;`"
         x-text="tooltip"
         style="display: none;">
    </div>

    <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border">
        <div class="flex space-x-1 p-1 bg-gray-100 rounded-lg">
            <button wire:click="setTab('keywords')" class="px-4 py-2 text-sm font-medium rounded-md {{ $activeTab === 'keywords' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                Keywords
            </button>
            <button wire:click="setTab('pages')" class="px-4 py-2 text-sm font-medium rounded-md {{ $activeTab === 'pages' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                Pages
            </button>
        </div>
        <select wire:model.live="lookbackDays" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="3">Last 3 Days</option>
            <option value="7">Last 7 Days</option>
            <option value="14">Last 14 Days</option>
            <option value="30">Last 30 Days</option>
        </select>
    </div>

    @if($activeTab === 'keywords')
        <div>
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Keyword Trends</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Keyword Winners -->
                <div class="bg-white shadow-sm rounded-lg border overflow-hidden">
                    <div class="px-4 py-3 bg-green-50 border-b flex justify-between items-center">
                        <h3 class="font-bold text-green-800">Top Keyword Gainers</h3>
                        <span class="text-xs text-green-600">Page {{ $kwPage }}</span>
                    </div>
                    <div class="w-full overflow-hidden">
                        <table class="w-full divide-y divide-gray-200 table-fixed">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="w-1/2 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Query</th>
                                    <th scope="col" class="w-1/4 px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Gain</th>
                                    <th scope="col" class="w-1/4 px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Pos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($keywordWinners as $gainer)
                                    @php $q = $queries->get($gainer['query_id']); @endphp
                                    @if($q)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm overflow-hidden">
                                            <div class="flex items-center space-x-1 overflow-hidden">
                                                <div class="w-32 lg:w-48 truncate font-medium text-indigo-600 block overflow-hidden whitespace-nowrap" 
                                                     @mouseleave="tooltip = ''" 
                                                     @mousemove="x = $event.clientX; y = $event.clientY" 
                                                     @mouseenter="tooltip = '{{ addslashes($q->query) }}'; x = $event.clientX; y = $event.clientY" 
                                                     style="overflow: hidden; width: 250px;">
                                                    <a href="{{ route('keywords.show', $q) }}" class="hover:underline">{{ $q->query }}</a>
                                                </div>
                                                @if($gainer['is_new']) <span class="flex-shrink-0 text-[8px] font-bold text-blue-500 uppercase">New</span> @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-bold text-green-600">+{{ number_format($gainer['imp_gain']) }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-500">
                                            {{ number_format($gainer['position'], 1) }}
                                            @if($gainer['pos_change'] > 0) <span class="text-green-600 text-[10px] block font-bold">↑{{ number_format($gainer['pos_change'], 1) }}</span> @endif
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-2 bg-gray-50 border-t flex justify-between">
                        <button wire:click="prevPage('kwPage')" @if($kwPage == 1) disabled @endif class="text-xs font-medium text-gray-500 hover:text-indigo-600 disabled:opacity-30">&larr; Prev</button>
                        <button wire:click="nextPage('kwPage')" @if(!$hasMoreKw) disabled @endif class="text-xs font-medium text-gray-500 hover:text-indigo-600 disabled:opacity-30">Next &rarr;</button>
                    </div>
                </div>

                <!-- Keyword Losers -->
                <div class="bg-white shadow-sm rounded-lg border overflow-hidden">
                    <div class="px-4 py-3 bg-red-50 border-b flex justify-between items-center">
                        <h3 class="font-bold text-red-800">Top Keyword Losers</h3>
                        <span class="text-xs text-red-600">Page {{ $klPage }}</span>
                    </div>
                    <div class="w-full overflow-hidden">
                        <table class="w-full divide-y divide-gray-200 table-fixed">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="w-1/2 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Query</th>
                                    <th scope="col" class="w-1/4 px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Loss</th>
                                    <th scope="col" class="w-1/4 px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Pos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($keywordLosers as $loser)
                                    @php $q = $queries->get($loser['query_id']); @endphp
                                    @if($q)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm overflow-hidden">
                                            <div class="w-32 lg:w-48 truncate font-medium text-gray-700 block overflow-hidden whitespace-nowrap" 
                                                 @mouseleave="tooltip = ''" 
                                                 @mousemove="x = $event.clientX; y = $event.clientY" 
                                                 @mouseenter="tooltip = '{{ addslashes($q->query) }}'; x = $event.clientX; y = $event.clientY" 
                                                 style="overflow: hidden; width: 250px;">
                                                <a href="{{ route('keywords.show', $q) }}" class="hover:underline">{{ $q->query }}</a>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-bold text-red-600">{{ number_format($loser['imp_gain']) }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-500">
                                            {{ number_format($loser['position'], 1) }}
                                            @if($loser['pos_change'] < 0) <span class="text-red-600 text-[10px] block font-bold">↓{{ number_format(abs($loser['pos_change']), 1) }}</span> @endif
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-2 bg-gray-50 border-t flex justify-between">
                        <button wire:click="prevPage('klPage')" @if($klPage == 1) disabled @endif class="text-xs font-medium text-gray-500 hover:text-indigo-600 disabled:opacity-30">&larr; Prev</button>
                        <button wire:click="nextPage('klPage')" @if(!$hasMoreKl) disabled @endif class="text-xs font-medium text-gray-500 hover:text-indigo-600 disabled:opacity-30">Next &rarr;</button>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div>
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Page Trends</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Page Winners -->
                <div class="bg-white shadow-sm rounded-lg border overflow-hidden">
                    <div class="px-4 py-3 bg-blue-50 border-b flex justify-between items-center">
                        <h3 class="font-bold text-blue-800">Top Page Gainers</h3>
                        <span class="text-xs text-blue-600">Page {{ $pwPage }}</span>
                    </div>
                    <div class="w-full overflow-hidden">
                        <table class="w-full divide-y divide-gray-200 table-fixed">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="w-1/2 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Path</th>
                                    <th scope="col" class="w-1/4 px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Gain</th>
                                    <th scope="col" class="w-1/4 px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Pos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($pageWinners as $gainer)
                                    @php $p = $pages->get($gainer['page_id']); @endphp
                                    @if($p)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm overflow-hidden">
                                            <div class="w-32 lg:w-48 truncate font-medium text-indigo-600 block overflow-hidden whitespace-nowrap" 
                                                 @mouseleave="tooltip = ''" 
                                                 @mousemove="x = $event.clientX; y = $event.clientY" 
                                                 @mouseenter="tooltip = '{{ addslashes($p->url) }}'; x = $event.clientX; y = $event.clientY" 
                                                 style="overflow: hidden; width: 250px;">
                                                <a href="{{ route('pages.show', $p) }}" class="hover:underline">{{ $p->path }}</a>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-bold text-green-600">+{{ number_format($gainer['imp_gain']) }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-500">
                                            {{ number_format($gainer['position'], 1) }}
                                            @if($gainer['pos_change'] > 0) <span class="text-green-600 text-[10px] block font-bold">↑{{ number_format($gainer['pos_change'], 1) }}</span> @endif
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-2 bg-gray-50 border-t flex justify-between">
                        <button wire:click="prevPage('pwPage')" @if($pwPage == 1) disabled @endif class="text-xs font-medium text-gray-500 hover:text-indigo-600 disabled:opacity-30">&larr; Prev</button>
                        <button wire:click="nextPage('pwPage')" @if(!$hasMorePw) disabled @endif class="text-xs font-medium text-gray-500 hover:text-indigo-600 disabled:opacity-30">Next &rarr;</button>
                    </div>
                </div>

                <!-- Page Losers -->
                <div class="bg-white shadow-sm rounded-lg border overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 border-b flex justify-between items-center">
                        <h3 class="font-bold text-gray-800">Top Page Losers</h3>
                        <span class="text-xs text-gray-600">Page {{ $plPage }}</span>
                    </div>
                    <div class="w-full overflow-hidden">
                        <table class="w-full divide-y divide-gray-200 table-fixed">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="w-1/2 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Path</th>
                                    <th scope="col" class="w-1/4 px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Loss</th>
                                    <th scope="col" class="w-1/4 px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Pos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($pageLosers as $loser)
                                    @php $p = $pages->get($loser['page_id']); @endphp
                                    @if($p)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm overflow-hidden">
                                            <div class="w-32 lg:w-48 truncate font-medium text-gray-700 block overflow-hidden whitespace-nowrap" 
                                                 @mouseleave="tooltip = ''" 
                                                 @mousemove="x = $event.clientX; y = $event.clientY" 
                                                 @mouseenter="tooltip = '{{ addslashes($p->url) }}'; x = $event.clientX; y = $event.clientY" 
                                                 style="overflow: hidden; width: 250px;">
                                                <a href="{{ route('pages.show', $p) }}" class="hover:underline">{{ $p->path }}</a>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-bold text-red-600">{{ number_format($loser['imp_gain']) }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-500">
                                            {{ number_format($loser['position'], 1) }}
                                            @if($loser['pos_change'] < 0) <span class="text-red-600 text-[10px] block font-bold">↓{{ number_format(abs($loser['pos_change']), 1) }}</span> @endif
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-2 bg-gray-50 border-t flex justify-between">
                        <button wire:click="prevPage('plPage')" @if($plPage == 1) disabled @endif class="text-xs font-medium text-gray-500 hover:text-indigo-600 disabled:opacity-30">&larr; Prev</button>
                        <button wire:click="nextPage('plPage')" @if(!$hasMorePl) disabled @endif class="text-xs font-medium text-gray-500 hover:text-indigo-600 disabled:opacity-30">Next &rarr;</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
