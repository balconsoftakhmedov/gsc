<?php

namespace App\Services;

use Google\Client;
use Google\Service\SearchConsole;
use Google\Service\SearchConsole\SearchAnalyticsQueryRequest;
use Illuminate\Support\Facades\Log;

class GoogleSearchConsoleService
{
    protected Client $client;
    protected SearchConsole $service;

    public function __construct()
    {
        $this->client = new Client();

        $authConfig = config('services.google.service_account_json');

        if (!file_exists($authConfig)) {
            Log::warning("Google Service Account JSON file not found at: {$authConfig}");
        } else {
            $this->client->setAuthConfig($authConfig);
        }

        $this->client->addScope(SearchConsole::WEBMASTERS_READONLY);

        $this->service = new SearchConsole($this->client);
    }

    public function getDailySearchAnalytics(string $siteUrl, string $date)
    {
        $request = new SearchAnalyticsQueryRequest();
        $request->setStartDate($date);
        $request->setEndDate($date);
        $request->setDimensions(['query', 'page', 'country']);        $request->setRowLimit(25000); // Max allowed by API per request

        $startRow = 0;
        $allRows = [];

        do {
            $request->setStartRow($startRow);

            try {
                $response = $this->service->searchanalytics->query($siteUrl, $request);
                $rows = $response->getRows() ?? [];

                $allRows = array_merge($allRows, $rows);
                $startRow += count($rows);

                // If we got exactly the limit, there might be more
            } catch (\Exception $e) {
                Log::error("GSC API Error for {$siteUrl} on {$date}: " . $e->getMessage());
                throw $e;
            }
        } while (count($rows) === 25000);

        return $allRows;
    }
}
