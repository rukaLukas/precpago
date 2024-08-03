<?php
namespace Tests\Feature;

use Mockery;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Services\RabbitMQService;
use Illuminate\Foundation\Testing\WithFaker;

class TransactionTest extends TestCase
{
    use WithFaker;

    protected $rabbitMQService;
    protected $endpoint;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rabbitMQService = Mockery::mock(RabbitMQService::class);
        app()->instance(RabbitMQService::class, $this->rabbitMQService);
        $this->endpoint = '/api/transactions';
    }

    /** @test */
    public function it_should_create_transaction()
    {
        $transactionData = [
            'amount' => $this->faker->randomFloat(4, 0, 100),
            'timestamp' => Carbon::now()->subSeconds(10)->format('Y-m-d\TH:i:s.v\Z'),
        ];

        $this->rabbitMQService
            ->shouldReceive('publish')
            ->once()
            ->with(json_encode($transactionData));

        $response = $this->postJson($this->endpoint, $transactionData);

        $response->assertStatus(201);
    }

    /** @test */
    public function it_should_return_204_when_when_timestamp_is_more_than_60_seconds_delay()
    {        
        $data = [
            'amount' => $this->faker->randomFloat(4, 0, 100),
            'timestamp' => Carbon::now()->subSeconds(61)->format('Y-m-d\TH:i:s.v\Z'),
        ];

        $response = $this->postJson($this->endpoint, $data);

        $response->assertStatus(204);
    }

    /** @test */
    public function it_should_return_400_when_payload_is_invalid_json()
    {        
        $data = [
            'wrong_field' => 'none',
            'timestamp' => Carbon::now()->subSeconds(20)->format('Y-m-d\TH:i:s.v\Z'),
        ];

        $response = $this->postJson($this->endpoint, $data);

        $response->assertStatus(400);
    }

    /** @test */
    public function it_should_return_422_when_timestamp_is_ahead_from_now()
    { 
        $data = [
            'amount' => $this->faker->randomFloat(4, 0, 100),
            'timestamp' => Carbon::now()->addSeconds(20)->format('Y-m-d\TH:i:s.v\Z'),
        ];

        $response = $this->postJson($this->endpoint, $data);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_should_destroy_transactions()
    {
        $this->rabbitMQService
            ->shouldReceive('deleteAllMessages')
            ->once();

        $response = $this->deleteJson($this->endpoint);

        $response->assertStatus(204);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}