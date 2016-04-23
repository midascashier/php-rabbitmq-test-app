<?php
require_once ("cashier_consumer.php");

class cashier_consumer_process extends cashier_consumer
{

  /*
   * (non-PHPdoc)
   * @see consumer::init()
   */
  protected function init()
  {
    $this->queue = CONSUMER_PROCESS_QUEUE;
    $this->qos = CONSUMER_PROCESS_QOS;
    $this->url = CONSUMER_PROCESS_URL;
  }
}

$consumer=new cashier_consumer_process();
$consumer->consume();

?>