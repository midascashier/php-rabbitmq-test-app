<?php
require_once __DIR__ . '/vendor/autoload.php';

define('HOST', '127.0.0.1');
define('PORT', 5672);
define('USER', 'midas');
define('PASS', 'midas');
define('VHOST', 'vh');

// process worker configuration
define('CONSUMER_QUEUE', 'customer');
define('CONSUMER_URL', 'http://cashier.localhost:8080/wscashier/wsController.php');
define('CONSUMER_QOS', 2);
define('CONSUMER_QTY', 4);

// general setup const
// define('DEFAULT_QUEUES', array('process'=>1, 'transaction'=>1, 'customer'=>1, 'bonus'=>1));
define('DEFAULT_QUEUES', serialize(array('customer' => 1)));
define('DEFAULT_EXCHANGE', serialize(array('name' => 'work_request', 'type' => 'topic')));

?>