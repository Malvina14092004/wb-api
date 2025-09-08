<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WbApiService
{
    protected  $host;
    protected  $key;
    protected  $limit   = 500;

    public function __construct()
    {
        $this->host = rtrim(config('services.wbapi.host'), '/');
        $this->key  = config('services.wbapi.key');

    }

    /**
     * Получение одной страницы данных.
     */
    public function fetchPage(string $endpoint, array $params = []): array
    {
        $params['key'] = $this->key;

        $response = Http::get($this->host . $endpoint, $params);

        if ($response->failed()) {
            throw new \RuntimeException("API error: {$response->status()} - {$response->body()}");
        }

        return $response->json();
    }

    /**
     * Получение всех страниц данных (пагинация).
     */
    public function fetchAll(string $endpoint, array $params = [], int $limitPerPage = 500): array
    {
        $page = 1;
        $all  = [];

        while (true) {
            $params['page']  = $page;
            $params['limit'] = $limitPerPage;

            $json = $this->fetchPage($endpoint, $params);

            // иногда API отдаёт массив, иногда {"data": [], "meta": ...}
            $items = $json['data'] ?? $json ?? [];

            if (empty($items)) {
                break;
            }

            $all = array_merge($all, $items);

            if (count($items) < $limitPerPage) {
                break; // больше страниц нет
            }

            $page++;
        }

        return $all;
    }
    public function getOrders(string $from, string $to, int $page = 1): array
    {
        $url = "{$this->host}/api/orders?dateFrom={$from}&dateTo={$to}&page={$page}&limit={$this->limit}&key={$this->key}";

        $response = Http::get($url);
        return $response->json();
    }

    public function getSales(string $from, string $to, int $page = 1): array
    {
        $url = "{$this->host}/api/sales?dateFrom={$from}&dateTo={$to}&page={$page}&limit={$this->limit}&key={$this->key}";
        $response = Http::get($url);
        return $response->json();
    }

    public function getStocks(string $from, int $page = 1): array
    {
        $url = "{$this->host}/api/stocks?dateFrom={$from}&page={$page}&limit={$this->limit}&key={$this->key}";
        $response = Http::get($url);
        return $response->json();
    }

    public function getIncomes(string $from, string $to, int $page = 1): array
    {
        $url = "{$this->host}/api/incomes?dateFrom={$from}&dateTo={$to}&page={$page}&limit={$this->limit}&key={$this->key}";
        $response = Http::get($url);
        return $response->json();
    }
}

