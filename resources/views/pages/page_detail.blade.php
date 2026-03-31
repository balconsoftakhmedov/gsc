<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Page Detail
            </h2>
            <div class="text-sm text-gray-500">
                Domain: {{ $page->domain->name }}
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 break-all">{{ $page->url }}</h3>
                    <p class="text-sm text-gray-500">Path: {{ $page->path }}</p>
                </div>

                <h4 class="text-md font-semibold mb-4 text-gray-700">Performance Trend (Last 30 Days)</h4>
                @php
                    $summaries = \App\Models\DailyPageSummary::where('page_id', $page->id)
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
                <div id="page-chart" class="h-80"></div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Top Countries for this Page</h3>
                @php
                    $countries = \App\Models\DailySearchAnalytic::where('page_id', $page->id)
                        ->selectRaw('country, SUM(clicks) as total_clicks, SUM(impressions) as total_impressions, AVG(position) as avg_position')
                        ->groupBy('country')
                        ->orderBy('total_impressions', 'desc')
                        ->get();
                @endphp

                <div class="overflow-x-auto border rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Country</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Clicks</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Impressions</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Pos</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($countries as $row)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900 uppercase">{{ $row->country }}</td>
                                    <td class="px-6 py-4 text-sm text-right">{{ number_format($row->total_clicks) }}</td>
                                    <td class="px-6 py-4 text-sm text-right">{{ number_format($row->total_impressions) }}</td>
                                    <td class="px-6 py-4 text-sm text-right font-bold text-indigo-600">{{ number_format($row->avg_position, 1) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No country data available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Top Keywords for this Page</h3>
                @php
                    $keywords = \App\Models\DailySearchAnalytic::with('seoQuery')
                        ->where('page_id', $page->id)
                        ->selectRaw('query_id, SUM(clicks) as total_clicks, SUM(impressions) as total_impressions, AVG(position) as avg_position')
                        ->groupBy('query_id')
                        ->orderBy('total_clicks', 'desc')
                        ->get();
                @endphp

                <div class="overflow-x-auto border rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keyword</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Clicks</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Impressions</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Pos</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($keywords as $row)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-indigo-600">
                                        <a href="{{ route('keywords.show', $row->seoQuery) }}" class="hover:underline font-medium">
                                            {{ $row->seoQuery->query }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">{{ number_format($row->total_clicks) }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-500">{{ number_format($row->total_impressions) }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-500">{{ number_format($row->avg_position, 1) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No keyword data found for this page.</td></tr>
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
            const el = document.querySelector("#page-chart");
            if (el) {
                el.innerHTML = '';
                new ApexCharts(el, options).render();
            }
        });
    </script>
</x-app-layout>
