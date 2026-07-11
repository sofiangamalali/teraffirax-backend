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

Schedule::command('app:send-telegram-products')
    ->everyTenMinutes()
    ->withoutOverlapping();

Schedule::command('horizon:snapshot')->everyTenMinutes();

Schedule::command('notifications:daily-digest')
    ->dailyAt('21:00')
    ->timezone('Africa/Cairo');

Schedule::command('notifications:weekly-fallback')
    ->dailyAt('21:00')
    ->timezone('Africa/Cairo');
