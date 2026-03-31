<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Domain;
use App\Models\DailyQuerySummary;
use App\Models\DailyPageSummary;
use App\Models\Query;
use App\Models\Page;
use Carbon\Carbon;

class TrendsOverview extends Component
{
    public $domain;
    public $lookbackDays = 7;
    public $activeTab = 'keywords'; // 'keywords' or 'pages'
    
    // Pagination states
    public $kwPage = 1;
    public $klPage = 1;
    public $pwPage = 1;
    public $plPage = 1;
    public $perPage = 10;

    public function mount(Domain $domain)
    {
        $this->domain = $domain;
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function nextPage($prop)
    {
        $this->$prop++;
    }

    public function prevPage($prop)
    {
        if ($this->$prop > 1) $this->$prop--;
    }

    public function updatedLookbackDays()
    {
        $this->resetPage();
    }

    public function resetPage()
    {
        $this->kwPage = 1;
        $this->klPage = 1;
        $this->pwPage = 1;
        $this->plPage = 1;
    }

    public function render()
    {
        $latestDate = DailyQuerySummary::where('domain_id', $this->domain->id)->max('stat_date');
        if (!$latestDate) {
            return view('livewire.trends-overview', ['keywordWinners' => collect(), 'keywordLosers' => collect()]);
        }

        $currentEnd = Carbon::parse($latestDate)->format('Y-m-d');
        $currentStart = Carbon::parse($latestDate)->subDays($this->lookbackDays - 1)->format('Y-m-d');
        $prevEnd = Carbon::parse($currentStart)->subDay()->format('Y-m-d');
        $prevStart = Carbon::parse($prevEnd)->subDays($this->lookbackDays - 1)->format('Y-m-d');

        // 1. Keywords
        $currentKeywords = DailyQuerySummary::where('domain_id', $this->domain->id)
            ->whereDate('stat_date', '>=', $currentStart)
            ->whereDate('stat_date', '<=', $currentEnd)
            ->selectRaw('query_id, SUM(total_clicks) as clicks, SUM(total_impressions) as impressions, AVG(avg_position) as position')
            ->groupBy('query_id')->get()->keyBy('query_id');

        $prevKeywords = DailyQuerySummary::where('domain_id', $this->domain->id)
            ->whereDate('stat_date', '>=', $prevStart)
            ->whereDate('stat_date', '<=', $prevEnd)
            ->selectRaw('query_id, SUM(total_clicks) as clicks, SUM(total_impressions) as impressions, AVG(avg_position) as position')
            ->groupBy('query_id')->get()->keyBy('query_id');

        $keywordGains = [];
        foreach ($currentKeywords as $id => $curr) {
            $prev = $prevKeywords->get($id);
            $impGain = $curr->impressions - ($prev->impressions ?? 0);
            if ($impGain == 0) continue;

            $keywordGains[] = [
                'query_id' => $id,
                'imp_gain' => $impGain,
                'position' => $curr->position,
                'pos_change' => $prev ? ($prev->position - $curr->position) : 0,
                'is_new' => !$prev
            ];
        }

        // 2. Pages
        $currentPages = DailyPageSummary::where('domain_id', $this->domain->id)
            ->whereDate('stat_date', '>=', $currentStart)
            ->whereDate('stat_date', '<=', $currentEnd)
            ->selectRaw('page_id, SUM(total_clicks) as clicks, SUM(total_impressions) as impressions, AVG(avg_position) as position')
            ->groupBy('page_id')->get()->keyBy('page_id');

        $prevPages = DailyPageSummary::where('domain_id', $this->domain->id)
            ->whereDate('stat_date', '>=', $prevStart)
            ->whereDate('stat_date', '<=', $prevEnd)
            ->selectRaw('page_id, SUM(total_clicks) as clicks, SUM(total_impressions) as impressions, AVG(avg_position) as position')
            ->groupBy('page_id')->get()->keyBy('page_id');

        $pageGains = [];
        foreach ($currentPages as $id => $curr) {
            $prev = $prevPages->get($id);
            $impGain = $curr->impressions - ($prev->impressions ?? 0);
            $clickGain = $curr->clicks - ($prev->clicks ?? 0);
            
            // Show if there is ANY change in impressions or clicks
            if ($impGain == 0 && $clickGain == 0) continue;

            $pageGains[] = [
                'page_id' => $id,
                'imp_gain' => $impGain,
                'click_gain' => $clickGain,
                'position' => $curr->position,
                'pos_change' => $prev ? ($prev->position - $curr->position) : 0,
            ];
        }

        $keywordWinnersAll = collect($keywordGains)->sortByDesc('imp_gain')->values();
        $keywordLosersAll = collect($keywordGains)->sortBy('imp_gain')->filter(fn($i) => $i['imp_gain'] < 0)->values();
        
        $pageWinnersAll = collect($pageGains)->sortByDesc('imp_gain')->values();
        $pageLosersAll = collect($pageGains)->sortBy('imp_gain')->filter(fn($i) => $i['imp_gain'] < 0)->values();

        // Paginate manually
        $keywordWinners = $keywordWinnersAll->slice(($this->kwPage - 1) * $this->perPage, $this->perPage);
        $keywordLosers = $keywordLosersAll->slice(($this->klPage - 1) * $this->perPage, $this->perPage);
        $pageWinners = $pageWinnersAll->slice(($this->pwPage - 1) * $this->perPage, $this->perPage);
        $pageLosers = $pageLosersAll->slice(($this->plPage - 1) * $this->perPage, $this->perPage);

        $queries = Query::whereIn('id', $keywordWinners->pluck('query_id')->merge($keywordLosers->pluck('query_id')))->get()->keyBy('id');
        $pages = Page::whereIn('id', $pageWinners->pluck('page_id')->merge($pageLosers->pluck('page_id')))->get()->keyBy('id');

        return view('livewire.trends-overview', [
            'keywordWinners' => $keywordWinners,
            'keywordLosers' => $keywordLosers,
            'pageWinners' => $pageWinners,
            'pageLosers' => $pageLosers,
            'queries' => $queries,
            'pages' => $pages,
            'hasMoreKw' => $keywordWinnersAll->count() > ($this->kwPage * $this->perPage),
            'hasMoreKl' => $keywordLosersAll->count() > ($this->klPage * $this->perPage),
            'hasMorePw' => $pageWinnersAll->count() > ($this->pwPage * $this->perPage),
            'hasMorePl' => $pageLosersAll->count() > ($this->plPage * $this->perPage),
        ]);
    }
}
