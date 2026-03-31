<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Domain;
use App\Models\DailySearchAnalytic;
use Carbon\Carbon;

class SnapshotTable extends Component
{
    use WithPagination;

    public $domain;
    public $date;

    public $searchQuery = '';
    public $searchPage = '';
    public $minImpressions = '';
    public $minClicks = '';
    public $minPosition = '';
    public $maxPosition = '';

    protected $queryString = [
        'searchQuery' => ['except' => ''],
        'searchPage' => ['except' => ''],
        'minImpressions' => ['except' => ''],
        'minClicks' => ['except' => ''],
        'minPosition' => ['except' => ''],
        'maxPosition' => ['except' => ''],
    ];

    public function mount(Domain $domain, $date)
    {
        $this->domain = $domain;
        $this->date = $date;
    }

    public function resetFilters()
    {
        $this->reset('searchQuery', 'searchPage', 'minImpressions', 'minClicks', 'minPosition', 'maxPosition');
    }

    public function render()
    {
        $query = DailySearchAnalytic::with(['seoQuery', 'page'])
            ->where('domain_id', $this->domain->id)
            ->where('stat_date', $this->date);

        if ($this->searchQuery) {
            $query->whereHas('seoQuery', function($q) {
                $q->where('query', 'like', '%' . $this->searchQuery . '%');
            });
        }

        if ($this->searchPage) {
            $query->whereHas('page', function($q) {
                $q->where('url', 'like', '%' . $this->searchPage . '%');
            });
        }

        if ($this->minImpressions !== '') {
            $query->where('impressions', '>=', (int) $this->minImpressions);
        }

        if ($this->minClicks !== '') {
            $query->where('clicks', '>=', (int) $this->minClicks);
        }

        if ($this->minPosition !== '') {
            $query->where('position', '>=', (float) $this->minPosition);
        }

        if ($this->maxPosition !== '') {
            $query->where('position', '<=', (float) $this->maxPosition);
        }

        $analytics = $query->orderBy('clicks', 'desc')->orderBy('impressions', 'desc')->paginate(50);

        // Fetch previous day data for comparison deltas
        $previousDate = Carbon::parse($this->date)->subDay()->format('Y-m-d');

        $previousData = DailySearchAnalytic::where('domain_id', $this->domain->id)
            ->where('stat_date', $previousDate)
            ->whereIn('query_id', $analytics->pluck('query_id'))
            ->whereIn('page_id', $analytics->pluck('page_id'))
            ->get()
            ->keyBy(function($item) {
                return $item->query_id . '_' . $item->page_id;
            });

        return view('livewire.snapshot-table', [
            'analytics' => $analytics,
            'previousData' => $previousData,
        ]);
    }
}
