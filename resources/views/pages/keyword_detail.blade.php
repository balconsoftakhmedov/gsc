<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Keyword: {{ $query->query }}
            </h2>
            <div class="text-sm text-gray-500">
                Domain: {{ $query->domain->name }}
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Performance Over Time (Last 30 Days)</h3>
                
                @php
                    $summaries = \App\Models\DailyQuerySummary::where('query_id', $query->id)
                        ->where('stat_date', '>=', now()->subDays(30))
                        ->orderBy('stat_date', 'asc')
                        ->get();
                    
                    $chartData = [
                        'dates' => $summaries->pluck('stat_date')->map(fn($d) => $d->format('M d'))->toArray(),
                        'clicks' => $summaries->pluck('total_clicks')->toArray(),
                        'impressions' => $summaries->pluck('total_impressions')->toArray(),
                        'positions' => $summaries->pluck('avg_position')->toArray(),
                    ];
                @endphp

                <div id="keyword-chart" class="h-80"></div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Top Pages for this Keyword</h3>
                @php
                    $pages = \App\Models\DailySearchAnalytic::with('page')
                        ->where('query_id', $query->id)
                        ->selectRaw('page_id, SUM(clicks) as total_clicks, SUM(impressions) as total_impressions, AVG(position) as avg_position')
                        ->groupBy('page_id')
                        ->orderBy('total_clicks', 'desc')
                        ->get();
                @endphp

                <div class="overflow-x-auto border rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Page</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Clicks</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Impressions</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Pos</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($pages as $row)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-indigo-600 break-all">
                                        <a href="{{ route('pages.show', $row->page) }}" class="hover:underline">
                                            {{ $row->page->path }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right">{{ number_format($row->total_clicks) }}</td>
                                    <td class="px-6 py-4 text-sm text-right">{{ number_format($row->total_impressions) }}</td>
                                    <td class="px-6 py-4 text-sm text-right">{{ number_format($row->avg_position, 1) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No page data available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const chartData = @json($chartData);
            if (!chartData.dates || chartData.dates.length === 0) return;

            const options = {
                series: [
                    { name: 'Clicks', type: 'column', data: chartData.clicks },
                    { name: 'Impressions', type: 'line', data: chartData.impressions },
                    { name: 'Position', type: 'line', data: chartData.positions }
                ],
                chart: { height: 320, type: 'line', toolbar: { show: false }, animations: { enabled: false } },
                stroke: { width: [0, 2, 3], curve: 'smooth' },
                xaxis: { categories: chartData.dates },
                yaxis: [
                    { 
                        title: { text: 'Clicks' },
                        labels: { formatter: (val) => Math.round(val) }
                    },
                    { 
                        opposite: true, 
                        title: { text: 'Impressions' },
                        labels: { formatter: (val) => Math.round(val) }
                    },
                    { 
                        opposite: true, 
                        reversed: true, 
                        title: { text: 'Position' },
                        labels: { formatter: (val) => val.toFixed(1) }
                    }
                ],
                colors: ['#3b82f6', '#c084fc', '#10b981']
            };
            const el = document.querySelector("#keyword-chart");
            if (el) {
                el.innerHTML = '';
                new ApexCharts(el, options).render();
            }
        });
    </script>
</x-app-layout>
