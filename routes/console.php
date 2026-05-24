<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('calculate:daily-clicks')->dailyAt('00:00');

Schedule::command('commissions:calculate')
    ->everyThreeHours()
    ->withoutOverlapping()
    ->onSuccess(function () {
        Artisan::call('commissions:mature');
        Artisan::call('balances:reconcile');
    });
