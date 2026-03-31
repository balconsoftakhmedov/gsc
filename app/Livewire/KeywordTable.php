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

    protected $queryString = [
        'searchQuery' => ['except' => ''],
        'minImpressions' => ['except' => ''],
        'minClicks' => ['except' => ''],
    ];

    public function mount(Domain $domain)
    {
        $this->domain = $domain;
    }

    public function resetFilters()
    {
        $this->reset('searchQuery', 'minImpressions', 'minClicks');
    }

    public function render()
    {
        $startDate = Carbon::now()->subDays($this->lookbackDays)->format('Y-m-d');

        $queryIdsQuery = DailyQuerySummary::where('domain_id', $this->domain->id)
            ->where('stat_date', '>=', $startDate)
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

        $aggregatedData = collect($queryIdsQuery->get())->keyBy('query_id');

        $q = Query::where('domain_id', $this->domain->id)
            ->whereIn('id', $aggregatedData->keys());

        if ($this->searchQuery) {
            $q->where('query', 'like', '%' . $this->searchQuery . '%');
        }

        $keywords = $q->paginate(50);

        return view('livewire.keyword-table', [
            'keywords' => $keywords,
            'aggregatedData' => $aggregatedData,
        ]);
    }
}
