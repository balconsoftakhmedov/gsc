<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Domain;
use App\Models\DailyQuerySummary;
use App\Models\Query;
use Carbon\Carbon;

class OpportunitiesTable extends Component
{
    use WithPagination;

    public $domain;
    public $lookbackDays = 30;

    public function mount(Domain $domain)
    {
        $this->domain = $domain;
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
            ->groupBy('query_id')
            ->having('sum_impressions', '>', 10)
            ->having('mean_position', '>', 3)
            ->orderBy('sum_impressions', 'desc');

        $aggregatedData = collect($queryIdsQuery->get())->keyBy('query_id');

        $keywords = Query::where('domain_id', $this->domain->id)
            ->whereIn('id', $aggregatedData->keys())
            ->paginate(50);

        return view('livewire.opportunities-table', [
            'keywords' => $keywords,
            'aggregatedData' => $aggregatedData,
        ]);
    }
}
