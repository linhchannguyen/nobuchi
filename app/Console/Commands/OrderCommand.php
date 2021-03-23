<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Repositories\Services\ECImport\ECImportServiceContract;
use Illuminate\Support\Facades\Log;

class OrderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import order table EC-CUBE';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    private $ec_service;
    public function __construct(ECImportServiceContract $ec_service)
    {
        parent::__construct();
        $this->ec_service = $ec_service;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->ec_service->importOrder();
    }
}
