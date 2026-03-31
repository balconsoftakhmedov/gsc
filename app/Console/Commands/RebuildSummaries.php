<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Models\DailySearchAnalytic;
use App\Actions\RebuildDailyDomainSummaryAction;
use App\Actions\RebuildDailyQuerySummaryAction;
use App\Actions\RebuildDailyPageSummaryAction;
use App\Actions\RebuildDailyCountrySummaryAction;
use Illuminate\Console\Command;

class RebuildSummaries extends Command
{
    protected $signature = 'seo:rebuild-summaries {--domain= : Domain ID to rebuild}';
    protected $description = 'Rebuild all summary tables from existing analytics data';

    public function handle(
        RebuildDailyDomainSummaryAction $domainSummary,
        RebuildDailyQuerySummaryAction $querySummary,
        RebuildDailyPageSummaryAction $pageSummary,
        RebuildDailyCountrySummaryAction $countrySummary
    ) {
        $domains = $this->option('domain') 
            ? Domain::where('id', $this->option('domain'))->get()
            : Domain::all();

        foreach ($domains as $domain) {
            $this->info("Rebuilding summaries for domain: {$domain->name}");
            
            $dates = DailySearchAnalytic::where('domain_id', $domain->id)
                ->distinct()
                ->pluck('stat_date')
                ->map(fn($d) => $d instanceof \Carbon\Carbon ? $d->format('Y-m-d') : substr($d, 0, 10));

            $bar = $this->output->createProgressBar(count($dates));
            $bar->start();

            foreach ($dates as $date) {
                $domainSummary->execute($domain, $date);
                $querySummary->execute($domain, $date);
                $pageSummary->execute($domain, $date);
                $countrySummary->execute($domain, $date);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        $this->info('Summary rebuild completed.');
    }
}
