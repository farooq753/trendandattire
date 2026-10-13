<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('inventory:release-expired-reservations')
    ->everyFiveMinutes()
    ->withoutOverlapping();

Schedule::command('carts:prune-abandoned')
    ->daily()
    ->withoutOverlapping();
