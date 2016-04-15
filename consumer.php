<?php
ini_set('display_errors', '1');
ini_set('error_reporting', '6135');

include (__DIR__ . '/config.php');
use PhpAmqpLib\Connection\AMQPStreamConnection;
use cashier\data\cashier;

class WRConsumer
{

  public $conn;

  public $channel;

  private $queue;

  const EXCHANGE = "work_request";

  const QOS = 1;

  public function __construct($queue)
  {
    $this->queue = $queue;
    $this->connection();
  }

  private function connection()
  {
    $this->cashier_data = new cashier();
    $this->conn = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST);
    $this->declarations($this->get_channel());
  }

  public function get_channel()
  {
    $channel = $this->conn->channel();
    $channel->basic_qos(null, self::QOS, null);
    return $channel;
  }

  public function declarations($channel)
  {
    $routing_key = $this->queue . ".*";
    $channel->queue_declare($this->queue, false, true, false, false);
    $channel->exchange_declare(self::EXCHANGE, 'topic', false, true, false);
    $channel->queue_bind($this->queue, self::EXCHANGE, $routing_key);
    $channel->close();
  }

  public function process_message($message)
  {
    if ($message->body)
    {
      echo "- EXCHANGE: " . $message->get('exchange') . "\n";
      echo "- ROUTING KEY: " . $message->get('routing_key') . "\n";
      $response = $this->cashier_data->getData($message->body);
      if ($response)
      {
        echo "- RESPONSE: \n";
        echo "-         State: " . $response->state . "\n";
        echo "-         Content: " . $response->userMessage . "\n\n\n";
        echo "**********************************************\n\n\n";
        $correlation_id = $message->get('application_headers')->getNativeData();
        $correlation_id = $correlation_id['correlation_id'];
        $reply_queue = $message->get('reply_to');
        $msg = new \PhpAmqpLib\Message\AMQPMessage(json_encode($response), array(
          'content_type' => 'application/json',
          'correlation_id' => $correlation_id
        ));
        
        $message->delivery_info['channel']->basic_publish($msg, "", $reply_queue);
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
      }
      else
      {
        $message->delivery_info['channel']->basic_nack($message->delivery_info['delivery_tag']);
        echo "***** ERROR: NO RESPONSE *****\n\n\n";
      }
    }
  }

  public function consume($channel)
  {
    $channel->basic_consume($this->queue, null, false, false, false, false, array(
      $this,
      'process_message'
    ));
  }
}

$action = $argv[1];

if (!$action)
{
  echo "No Action";
}
else
{
  $consumer = new WRConsumer($action);
  
  $all_channels = array();
  
  for ($i = 0; $i < 10; $i ++)
  {
    $channel = $consumer->get_channel();
    $all_channels[] = $channel;
    $consumer->consume($channel);
  }
  
  while (true)
  {
    $read = array($consumer->conn->getSocket());
    $write = null;
    $except = null;
    if (false === ($changeStreamsCount = stream_select($read, $write, $except, 60)))
    {
      /* Error */
    }
    elseif ($changeStreamsCount > 0)
    {
      foreach ($all_channels as $channel)
      {
        $channel->wait();
      }
      //stream_set_blocking($consumer->conn->getSocket(), 0);
      //var_dump(stream_get_contents($consumer->channel->getSocket()));
    }
  }
    
  
}
?>