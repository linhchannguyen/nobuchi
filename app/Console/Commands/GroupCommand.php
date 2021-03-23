<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Services\ECImport\ECImportServiceContract;

class GroupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'group:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'import group table from dtb_group Ec-cube';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public $ec_service;
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
        $this->ec_service->groups();
    }
}
