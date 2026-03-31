<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Domain;
use App\Models\DailyQuerySummary;
use App\Models\Query;
use Carbon\Carbon;

class KeywordTable extends Component
{
    use WithPagination;

    public $domain;
    public $lookbackDays = 30;

    public $searchQuery = '';
    public $minImpressions = '';
    public $minClicks = '';

    public $sortField = 'sum_clicks';
    public $sortDirection = 'desc';

    protected $queryString = [
        'searchQuery' => ['except' => ''],
        'minImpressions' => ['except' => ''],
        'minClicks' => ['except' => ''],
        'sortField' => ['except' => 'sum_clicks'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function mount(Domain $domain)
    {
        $this->domain = $domain;
    }

    public function resetFilters()
    {
        $this->reset('searchQuery', 'minImpressions', 'minClicks', 'sortField', 'sortDirection');
    }

    public function render()
    {
        $startDate = Carbon::now()->subDays($this->lookbackDays)->format('Y-m-d');

        $queryIdsQuery = DailyQuerySummary::where('domain_id', $this->domain->id)
            ->whereDate('stat_date', '>=', $startDate)
            ->selectRaw('
                query_id,
                SUM(total_clicks) as sum_clicks,
                SUM(total_impressions) as sum_impressions,
                AVG(avg_ctr) as mean_ctr,
                AVG(avg_position) as mean_position
            ')
            ->groupBy('query_id');

        if ($this->minImpressions !== '') {
            $queryIdsQuery->having('sum_impressions', '>=', (int) $this->minImpressions);
        }

        if ($this->minClicks !== '') {
            $queryIdsQuery->having('sum_clicks', '>=', (int) $this->minClicks);
        }

        $aggregatedData = collect($queryIdsQuery->orderBy($this->sortField, $this->sortDirection)->get())->keyBy('query_id');

        $q = Query::where('domain_id', $this->domain->id)
            ->whereIn('id', $aggregatedData->keys());

        if ($this->searchQuery) {
            $q->where('query', 'like', '%' . $this->searchQuery . '%');
        }

        // Maintain the order from the aggregated data
        $ids = $aggregatedData->keys()->toArray();
        if (!empty($ids)) {
            $q->orderByRaw('CASE id ' . implode(' ', array_map(fn($id, $i) => "WHEN {$id} THEN {$i}", $ids, array_keys($ids))) . ' END');
        }

        $keywords = $q->paginate(50);

        // Fetch the latest date available in summaries for this domain
        $latestDate = DailyQuerySummary::where('domain_id', $this->domain->id)->max('stat_date');

        // Period-over-Period comparison logic
        $currentEnd = $latestDate ? Carbon::parse($latestDate)->format('Y-m-d') : now()->format('Y-m-d');
        $currentStart = Carbon::parse($currentEnd)->subDays($this->lookbackDays - 1)->format('Y-m-d');
        
        $prevEnd = Carbon::parse($currentStart)->subDay()->format('Y-m-d');
        $prevStart = Carbon::parse($prevEnd)->subDays($this->lookbackDays - 1)->format('Y-m-d');

        // Aggregate data for the previous period to compare
        $comparisonData = DailyQuerySummary::where('domain_id', $this->domain->id)
            ->whereDate('stat_date', '>=', $prevStart)
            ->whereDate('stat_date', '<=', $prevEnd)
            ->selectRaw('
                query_id,
                SUM(total_clicks) as sum_clicks,
                SUM(total_impressions) as sum_impressions,
                AVG(avg_ctr) as mean_ctr,
                AVG(avg_position) as mean_position
            ')
            ->groupBy('query_id')
            ->get()
            ->keyBy('query_id');

        return view('livewire.keyword-table', [
            'keywords' => $keywords,
            'aggregatedData' => $aggregatedData,
            'comparisonData' => $comparisonData,
        ]);
    }
}
