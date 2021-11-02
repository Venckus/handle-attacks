<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

// use App\Services\AttackHandler;
use App\Services\LogFileReader;

class CheckAttacks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:attacks {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $path = $this->argument('path');

        $LogFileReader = new LogFileReader($path);
        $result = $LogFileReader->execute();
        
        if (!$result) Log::error('Job Attack handle went wrong');
        else Log::error($result);
    }
}
