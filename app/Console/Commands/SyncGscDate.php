<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Actions\SyncDomainSearchAnalyticsAction;
use Illuminate\Console\Command;

class SyncGscDate extends Command
{
    protected $signature = 'seo:sync-gsc-date {date}';
    protected $description = 'Sync GSC data for all active domains for a specific date (YYYY-MM-DD)';

    public function handle(SyncDomainSearchAnalyticsAction $syncAction)
    {
        $date = $this->argument('date');
        $domains = Domain::where('is_active', true)->get();

        $this->info("Starting GSC sync for {$domains->count()} domains on {$date}.");

        foreach ($domains as $domain) {
            $this->info("Syncing domain: {$domain->name} for date: {$date}");

            try {
                $syncAction->execute($domain, $date);
                $this->info("  -> Completed: {$date}");
            } catch (\Exception $e) {
                $this->error("  -> Failed on {$date}: " . $e->getMessage());
            }
        }

        $this->info('GSC date sync completed.');
    }
}
