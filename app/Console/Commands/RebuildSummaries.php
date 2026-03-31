<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Models\DailySearchAnalytic;
use App\Actions\RebuildDailyDomainSummaryAction;
use App\Actions\RebuildDailyQuerySummaryAction;
use App\Actions\RebuildDailyPageSummaryAction;
use Illuminate\Console\Command;

class RebuildSummaries extends Command
{
    protected $signature = 'seo:rebuild-summaries';
    protected $description = 'Rebuilds all daily summaries based on the raw analytic data.';

    public function handle(
        RebuildDailyDomainSummaryAction $domainSummary,
        RebuildDailyQuerySummaryAction $querySummary,
        RebuildDailyPageSummaryAction $pageSummary
    ) {
        $dates = DailySearchAnalytic::select('stat_date')->distinct()->pluck('stat_date');
        $domains = Domain::where('is_active', true)->get();

        $this->info("Rebuilding summaries for {$domains->count()} domains across {$dates->count()} unique dates.");

        foreach ($domains as $domain) {
            $this->info("Processing domain: {$domain->name}");

            foreach ($dates as $date) {
                $dateStr = $date->format('Y-m-d');
                $this->line("  -> Rebuilding summaries for: {$dateStr}");

                try {
                    $domainSummary->execute($domain, $dateStr);
                    $querySummary->execute($domain, $dateStr);
                    $pageSummary->execute($domain, $dateStr);
                } catch (\Exception $e) {
                    $this->error("  -> Failed rebuilding on {$dateStr}: " . $e->getMessage());
                }
            }
        }

        $this->info('Summary rebuild completed.');
    }
}
