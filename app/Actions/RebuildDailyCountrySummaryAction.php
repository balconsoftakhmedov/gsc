<?php

namespace App\Actions;

use App\Models\Domain;
use App\Models\DailySearchAnalytic;
use App\Models\DailyCountrySummary;
use Illuminate\Support\Facades\DB;

class RebuildDailyCountrySummaryAction
{
    public function execute(Domain $domain, string $date)
    {
        $stats = DailySearchAnalytic::where('domain_id', $domain->id)
            ->whereDate('stat_date', $date)
            ->whereNotNull('country')
            ->selectRaw('
                country,
                SUM(clicks) as total_clicks,
                SUM(impressions) as total_impressions
            ')
            ->groupBy('country')
            ->get();

        $avgStats = DailySearchAnalytic::where('domain_id', $domain->id)
            ->whereDate('stat_date', $date)
            ->whereNotNull('country')
            ->where('impressions', '>', 0)
            ->selectRaw('
                country,
                CAST(SUM(clicks) AS FLOAT) / SUM(impressions) as avg_ctr,
                CAST(SUM(position * impressions) AS FLOAT) / SUM(impressions) as avg_position
            ')
            ->groupBy('country')
            ->get()->keyBy('country');

        foreach ($stats as $stat) {
            $avgStat = $avgStats->get($stat->country);
            DailyCountrySummary::updateOrCreate(
                ['domain_id' => $domain->id, 'country' => $stat->country, 'stat_date' => $date],
                [
                    'total_clicks' => $stat->total_clicks ?? 0,
                    'total_impressions' => $stat->total_impressions ?? 0,
                    'avg_ctr' => $avgStat->avg_ctr ?? 0,
                    'avg_position' => $avgStat->avg_position ?? 0,
                ]
            );
        }
    }
}
