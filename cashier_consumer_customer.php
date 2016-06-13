<?php
/**
 *
 * @author jocampo
 *
 */
require_once ("cashier_consumer.php");

/**
 * this class is to consume messages from process queue, what we do here is just load variables and then
 * keep listening for messages
 */
class cashier_consumer_customer extends cashier_consumer
{

  /**
   *
   * @see consumer::init()
   */
  protected function init()
  {
    $this->queue = CONSUMER_QUEUE;
    $this->qos = CONSUMER_QOS;
    $this->url = CONSUMER_URL;
    $this->hostname = CONSUMER_HOSTNAME;
  }
}

?>