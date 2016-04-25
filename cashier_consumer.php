<?php
/**
 *
 * @author jocampo
 *
 */
require_once (__DIR__ . '/config.php');
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * this is the general consumer
 */
abstract class cashier_consumer
{

  /**
   * define the queue where the consumer will connect and wait for messages
   *
   * @var string
   */
  protected $queue = null;

  /**
   * this is where the consumer will do the post asking for messages response
   *
   * @var string
   */
  protected $url = null;

  /**
   * this specifies the limit of unacknowledged messages on a channel
   *
   * @var int
   */
  protected $qos = null;

  /**
   * rabbit connection
   *
   * @var AMQPStreamConnection
   */
  protected $conn;

  public function __construct()
  {
    $this->init();
    $this->connect();
  }

  /**
   * start rabbit connection
   */
  private function connect()
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

  /**
   * this simulate cashier connection
   *
   * @param unknown $params          
   * @return mixed
   */
  private function execPost($params)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $this->url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($result);
    return $result;
  }

  /**
   * here we process the message read it from queue
   *
   * @param AMQPMessage $msg          
   */
  public function process_message($msg)
  {
    if ($msg->body)
    {
      $response = $this->execPost($msg->body);
      if ($response)
      {
        $correlation_id = $msg->get('application_headers')->getNativeData();
        $correlation_id = $correlation_id['correlation_id'];
        $reply_queue = $msg->get('reply_to');
        $reply_msg = new \PhpAmqpLib\Message\AMQPMessage(json_encode($response), array(
          'content_type' => 'application/json',
          'correlation_id' => $correlation_id
        ));
        $msg->delivery_info['channel']->basic_publish($reply_msg, "", $reply_queue);
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
      }
      else
      {
        $message->delivery_info['channel']->basic_nack($message->delivery_info['delivery_tag']);
      }
    }
  }

  /**
   * this method ask for channel, start consuming on specified queue and keeps waiting until get nes messages
   */
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