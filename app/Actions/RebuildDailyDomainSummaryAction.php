<?php

namespace App\Actions;

use App\Models\Domain;
use App\Models\DailySearchAnalytic;
use App\Models\DailyDomainSummary;
use Illuminate\Support\Facades\DB;

class RebuildDailyDomainSummaryAction
{
    public function execute(Domain $domain, string $date)
    {
        $stats = DailySearchAnalytic::where('domain_id', $domain->id)
            ->whereDate('stat_date', $date)
            ->selectRaw('
                SUM(clicks) as total_clicks,
                SUM(impressions) as total_impressions,
                COUNT(DISTINCT query_id) as keyword_count,
                COUNT(DISTINCT page_id) as page_count
            ')
            ->first();

        // Calculate average CTR and position safely (weighted by impressions)
        $avgStats = DailySearchAnalytic::where('domain_id', $domain->id)
            ->whereDate('stat_date', $date)
            ->where('impressions', '>', 0)
            ->selectRaw('
                CAST(SUM(clicks) AS FLOAT) / SUM(impressions) as avg_ctr,
                CAST(SUM(position * impressions) AS FLOAT) / SUM(impressions) as avg_position
            ')
            ->first();

        DailyDomainSummary::updateOrCreate(
            ['domain_id' => $domain->id, 'stat_date' => $date],
            [
                'total_clicks' => $stats->total_clicks ?? 0,
                'total_impressions' => $stats->total_impressions ?? 0,
                'keyword_count' => $stats->keyword_count ?? 0,
                'page_count' => $stats->page_count ?? 0,
                'avg_ctr' => $avgStats->avg_ctr ?? 0,
                'avg_position' => $avgStats->avg_position ?? 0,
            ]
        );
    }
}
