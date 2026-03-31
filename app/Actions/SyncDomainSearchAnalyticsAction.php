<?php

namespace App\Actions;

use App\Models\Domain;
use App\Models\Query;
use App\Models\Page;
use App\Models\DailySearchAnalytic;
use App\Models\SyncRun;
use App\Services\GoogleSearchConsoleService;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncDomainSearchAnalyticsAction
{
    public function __construct(
        protected GoogleSearchConsoleService $gscService,
        protected RebuildDailyDomainSummaryAction $domainSummaryAction,
        protected RebuildDailyQuerySummaryAction $querySummaryAction,
        protected RebuildDailyPageSummaryAction $pageSummaryAction
    ) {}

    public function execute(Domain $domain, string $date)
    {
        $syncRun = SyncRun::create([
            'domain_id' => $domain->id,
            'target_date' => $date,
            'status' => 'running',
        ]);

        try {
            $siteUrl = $domain->gsc_property ?? $domain->site_url;
            $rows = $this->gscService->getDailySearchAnalytics($siteUrl, $date);

            $rowsFetched = count($rows);
            $syncRun->update(['rows_fetched' => $rowsFetched]);

            if ($rowsFetched === 0) {
                $syncRun->update(['status' => 'completed', 'finished_at' => now()]);
                return;
            }

            DB::beginTransaction();

            $inserted = 0;
            $updated = 0;

            foreach ($rows as $row) {
                $keys = $row->getKeys();
                $queryStr = $keys[0];
                $pageStr = $keys[1];

                $clicks = $row->getClicks();
                $impressions = $row->getImpressions();
                $ctr = $row->getCtr();
                $position = $row->getPosition();

                // 1. Normalize query
                $normalizedQuery = strtolower(trim($queryStr));

                // Branded check helper logic
                $isBranded = false;
                $slug = $domain->slug;
                $brandedTerms = [$slug, "{$slug}.com", str_replace('-', ' ', $slug)];
                foreach ($brandedTerms as $term) {
                    if (str_contains($normalizedQuery, $term)) {
                        $isBranded = true;
                        break;
                    }
                }

                // 2. Normalize URL
                $normalizedUrl = rtrim($pageStr, '/');
                $path = parse_url($normalizedUrl, PHP_URL_PATH) ?? '/';

                // 3 & 5. Upsert query & update dates
                $queryModel = Query::updateOrCreate(
                    ['domain_id' => $domain->id, 'normalized_query' => $normalizedQuery],
                    [
                        'query' => $queryStr,
                        'is_branded' => $isBranded,
                    ]
                );

                if (!$queryModel->first_seen_at || Carbon::parse($date)->lt($queryModel->first_seen_at)) {
                    $queryModel->first_seen_at = $date;
                }
                if (!$queryModel->last_seen_at || Carbon::parse($date)->gt($queryModel->last_seen_at)) {
                    $queryModel->last_seen_at = $date;
                }
                $queryModel->save();

                // 4 & 5. Upsert page & update dates
                $pageModel = Page::updateOrCreate(
                    ['domain_id' => $domain->id, 'normalized_url' => $normalizedUrl],
                    [
                        'url' => $pageStr,
                        'path' => $path,
                    ]
                );

                if (!$pageModel->first_seen_at || Carbon::parse($date)->lt($pageModel->first_seen_at)) {
                    $pageModel->first_seen_at = $date;
                }
                if (!$pageModel->last_seen_at || Carbon::parse($date)->gt($pageModel->last_seen_at)) {
                    $pageModel->last_seen_at = $date;
                }
                $pageModel->save();

                // 6. Upsert daily search analytics
                $analytic = DailySearchAnalytic::updateOrCreate(
                    [
                        'domain_id' => $domain->id,
                        'query_id' => $queryModel->id,
                        'page_id' => $pageModel->id,
                        'stat_date' => $date,
                    ],
                    [
                        'clicks' => $clicks,
                        'impressions' => $impressions,
                        'ctr' => $ctr,
                        'position' => $position,
                    ]
                );

                if ($analytic->wasRecentlyCreated) {
                    $inserted++;
                } else {
                    $updated++;
                }
            }

            DB::commit();

            // 7. Rebuild summaries
            $this->domainSummaryAction->execute($domain, $date);
            $this->querySummaryAction->execute($domain, $date);
            $this->pageSummaryAction->execute($domain, $date);

            $syncRun->update([
                'status' => 'completed',
                'rows_inserted' => $inserted,
                'rows_updated' => $updated,
                'finished_at' => now(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Sync failed for domain {$domain->name} on {$date}: " . $e->getMessage());
            $syncRun->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);
            throw $e;
        }
    }
}
