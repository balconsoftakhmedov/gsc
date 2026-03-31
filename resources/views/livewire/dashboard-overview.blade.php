<div class="space-y-6">
    <div class="flex justify-between items-center">
        <select wire:model.live="selectedDomainId" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach($domains as $domain)
                <option value="{{ $domain->id }}">{{ $domain->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="lookbackDays" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="7">Last 7 Days</option>
            <option value="30">Last 30 Days</option>
            <option value="90">Last 90 Days</option>
        </select>
    </div>

    @if($latestSummary)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Clicks -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                <div class="text-sm font-medium text-gray-500 truncate">Total Clicks</div>
                <div class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($latestSummary->total_clicks) }}</div>
                @if($previousSummary)
                    <div class="text-sm {{ $latestSummary->total_clicks >= $previousSummary->total_clicks ? 'text-green-600' : 'text-red-600' }}">
                        {{ $latestSummary->total_clicks >= $previousSummary->total_clicks ? '↑' : '↓' }}
                        {{ abs($latestSummary->total_clicks - $previousSummary->total_clicks) }} vs yesterday
                    </div>
                @endif
            </div>

            <!-- Impressions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-purple-500">
                <div class="text-sm font-medium text-gray-500 truncate">Total Impressions</div>
                <div class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($latestSummary->total_impressions) }}</div>
                @if($previousSummary)
                    <div class="text-sm {{ $latestSummary->total_impressions >= $previousSummary->total_impressions ? 'text-green-600' : 'text-red-600' }}">
                        {{ $latestSummary->total_impressions >= $previousSummary->total_impressions ? '↑' : '↓' }}
                        {{ number_format(abs($latestSummary->total_impressions - $previousSummary->total_impressions)) }} vs yesterday
                    </div>
                @endif
            </div>

            <!-- Average CTR -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-500">
                <div class="text-sm font-medium text-gray-500 truncate">Average CTR</div>
                <div class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($latestSummary->avg_ctr * 100, 2) }}%</div>
                @if($previousSummary)
                    <div class="text-sm {{ $latestSummary->avg_ctr >= $previousSummary->avg_ctr ? 'text-green-600' : 'text-red-600' }}">
                        {{ $latestSummary->avg_ctr >= $previousSummary->avg_ctr ? '↑' : '↓' }}
                        {{ number_format(abs($latestSummary->avg_ctr - $previousSummary->avg_ctr) * 100, 2) }}% vs yesterday
                    </div>
                @endif
            </div>

            <!-- Average Position -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500">
                <div class="text-sm font-medium text-gray-500 truncate">Average Position <span class="text-xs text-gray-400" title="Google Search Console Average Position">(GSC Avg)</span></div>
                <div class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($latestSummary->avg_position, 1) }}</div>
                @if($previousSummary)
                    <div class="text-sm {{ $latestSummary->avg_position <= $previousSummary->avg_position ? 'text-green-600' : 'text-red-600' }}">
                        {{ $latestSummary->avg_position <= $previousSummary->avg_position ? '↑' : '↓' }}
                        {{ number_format(abs($latestSummary->avg_position - $previousSummary->avg_position), 1) }} vs yesterday
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Clicks & Impressions Trend</h3>
                <div id="chart-clicks-impressions" class="h-64" wire:ignore wire:key="chart-clicks-impressions-{{ $selectedDomainId }}-{{ $lookbackDays }}"></div>
            </div>
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">CTR & Position Trend</h3>
                <div id="chart-ctr-position" class="h-64" wire:ignore wire:key="chart-ctr-position-{{ $selectedDomainId }}-{{ $lookbackDays }}"></div>
            </div>
        </div>

        <div class="mt-6 flex justify-between bg-gray-50 p-4 rounded-lg shadow-sm border">
            <div>
                <span class="text-gray-600 font-medium">Data Date:</span>
                <span class="font-bold text-gray-800">{{ $latestSummary->stat_date->format('F j, Y') }}</span>
            </div>
            <div>
                <a href="{{ route('snapshots.show', [$selectedDomainId, $latestSummary->stat_date->format('Y-m-d')]) }}" class="text-indigo-600 hover:text-indigo-900 font-medium underline">
                    View Full Snapshot for this Day &rarr;
                </a>
            </div>
        </div>
    @else
        <div class="bg-white p-6 shadow-sm sm:rounded-lg text-center text-gray-500">
            No data available for the selected domain and date range. Please run a sync.
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        (function() {
            let clicksChart = null;
            let ctrChart = null;

            function initCharts(data) {
                if (!data || !data.dates || data.dates.length === 0) return;

                const clicksEl = document.querySelector("#chart-clicks-impressions");
                const ctrEl = document.querySelector("#chart-ctr-position");

                if (!clicksEl || !ctrEl) return;

                if (clicksChart) clicksChart.destroy();
                if (ctrChart) ctrChart.destroy();

                clicksChart = new ApexCharts(clicksEl, {
                    series: [{
                        name: 'Clicks',
                        type: 'line',
                        data: data.clicks
                    }, {
                        name: 'Impressions',
                        type: 'area',
                        data: data.impressions
                    }],
                    chart: { height: 280, type: 'line', toolbar: { show: false }, animations: { enabled: false } },
                    stroke: { width: [3, 0], curve: 'smooth' },
                    xaxis: { categories: data.dates },
                    colors: ['#3b82f6', '#c084fc'],
                    yaxis: [
                        { title: { text: 'Clicks' } },
                        { opposite: true, title: { text: 'Impressions' } }
                    ]
                });
                clicksChart.render();

                ctrChart = new ApexCharts(ctrEl, {
                    series: [{
                        name: 'Position (Lower is Better)',
                        type: 'line',
                        data: data.positions
                    }],
                    chart: { height: 280, type: 'line', toolbar: { show: false }, animations: { enabled: false } },
                    stroke: { width: 3, curve: 'smooth' },
                    xaxis: { categories: data.dates },
                    colors: ['#10b981'],
                    yaxis: { reversed: true, title: { text: 'Position' } }
                });
                ctrChart.render();
            }

            document.addEventListener('livewire:initialized', () => {
                @if($latestSummary)
                    initCharts(@json($chartData));
                @endif

                Livewire.on('charts-updated', (event) => {
                    initCharts(event.chartData);
                });
            });
        })();
    </script>
</div>
