<?php
require_once __DIR__ . '/vendor/autoload.php';

define('HOST', '127.0.0.1');
define('PORT', 5672);
define('USER', 'midas');
define('PASS', 'midas');
define('VHOST', 'vh');

// process worker configuration
define('CONSUMER_PROCESS_QUEUE', 'process');
define('CONSUMER_PROCESS_URL', 'http://cashier.backend.localhost:8080/ws/test.php');
define('CONSUMER_PROCESS_QOS', 2);
define('CONSUMER_PROCESS_QTY', 4);

// general setup const
// define('DEFAULT_QUEUES', array('process'=>1, 'transaction'=>1, 'customer'=>1, 'bonus'=>1));
define('DEFAULT_QUEUES', array('process' => 1));
define('DEFAULT_EXCHANGE', array('name' => 'work_request', 'type' => 'topic'));

?>