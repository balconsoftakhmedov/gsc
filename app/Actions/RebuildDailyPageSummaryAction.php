<?php

namespace App\Actions;

use App\Models\Domain;
use App\Models\DailySearchAnalytic;
use App\Models\DailyPageSummary;
use Illuminate\Support\Facades\DB;

class RebuildDailyPageSummaryAction
{
    public function execute(Domain $domain, string $date)
    {
        $stats = DailySearchAnalytic::where('domain_id', $domain->id)
            ->whereDate('stat_date', $date)
            ->selectRaw('
                page_id,
                SUM(clicks) as total_clicks,
                SUM(impressions) as total_impressions,
                COUNT(DISTINCT query_id) as query_count
            ')
            ->groupBy('page_id')
            ->get();

        $avgStats = DailySearchAnalytic::where('domain_id', $domain->id)
            ->whereDate('stat_date', $date)
            ->where('impressions', '>', 0)
            ->selectRaw('
                page_id,
                CAST(SUM(clicks) AS FLOAT) / SUM(impressions) as avg_ctr,
                CAST(SUM(position * impressions) AS FLOAT) / SUM(impressions) as avg_position
            ')
            ->groupBy('page_id')
            ->get()->keyBy('page_id');

        foreach ($stats as $stat) {
            $avgStat = $avgStats->get($stat->page_id);
            DailyPageSummary::updateOrCreate(
                ['domain_id' => $domain->id, 'page_id' => $stat->page_id, 'stat_date' => $date],
                [
                    'total_clicks' => $stat->total_clicks ?? 0,
                    'total_impressions' => $stat->total_impressions ?? 0,
                    'query_count' => $stat->query_count ?? 0,
                    'avg_ctr' => $avgStat->avg_ctr ?? 0,
                    'avg_position' => $avgStat->avg_position ?? 0,
                ]
            );
        }
    }
}
