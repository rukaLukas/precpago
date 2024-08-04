<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;

class StatisticController extends Controller
{
    protected $pollingTimeout = 60;
    public function index()
    { 
        $startTime = time();

        while (true) {
            if (Cache::pull('statistics_polling_flag')) {
                $statistics = Cache::get('statistics');
                return response()->json($statistics);
            }

            if (time() - $startTime > $this->pollingTimeout) {          
                return response('', 204);
            }
            usleep(500000);
        }
    }
}