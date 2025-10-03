<?php

namespace App\Console\Commands;

use Facade\Ignition\DumpRecorder\Dump;
use Illuminate\Console\Command;
use App\Services\WbApiService;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\Income;

class ImportApiData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:wbapi {--from=} {--to=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Импорт данных из WB API (orders, sales, stocks, incomes)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(WbApiService $api)
    {
        parent::__construct();
        $this->api = $api;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $from = $this->option('from');
        $to = $this->option('to');
        /**
         * 📦 Импорт заказов
         */
        $this->info("Импорт заказов...");
        $page = 1;
        do {
            $orders = $this->api->getOrders($from, $to, $page);
            foreach ($orders['data'] ?? [] as $order) {
                Order::updateOrCreate(
                    ['external_id' => $order['income_id'] ?: $order['g_number']], // уникальный ключ
                    ['payload' => json_encode($order)] // сохраняем весь объект в JSON
                );
            }
            $page++;

        } while (!empty($orders['data']));
        $this->info("Заказы импортированы");

        /**
         * 💰 Импорт продаж
         */
        $this->info("Импорт продаж...");
        $page = 1;
        do {
            $sales = $this->api->getSales($from, $to, $page);
            foreach ($sales['data'] ?? [] as $sale) {
                Sale::updateOrCreate(
                    ['external_id' => $sale['g_number']], // используем g_number как уникальный ключ
                    ['payload' => json_encode($sale)]
                );
            }
            $page++;
        } while (!empty($sales['data']));
        $this->info("Продажи импортированы");

        /**
         * 🏬 Импорт складов
         * (только по dateFrom, без dateTo)
         */
        $this->info("Импорт складов...");
        $stocks = $this->api->getStocks(now()->toDateString());
        foreach ($stocks['data'] ?? [] as $stock) {
            Stock::updateOrCreate(
                ['external_id' => $stock['supplier_article']], // используем g_number как уникальный ключ
                ['payload' => json_encode($stock)]
            );
        }
        $this->info("Склады импортированы");

        /**
         * 📊 Импорт доходов
         */
        $this->info("Импорт доходов...");
        $page = 1;
        do {
            $incomes = $this->api->getIncomes($from, $to, $page);
            dump($incomes);
            foreach ($incomes['data'] ?? [] as $income) {
                Income::updateOrCreate(
                    ['external_id' => $income['income_id']], // используем g_number как уникальный ключ
                    ['payload' => json_encode($income)]
                );
            }
            $page++;
        } while (!empty($incomes['data']));
        $this->info("Доходы импортированы");

        return Command::SUCCESS;
    }

}
