<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQService
{
    protected $connection;
    protected $channel;
    
    public function __construct()
    {
        try {           
            $this->connection = new AMQPStreamConnection(
                env('RABBITMQ_HOST', '127.0.0.1'),
                env('RABBITMQ_PORT', 5672),
                env('RABBITMQ_USER', 'guest'),
                env('RABBITMQ_PASSWORD', 'guest')
            );            
            $this->channel = $this->connection->channel();
            $this->channel->queue_declare('statistics', false, true, false, false);         
        } catch(\Exception $e) {
            dd($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function publish($message)
    {
        $msg = new AMQPMessage($message);
        $this->channel->basic_publish($msg, '', 'statistics');        
    }

    public function consume($callback)
    {
        $this->channel->basic_consume('statistics', '', false, false, false, false, $callback);
        
        while(count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    public function getMessages()
    {
        $messages = [];
        while ($msg = $this->channel->basic_get('statistics', false)) {
            $messages[] = json_decode($msg->getBody(), true);
        }

        return $messages;
    }

    public function deleteAllMessages()
    {
        $this->channel->queue_purge('statistics');
    }

    public function close()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
