<?php

namespace Tests\Unit;

use Tests\TestCase;
use ReflectionClass;
use Illuminate\Support\Carbon;
use App\Services\RabbitMQService;
use Illuminate\Support\Facades\Cache;
use App\Console\Commands\StatisticsCommand;

class StatisticsCommandTest extends TestCase
{
    protected $rabbitMQService;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->rabbitMQService = $this->createMock(RabbitMQService::class);
    }

    protected function invokeStoreMethod(StatisticsCommand $command, array $data)
    {
        $reflection = new ReflectionClass($command);
        $method = $reflection->getMethod('store');
        $method->setAccessible(true);
        $method->invokeArgs($command, [json_encode($data)]);
    }

    protected function getStatisticsProperty(StatisticsCommand $command)
    {
        $reflection = new ReflectionClass($command);
        $statisticsProperty = $reflection->getProperty('statistics');
        $statisticsProperty->setAccessible(true);
        return $statisticsProperty->getValue($command);
    }

    public function testStoreWithEmptyCache()
    {
        Cache::shouldReceive('get')
            ->with('statistics')
            ->andReturn(null);

        $command = new StatisticsCommand($this->rabbitMQService);

        $this->invokeStoreMethod($command, [
            'amount' => 100.00,
            'timestamp' => Carbon::now()->subSeconds(10)->format('Y-m-d\TH:i:s.v\Z')
        ]);

        $statistics = $this->getStatisticsProperty($command);

        $this->assertEquals([
            'sum' => '100.00',
            'avg' => '100.00',
            'max' => '100.00',
            'min' => '100.00',
            'count' => 1
        ], $statistics[0]);
    }

    public function testStoreWithExistingStatistics()
    {
        Cache::shouldReceive('get')
            ->with('statistics')
            ->andReturn([
                [
                    'sum' => '100.00',
                    'avg' => '100.00',
                    'max' => '100.00',
                    'min' => '100.00',
                    'count' => 1
                ]
            ]);

        $command = new StatisticsCommand($this->rabbitMQService);

        $this->invokeStoreMethod($command, [
            'amount' => 200.00,
            'timestamp' => Carbon::now()->subSeconds(10)->format('Y-m-d\TH:i:s.v\Z')
        ]);

        $statistics = $this->getStatisticsProperty($command);

        $this->assertEquals([
            'sum' => '300.00',
            'avg' => '150.00',
            'max' => '200.00',
            'min' => '100.00',
            'count' => 2
        ], $statistics[0]);
    }
}