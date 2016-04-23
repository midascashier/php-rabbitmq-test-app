<?php
require_once (__DIR__ . '/config.php');
use PhpAmqpLib\Connection\AMQPStreamConnection;

abstract class cashier_consumer
{

  protected $queue = null;

  protected $url = null;

  protected $qos = null;

  protected $conn;

  public function __construct()
  {
    $this->init();
    $this->connection();
  }

  /**
   * start rabbit connection
   */
  private function connection()
  {
    $this->conn = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST);
  }

  /**
   * create new channel and set up QOS
   *
   * @return \PhpAmqpLib\Channel\AMQPChannel
   */
  private function get_channel()
  {
    $channel = $this->conn->channel();
    $channel->basic_qos(null, $this->qos, null);
    return $channel;
  }

  public function process_message($msg)
  {
    // var_dump($msg->body);
    // echo "\n";
  }

  public function consume()
  {
    $channel = $this->get_channel();
    $channel->basic_consume($this->queue, null, false, false, false, false, array(
      $this,
      'process_message'
    ));
    
    while (count($channel->callbacks))
    {
      $channel->wait();
    }
  }

  protected abstract function init();
}

?>