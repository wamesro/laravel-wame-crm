<?php

namespace Wame\LaravelAuth\Console;

use Illuminate\Console\Command;

class InstallLaravelAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wame:auth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Laravel Auth by WAME';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return Command::SUCCESS;
    }
}
