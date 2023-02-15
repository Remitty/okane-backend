<?php

namespace App\Console\Commands;

use App\Http\Controllers\StocksController;
use Illuminate\Console\Command;

class CheckOpenOrderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hourly:checkOpenOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check open orders hourly';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        (new StocksController)->checkOpenOrders();
        return Command::SUCCESS;
    }
}
