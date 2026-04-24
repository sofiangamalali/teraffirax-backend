<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('calculate:daily-clicks')->dailyAt('00:00');
