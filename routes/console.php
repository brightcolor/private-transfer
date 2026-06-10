<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('transfers:cleanup')->hourly();
