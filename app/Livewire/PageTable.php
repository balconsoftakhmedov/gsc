<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Domain;
use App\Models\DailyPageSummary;
use App\Models\Page;
use Carbon\Carbon;

class PageTable extends Component
{
    use WithPagination;

    public $domain;
    public $lookbackDays = 30;

    public $searchPage = '';
    public $minImpressions = '';
    public $minClicks = '';

    public $sortField = 'sum_clicks';
    public $sortDirection = 'desc';

    protected $queryString = [
        'searchPage' => ['except' => ''],
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
        $this->reset('searchPage', 'minImpressions', 'minClicks', 'sortField', 'sortDirection');
    }

    public function render()
    {
        $startDate = Carbon::now()->subDays($this->lookbackDays)->format('Y-m-d');

        $pageIdsQuery = DailyPageSummary::where('domain_id', $this->domain->id)
            ->whereDate('stat_date', '>=', $startDate)
            ->selectRaw('
                page_id,
                SUM(total_clicks) as sum_clicks,
                SUM(total_impressions) as sum_impressions,
                AVG(avg_ctr) as mean_ctr,
                AVG(avg_position) as mean_position
            ')
            ->groupBy('page_id');

        if ($this->minImpressions !== '') {
            $pageIdsQuery->having('sum_impressions', '>=', (int) $this->minImpressions);
        }

        if ($this->minClicks !== '') {
            $pageIdsQuery->having('sum_clicks', '>=', (int) $this->minClicks);
        }

        $aggregatedData = collect($pageIdsQuery->orderBy($this->sortField, $this->sortDirection)->get())->keyBy('page_id');

        $q = Page::where('domain_id', $this->domain->id)
            ->whereIn('id', $aggregatedData->keys());

        if ($this->searchPage) {
            $q->where('url', 'like', '%' . $this->searchPage . '%');
        }

        // Maintain the order from the aggregated data
        $ids = $aggregatedData->keys()->toArray();
        if (!empty($ids)) {
            $q->orderByRaw('CASE id ' . implode(' ', array_map(fn($id, $i) => "WHEN {$id} THEN {$i}", $ids, array_keys($ids))) . ' END');
        }

        $pages = $q->paginate(50);

        // Fetch comparison data (Latest Day with data vs the day before)
        $latestDate = DailyPageSummary::where('domain_id', $this->domain->id)
            ->where('total_impressions', '>', 0)
            ->max('stat_date');
            
        $yesterdayDate = $latestDate ? Carbon::parse($latestDate)->subDay()->format('Y-m-d') : null;

        $comparisonData = [];
        if ($latestDate && $yesterdayDate) {
            $comparisonData = DailyPageSummary::where('domain_id', $this->domain->id)
                ->whereDate('stat_date', '>=', $yesterdayDate)
                ->whereDate('stat_date', '<=', $latestDate)
                ->get()
                ->groupBy('page_id');
        }

        return view('livewire.page-table', [
            'pages' => $pages,
            'aggregatedData' => $aggregatedData,
            'comparisonData' => $comparisonData,
            'latestDate' => $latestDate,
        ]);
    }
}
