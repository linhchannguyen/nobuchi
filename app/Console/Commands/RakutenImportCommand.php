<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Services\RakutenImport\RakutenImportServiceContract;

class RakutenImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rakuten:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from API in Rakuten website';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public $RakutenImportServiceContract;
    public function __construct(RakutenImportServiceContract $RakutenImportServiceContract)
    {
        parent::__construct();
        $this->RakutenImportServiceContract = $RakutenImportServiceContract;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->RakutenImportServiceContract->import();
    }
}
