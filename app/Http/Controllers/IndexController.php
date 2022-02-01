<?php

namespace App\Http\Controllers;

use App\Empower\RestClient\RestClient;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use App\Empower\Logger\Logger;
class IndexController extends Controller
{
    
    public function index(){
        $host = 'rabbitmq';
        $port = 5672;
        $user = 'guest';
        $pass = 'guest';
        $vhost = '/';
        $exchange = 'log';
        $queue = 'queue_logger';
    
        $connection = new AMQPStreamConnection($host, $port, $user, $pass, $vhost);
        $channel = $connection->channel();
        /*
            The following code is the same both in the consumer and the producer.
            In this way we are sure we always have a queue to consume from and an
                exchange where to publish messages.
        */
        /*
            name: $queue
            passive: false
            durable: true // the queue will survive server restarts
            exclusive: false // the queue can be accessed in other channels
            auto_delete: false //the queue won't be deleted once the channel is closed.
        */
        $channel->queue_declare($queue, false, true, false, false);
        /*
            name: $exchange
            type: direct
            passive: false
            durable: true // the exchange will survive server restarts
            auto_delete: false //the exchange won't be deleted once the channel is closed.
        */
        $channel->exchange_declare($exchange, 'direct', false, true, false);
        $channel->queue_bind($queue, $exchange);
        
        /*
            queue: Queue from where to get the messages
            consumer_tag: Consumer identifier
            no_local: Don't receive messages published by this consumer.
            no_ack: Tells the server if the consumer will acknowledge the messages.
            exclusive: Request exclusive consumer access, meaning only this consumer can access the queue
            nowait:
            callback: A PHP Callback
        */
        $callback = function($message) {
            $log = json_decode($message->body);
            dump($log);
                Logger::log($log->cin, $log->response, 'http://localhost:8000/polls/show/'.$log->cin.'/');

        };
        $consumerTag = 'local.consumer';
        $channel->basic_consume($queue, $consumerTag, false, false, false, false, $callback);
        /**
         * @param \PhpAmqpLib\Channel\AMQPChannel $channel
         * @param \PhpAmqpLib\Connection\AbstractConnection $connection
         */
        $shutdonw = function($channel, $connection){
            $channel->close();
            $connection->close();
        };
    
        register_shutdown_function($shutdonw, $channel, $connection);
    
        while (count($channel->callbacks)) {
            $channel->wait();
        }
        return 0;

    }
}
