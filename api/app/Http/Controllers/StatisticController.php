<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;

class StatisticController extends Controller
{
    public function index()
    { 
        $transactions = Cache::get('statistics');
        return response()->json($transactions);
    }
}