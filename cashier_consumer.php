<?php
/**
 *
 * @author jocampo
 *
 */
require_once(__DIR__ . '/config.php');
require_once (__DIR__ . '/Util.class.php');
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * this is the general consumer
 */
abstract class cashier_consumer
{
  /**
   * Use to info in log timeOut
   *
   * @var null
   */
  private $msg = null;

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
   * hostname to use in the CURL connection
   *
   * @var string
   */
  protected $hostname = null;

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

  /**
   * cashier_consumer constructor.
   */
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
   * @param $obj
   * @return string
   */
  private function objToStr($obj)
  {
    ob_start();
    print_r($obj);
    $str = ob_get_contents();
    ob_end_clean();
    return $str;
  }

  /**
   * @param $mixed
   * @return string
   */
  private function unknownToStr($mixed)
  {
    ob_start();
    var_dump($mixed);
    $str = ob_get_contents();
    ob_end_clean();
    return $str;
  }

  /**
   * log any invalid state
   *
   * @param string $url
   * @param string $sent
   * @param string $received
   * @param resource $ch
   */
  private function logOnInvalidState($url, $sent, $received, $ch)
  {
    if (is_string($sent)){
      $request = $sent;
    } else if (is_object($sent)) {
      $request = $this->objToStr($sent);
    } else {
      $request = '*****';
    }

    if (is_string($received)){
      $response = $received;
    } else if (is_object($received)) {
      $response = $this->objToStr($received);
    } else {
      $response = $this->unknownToStr($received);
    }

    $check = strpos($response, '"state":"ok"');
    if ($check === FALSE)
    {
      $lastErrorCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $lastStats = curl_getinfo($ch);
      $lastError = curl_error($ch);

      $logFile = "process_error_" . strtoupper('review') . ".txt";
      $content = date('Y-m-d H:i:s') . ":\n\n";
      $content .= "URL: \n";
      $content .= $url . " \n";
      $content .= "request: \n";
      $content .= $request . " \n";
      $content .= "response: \n";
      $content .= $response . " \n";
      $content .= "Error information: \n";
      $content .= $lastErrorCode . ":" . $lastError . " \n";
      $content .= "Stats: \n";
      $content .= $this->unknownToStr($lastStats) . " \n";
      $content .= "\n";
      @file_put_contents($logFile, $content, FILE_APPEND);
    }
  }

  private function logOnTimeOut($startTime, $initialMemory, $response = null){
    $msg = $this->msg;
    $timeOut = Util::timeForDisplay(Util::calculateProcessTime($startTime));
    $time = explode(' ', $timeOut);
    if($time[0] >= '3.00' && $this->queue != 'process'){

      $finalMemory = memory_get_usage();
      $finalMemory = Util::getMemoryDisplay($finalMemory);

      $logFile = "timeOut-Workers_" . strtoupper('review') . ".txt";
      $content = date('Y-m-d H:i:s') . ":\n\n";
      $content .= "Time to execution {$timeOut} \n\n";
      $content .= "Last Memory in use {$initialMemory} \n\n";
      $content .= "Final Memory: {$finalMemory} \n\n";
      $content .= "URL {$this->url} \n\n";
      $content .= "Queue {$this->queue} \n\n";
      $content .= "Qos {$this->qos} \n\n";
      $content .= "Message consumer: \n\n";
      $content .= json_encode($msg, JSON_PRETTY_PRINT) . "\n\n";
      $content .= "Response: \n\n";
      $content .= json_encode($response, JSON_PRETTY_PRINT) . "\n\n";
      @file_put_contents($logFile, $content, FILE_APPEND);
    }
  }

  /**
   * this simulate cashier connection
   *
   * @param array $params
   * @return mixed
   */
  private function execPost($params)
  {
    $headers = array();
    if ($this->hostname)
    {
      $headers[] = "Host: $this->hostname";
    }

    // params to debug
    if(IS_DEV){
      $this->url=$this->url."?XDEBUG_SESSION_START=ECLIPSE_DBGP";
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $this->url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    curl_close($ch);
    $json = json_decode($result);

    $this->logOnInvalidState($this->url, $params, $result, $ch);

    return $json;
  }

  /**
   * set cashier params
   * 
   * @param string $paramsRequest
   * 
   * @return string
   */
  private function setupRequest($paramsRequest){
    $params = json_decode($paramsRequest, true);
    $params['sys_access_pass'] = SYS_ACCESS_PASS;
    $params['userId'] = ONLINE_BE_USER_ID;
    $params['format'] = 'json';

    return json_encode($params);
  }
  
  /**
   * here we process the message read it from queue
   *
   * @param $msg \PhpAmqpLib\Message\AMQPMessage
   */
  public function process_message($msg)
  {
    if ($msg->body)
    {
      $this->msg = $msg;
      $memory = memory_get_usage();
      $memory = Util::getMemoryDisplay($memory);
      $startTime = Util::getStartTime();

      $request = $this->setupRequest($msg->body);
      $response = $this->execPost($request);
      if ($response)
      {
        $this->logOnTimeOut($startTime, $memory, $response);
        $reply_msg_properties = array();
        $reply_msg_properties['content_type'] = 'application/json';

        if ($msg->get('application_headers')->getNativeData()['correlation_id'])
        {
          $correlation_id = $msg->get('application_headers')->getNativeData();
          $correlation_id = $correlation_id['correlation_id'];
          $reply_msg_properties['correlation_id'] = $correlation_id;
        }

        $reply_queue = $msg->get('reply_to');
        $reply_msg = new \PhpAmqpLib\Message\AMQPMessage(json_encode($response), $reply_msg_properties);

        $msg->delivery_info['channel']->basic_publish($reply_msg, "", $reply_queue);
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
      }else{
        $msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag']);
      }
    }
  }

  /**
   * asks for channel, start consuming on specified queue and keeps waiting until get new messages
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
