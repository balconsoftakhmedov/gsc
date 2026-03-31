<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Domain;
use App\Models\DailyQuerySummary;
use App\Models\DailyPageSummary;
use App\Models\Query;
use App\Models\Page;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrendsOverview extends Component
{
    public $domain;
    public $lookbackDays = 7;

    public function mount(Domain $domain)
    {
        $this->domain = $domain;
    }

    public function render()
    {
        $latestDate = DailyQuerySummary::where('domain_id', $this->domain->id)->max('stat_date');
        if (!$latestDate) {
            return view('livewire.trends-overview', ['gainers' => [], 'pageGainers' => []]);
        }

        $currentEnd = Carbon::parse($latestDate)->format('Y-m-d');
        $currentStart = Carbon::parse($latestDate)->subDays($this->lookbackDays - 1)->format('Y-m-d');
        $prevEnd = Carbon::parse($currentStart)->subDay()->format('Y-m-d');
        $prevStart = Carbon::parse($prevEnd)->subDays($this->lookbackDays - 1)->format('Y-m-d');

        // 1. Trending Keywords (Most Impression Growth)
        $currentKeywords = DailyQuerySummary::where('domain_id', $this->domain->id)
            ->whereDate('stat_date', '>=', $currentStart)
            ->whereDate('stat_date', '<=', $currentEnd)
            ->selectRaw('query_id, SUM(total_clicks) as clicks, SUM(total_impressions) as impressions, AVG(avg_position) as position')
            ->groupBy('query_id')
            ->get()->keyBy('query_id');

        $prevKeywords = DailyQuerySummary::where('domain_id', $this->domain->id)
            ->whereDate('stat_date', '>=', $prevStart)
            ->whereDate('stat_date', '<=', $prevEnd)
            ->selectRaw('query_id, SUM(total_clicks) as clicks, SUM(total_impressions) as impressions, AVG(avg_position) as position')
            ->groupBy('query_id')
            ->get()->keyBy('query_id');

        $keywordGains = [];
        foreach ($currentKeywords as $id => $curr) {
            $prev = $prevKeywords->get($id);
            $gain = $curr->impressions - ($prev->impressions ?? 0);
            $clickGain = $curr->clicks - ($prev->clicks ?? 0);
            $posChange = $prev ? ($prev->position - $curr->position) : 0; // Positive means moved UP

            if ($gain > 0 || $clickGain > 0) {
                $keywordGains[] = [
                    'query_id' => $id,
                    'impressions' => $curr->impressions,
                    'imp_gain' => $gain,
                    'clicks' => $curr->clicks,
                    'click_gain' => $clickGain,
                    'position' => $curr->position,
                    'pos_change' => $posChange,
                    'is_new' => !$prev
                ];
            }
        }

        // 2. Trending Pages (Most Click Growth)
        $currentPages = DailyPageSummary::where('domain_id', $this->domain->id)
            ->whereDate('stat_date', '>=', $currentStart)
            ->whereDate('stat_date', '<=', $currentEnd)
            ->selectRaw('page_id, SUM(total_clicks) as clicks, SUM(total_impressions) as impressions')
            ->groupBy('page_id')
            ->get()->keyBy('page_id');

        $prevPages = DailyPageSummary::where('domain_id', $this->domain->id)
            ->whereDate('stat_date', '>=', $prevStart)
            ->whereDate('stat_date', '<=', $prevEnd)
            ->selectRaw('page_id, SUM(total_clicks) as clicks, SUM(total_impressions) as impressions')
            ->groupBy('page_id')
            ->get()->keyBy('page_id');

        $pageGains = [];
        foreach ($currentPages as $id => $curr) {
            $prev = $prevPages->get($id);
            $clickGain = $curr->clicks - ($prev->clicks ?? 0);
            $impGain = $curr->impressions - ($prev->impressions ?? 0);

            if ($clickGain > 0 || $impGain > 0) {
                $pageGains[] = [
                    'page_id' => $id,
                    'clicks' => $curr->clicks,
                    'click_gain' => $clickGain,
                    'impressions' => $curr->impressions,
                    'imp_gain' => $impGain,
                ];
            }
        }

        // Sort and Limit
        usort($keywordGains, fn($a, $b) => $b['imp_gain'] <=> $a['imp_gain']);
        usort($pageGains, fn($a, $b) => $b['click_gain'] <=> $a['click_gain']);

        // Fetch Names
        $keywordIds = collect($keywordGains)->take(10)->pluck('query_id');
        $queries = Query::whereIn('id', $keywordIds)->get()->keyBy('id');

        $pageIds = collect($pageGains)->take(10)->pluck('page_id');
        $pages = Page::whereIn('id', $pageIds)->get()->keyBy('id');

        return view('livewire.trends-overview', [
            'keywordGainers' => collect($keywordGains)->take(10),
            'pageGainers' => collect($pageGains)->take(10),
            'queries' => $queries,
            'pages' => $pages,
        ]);
    }
}
