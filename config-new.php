<?php
require_once __DIR__ . '/vendor/autoload.php';

define('HOST', '127.0.0.1');
define('PORT', 5672);
define('USER', 'midas');
define('PASS', 'midas');
define('VHOST', 'vh');

// consumer configuration
define('CONSUMER_URL', 'http://10.0.223.155:8080/wscashier/wsController.php');
define('CONSUMER_HOSTNAME', 'cashier.localhost');
define('CONSUMER_QOS', 2);
define('CONSUMER_QTY', 4);

// Customer specific parameters.
define('CONSUMER_CUSTOMER_QUEUE', 'customer');

// Process specific parameters.
define('CONSUMER_PROCESS_QUEUE', 'process');

// general setup const
define('DEFAULT_QUEUES', serialize(array('process'=>1, 'transaction'=>1, 'customer'=>1, 'bonus'=>1)));
define('DEFAULT_EXCHANGE', serialize(array('name' => 'work_request', 'type' => 'topic')));

?>