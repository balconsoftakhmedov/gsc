<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Actions\SyncDomainSearchAnalyticsAction;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncGscData extends Command
{
    protected $signature = 'seo:sync-gsc';
    protected $description = 'Sync GSC data for all active domains for the configured lookback period.';

    public function handle(SyncDomainSearchAnalyticsAction $syncAction)
    {
        $lookbackDays = config('app.seo_sync_lookback_days', env('SEO_SYNC_LOOKBACK_DAYS', 3));
        $domains = Domain::where('is_active', true)->get();

        $this->info("Starting GSC sync for {$domains->count()} domains over the last {$lookbackDays} days.");

        foreach ($domains as $domain) {
            $this->info("Syncing domain: {$domain->name}");

            for ($i = $lookbackDays; $i >= 1; $i--) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');
                $this->line("  -> Fetching data for date: {$date}");

                try {
                    $syncAction->execute($domain, $date);
                    $this->info("  -> Completed: {$date}");
                } catch (\Exception $e) {
                    $this->error("  -> Failed on {$date}: " . $e->getMessage());
                }
            }
        }

        $this->info('GSC sync completed.');
    }
}
