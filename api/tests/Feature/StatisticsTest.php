<?php
namespace Tests\Feature;

use Mockery;
use Tests\TestCase;
use App\Services\RabbitMQService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\WithFaker;

class StatisticsTest extends TestCase
{
    use WithFaker;

    protected $rabbitMQService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rabbitMQService = Mockery::mock(RabbitMQService::class);
        app()->instance(RabbitMQService::class, $this->rabbitMQService);
    }

    /** @test */
    public function it_should_return_statistics()
    {
        $sum = $this->faker->randomFloat(2, 0, 100);
        $avg = $this->faker->randomFloat(2, 0, 100);
        $max = $this->faker->randomFloat(2, 0, 1000);
        $min = $this->faker->randomFloat(2, 0, 1000);
        $count = $this->faker->numberBetween(1, 100);
        Cache::shouldReceive('get')
        ->with('statistics')
        ->andReturn([
            'sum' => $sum,
            'avg' => $avg,
            'max' => $max,
            'min' => $min,
            'count' => $count           
        ]);

        $response = $this->json('GET', '/api/statistics');

        // Assert that the response has a successful status code
        $response->assertStatus(200);

        // Assert that the response contains the expected data
        $response->assertJson([
            'sum' => $sum,
            'avg' => $avg,
            'max' => $max,
            'min' => $min,
            'count' => $count
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}