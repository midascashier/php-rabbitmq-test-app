<?php
/**
 *
 * @author jocampo
 *
 */
require_once("cashier_consumer.php");

/**
 * this class is to consume messages from the transaction queue, what we do here is just load variables and then
 * keep listening for messages
 */
class cashier_consumer_transaction extends cashier_consumer
{

  /**
   * @see consumer::init()
   */
  protected function init()
  {
    $this->queue = CONSUMER_TRANSACTION_QUEUE;
    $this->qos = CONSUMER_QOS;
    $this->url = CONSUMER_TRANSACTION_URL;
    $this->hostname = CONSUMER_TRANSACTION_HOSTNAME;
  }
}

?>
