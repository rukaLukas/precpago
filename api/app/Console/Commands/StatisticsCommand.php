<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RabbitMQService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class StatisticsCommand extends Command
{
    protected $signature = 'statistics:consume';
    protected $description = 'Consume messages from RabbitMQ and store statistics in cache';

    protected $rabbitMQService;
    protected $statistics = [];

    public function __construct(RabbitMQService $rabbitMQService)
    {
        parent::__construct();
        $this->rabbitMQService = $rabbitMQService;
    }

    public function handle()
    {        
        $this->rabbitMQService->consume(function($msg) {            
            $lock = Cache::lock('statistics_lock', 10);
            try {
                if ($lock->get()) {
                    $this->store($msg->body);
                    Cache::put('statistics', $this->statistics, 60);
                    $this->info(date('Y-m-d H:i:s') . ' cache of polling flag->>' . Cache::get('statistics_polling_flag'));                    
                } else {
                    Log::error('Falha Lock Cache statistics_lock. Possível ter ocorrido race condition');
                }
            } finally {
                $lock->release();
            }

        });
    }

    private function store(string $transaction): void
    {
        $transaction = json_decode($transaction, true);
        $statistics = Cache::get('statistics');       

        $sum = is_null($statistics) ? 0 : $statistics[0]['sum']; //0;
        $count = is_null($statistics) ? 1 : ++$statistics[0]['count']; //count($transactions);
        $max = is_null($statistics) ? 0 : $statistics[0]['max']; //0;
        $min = is_null($statistics) ? PHP_FLOAT_MAX : $statistics[0]['min']; //PHP_FLOAT_MAX
        $amount = (float) $transaction['amount'];
        $sum += $amount;
        $max = max($max, $amount);
        $min = min($min, $amount);        

        $avg = $sum / $count;

        $formattedValues = [
            'sum' => number_format($sum, 2, '.', ''),
            'avg' => number_format($avg, 2, '.', ''),
            'max' => number_format($max, 2, '.', ''),
            'min' => number_format($min, 2, '.', ''),
            'count' => $count
        ];
        $this->statistics[0] = $formattedValues;      
    }

}
