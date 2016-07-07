<?php
/**
 *
 * @author jocampo
 *
 */
require_once("cashier_consumer.php");

/**
 * this class is to consume messages from the backend queue, what we do here is just load variables and then
 * keep listening for messages
 */
class cashier_consumer_backend extends cashier_consumer
{

  /**
   * @see consumer::init()
   */
  protected function init()
  {
    $this->queue = CONSUMER_BACKEND_QUEUE;
    $this->qos = CONSUMER_QOS;
    $this->url = CONSUMER_BACKEND_URL;
    $this->hostname = CONSUMER_BACKEND_HOSTNAME;
  }
}

?>
