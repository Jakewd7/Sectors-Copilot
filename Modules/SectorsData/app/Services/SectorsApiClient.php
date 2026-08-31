<?php

namespace Modules\SectorsData\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Exception;

class SectorsApiClient
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.sectors.base_url', env('SECTORS_API_URL', ''));
        $this->apiKey = config('services.sectors.api_key', env('SECTORS_API_KEY', ''));
    }

    protected function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'Authorization' => $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->timeout(15)
            ->retry(2, 500);
    }

    /**
     * GET /v2/company/report/{symbol}/
     */
    public function getCompanyReport(string $symbol, array $sections = ['overview', 'valuation', 'financials'])
    {
        $symbol = strtoupper(trim($symbol));
        $params = [];
        if (!empty($sections)) {
            $params['sections'] = implode(',', $sections);
        }

        $response = $this->client()->get("/company/report/{$symbol}/", $params);

        if ($response->status() === 429) {
            throw new Exception('Sectors API rate limit exceeded (HTTP 429).');
        }
        if ($response->status() === 404) {
            throw new Exception("Stock symbol '{$symbol}' tidak ditemukan di Bursa Efek Indonesia.");
        }
        if ($response->failed()) {
            throw new Exception("Sectors API error [{$response->status()}]: {$response->body()}");
        }

        return $response->json();
    }

    /**
     * GET /v2/financials/quarterly/{symbol}/
     */
    public function getQuarterlyFinancials(string $symbol, int $nQuarters = 4, bool $approx = true)
    {
        $symbol = strtoupper(trim($symbol));
        $params = [
            'n_quarters' => $nQuarters,
            'approx' => $approx ? 'true' : 'false',
        ];

        $response = $this->client()->get("/financials/quarterly/{$symbol}/", $params);

        if ($response->failed()) {
            throw new Exception("Sectors API quarterly error [{$response->status()}]: {$response->body()}");
        }

        return $response->json();
    }

    /**
     * GET /v2/subsector/report/{sub_sector}/
     */
    public function getSubsectorReport(string $subSector, array $sections = ['statistics', 'valuation', 'companies'])
    {
        $subSector = strtolower(trim(str_replace(' ', '-', $subSector)));
        $params = [];
        if (!empty($sections)) {
            $params['sections'] = implode(',', $sections);
        }

        $response = $this->client()->get("/subsector/report/{$subSector}/", $params);

        if ($response->status() === 404) {
            throw new Exception("Subsector '{$subSector}' tidak ditemukan.");
        }
        if ($response->failed()) {
            throw new Exception("Sectors API subsector error [{$response->status()}]: {$response->body()}");
        }

        return $response->json();
    }

    /**
     * GET /v2/companies/ (Screener)
     */
    public function screenCompanies(array $params)
    {
        $response = $this->client()->get('/companies/', $params);

        if ($response->failed()) {
            throw new Exception("Sectors API screener error [{$response->status()}]: {$response->body()}");
        }

        return $response->json();
    }

    /**
     * GET /v2/companies/top-changes/
     * Biaya: 1 kredit per requested classification x period
     */
    public function getTopCompanyMovers(array $params = [])
    {
        $defaultParams = [
            'n_stock' => 5,
            'classifications' => 'top_gainers,top_losers',
            'periods' => '1d,7d,30d',
            'min_mcap_billion' => 5000,
        ];

        $queryParams = array_merge($defaultParams, $params);

        $response = $this->client()->get('/companies/top-changes/', $queryParams);

        if ($response->failed()) {
            throw new Exception("Sectors API top-changes error [{$response->status()}]: {$response->body()}");
        }

        return $response->json();
    }

    /**
     * GET /v2/most-traded/
     * Biaya: 2 kredit
     */
    public function getMostTradedStocks(array $params = [])
    {
        $defaultParams = [
            'n_stock' => 5,
            'adjusted' => 'false',
        ];

        $queryParams = array_merge($defaultParams, $params);

        $response = $this->client()->get('/most-traded/', $queryParams);

        if ($response->failed()) {
            throw new Exception("Sectors API most-traded error [{$response->status()}]: {$response->body()}");
        }

        return $response->json();
    }

    /**
     * GET /v2/idx-total/
     * Biaya: 1 kredit
     */
    public function getIdxMarketSummary(array $params = [])
    {
        $response = $this->client()->get('/idx-total/', $params);

        if ($response->failed()) {
            throw new Exception("Sectors API idx-total error [{$response->status()}]: {$response->body()}");
        }

        return $response->json();
    }
}