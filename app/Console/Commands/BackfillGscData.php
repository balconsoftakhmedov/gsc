<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Actions\SyncDomainSearchAnalyticsAction;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BackfillGscData extends Command
{
    protected $signature = 'seo:backfill {--days=90}';
    protected $description = 'Backfills GSC data for the specified number of days.';

    public function handle(SyncDomainSearchAnalyticsAction $syncAction)
    {
        $days = (int) $this->option('days');
        $domains = Domain::where('is_active', true)->get();

        $this->info("Starting GSC backfill for {$domains->count()} domains over the last {$days} days.");

        foreach ($domains as $domain) {
            $this->info("Backfilling domain: {$domain->name}");

            // Loop backwards so older data is processed first
            for ($i = $days; $i >= 1; $i--) {
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

        $this->info('GSC backfill completed.');
    }
}
