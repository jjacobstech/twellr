<?php

use App\Models\AdminSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\CalculateVotesAndDetermineWinner;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


    // Schedule::command( 'winners')->saturdays()->at('00:00');
    // Schedule::command('activate-voting')->mondays()->at('07:00');


    #Tests
Schedule::command('winners')->everyFiveSeconds();
Schedule::command('activate-voting')->everyFiveSeconds();

