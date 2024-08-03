<?php

namespace App\Services;

class StatisticsService
{
    protected $rabbitMQService;
    public function __construct(RabbitMQService $rabbitMQService)
    {
        $this->rabbitMQService = $rabbitMQService;
    }

    public function calc(): array
    {
        $transactions = $this->rabbitMQService->getMessages();
               
        $formattedValues = [];
        if (count($transactions) > 0) {
            $formattedValues = $this->formatValues($transactions);
        }        

        return $formattedValues;
    }

    private function formatValues(array $transactions): array
    {
        $sum = 0;
        $count = count($transactions);
        $max = 0;
        $min = PHP_FLOAT_MAX;

        foreach ($transactions as $transaction) {
            $amount = (float) $transaction['amount'];
            $sum += $amount;
            $max = max($max, $amount);
            $min = min($min, $amount);
        }

        $avg = $sum / $count;

        $formattedValues = [
            'sum' => number_format($sum, 2, '.', ''),
            'avg' => number_format($avg, 2, '.', ''),
            'max' => number_format($max, 2, '.', ''),
            'min' => number_format($min, 2, '.', ''),
            'count' => $count
        ];

        return $formattedValues;
    }
}
