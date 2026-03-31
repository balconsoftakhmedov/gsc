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

    protected $queryString = [
        'searchPage' => ['except' => ''],
        'minImpressions' => ['except' => ''],
        'minClicks' => ['except' => ''],
    ];

    public function mount(Domain $domain)
    {
        $this->domain = $domain;
    }

    public function resetFilters()
    {
        $this->reset('searchPage', 'minImpressions', 'minClicks');
    }

    public function render()
    {
        $startDate = Carbon::now()->subDays($this->lookbackDays)->format('Y-m-d');

        $pageIdsQuery = DailyPageSummary::where('domain_id', $this->domain->id)
            ->where('stat_date', '>=', $startDate)
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

        $aggregatedData = collect($pageIdsQuery->get())->keyBy('page_id');

        $q = Page::where('domain_id', $this->domain->id)
            ->whereIn('id', $aggregatedData->keys());

        if ($this->searchPage) {
            $q->where('url', 'like', '%' . $this->searchPage . '%');
        }

        $pages = $q->paginate(50);

        return view('livewire.page-table', [
            'pages' => $pages,
            'aggregatedData' => $aggregatedData,
        ]);
    }
}
