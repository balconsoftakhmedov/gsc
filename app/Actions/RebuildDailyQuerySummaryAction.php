<?php

namespace App\Actions;

use App\Models\Domain;
use App\Models\DailySearchAnalytic;
use App\Models\DailyQuerySummary;
use Illuminate\Support\Facades\DB;

class RebuildDailyQuerySummaryAction
{
    public function execute(Domain $domain, string $date)
    {
        $stats = DailySearchAnalytic::where('domain_id', $domain->id)
            ->where('stat_date', $date)
            ->selectRaw('
                query_id,
                SUM(clicks) as total_clicks,
                SUM(impressions) as total_impressions,
                COUNT(DISTINCT page_id) as page_count
            ')
            ->groupBy('query_id')
            ->get();

        $avgStats = DailySearchAnalytic::where('domain_id', $domain->id)
            ->where('stat_date', $date)
            ->where('impressions', '>', 0)
            ->selectRaw('
                query_id,
                SUM(clicks) / SUM(impressions) as avg_ctr,
                SUM(position * impressions) / SUM(impressions) as avg_position
            ')
            ->groupBy('query_id')
            ->get()->keyBy('query_id');

        foreach ($stats as $stat) {
            $avgStat = $avgStats->get($stat->query_id);
            DailyQuerySummary::updateOrCreate(
                ['domain_id' => $domain->id, 'query_id' => $stat->query_id, 'stat_date' => $date],
                [
                    'total_clicks' => $stat->total_clicks ?? 0,
                    'total_impressions' => $stat->total_impressions ?? 0,
                    'page_count' => $stat->page_count ?? 0,
                    'avg_ctr' => $avgStat->avg_ctr ?? 0,
                    'avg_position' => $avgStat->avg_position ?? 0,
                ]
            );
        }
    }
}
