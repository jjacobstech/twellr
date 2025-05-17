<?php

namespace App\Console\Commands;

use App\Models\AdminSetting;
use Illuminate\Console\Command;

class ActivateVoting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activate-voting';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate voting for the contest';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $setting =  AdminSetting::first();
        $setting->voting = 1;
        $setting->save();
        info('Voting has been activated. The contest is now open for voting.');
        $this->info('Voting has been activated. The contest is now open for voting.');
    }
}
