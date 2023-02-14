<?php

namespace App\Console\Commands;

use App\Http\Controllers\StocksController;
use App\Repositories\AlpacaRepository;
use Illuminate\Console\Command;

class CheckAccountCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '3hourly:checkaccount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check alpaca accounts every 3 hrs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(AlpacaRepository $alpacaRepo)
    {
        (new StocksController)->checkAccounts($alpacaRepo);
        return Command::SUCCESS;
    }
}
