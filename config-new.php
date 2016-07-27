<?php
require_once __DIR__ . '/vendor/autoload.php';

define('HOST', '127.0.0.1');
define('PORT', 5672);
define('USER', 'midas');
define('PASS', 'midas');
define('VHOST', 'vh');

// global consumer configuration
define('CONSUMER_QOS', 2);
define('CONSUMER_QTY', 4);

// Customer specific parameters.
define('CONSUMER_CUSTOMER_QUEUE', 'customer');
define('CONSUMER_CUSTOMER_URL', 'http://10.0.223.148:8080/wscashier/wsController.php');
define('CONSUMER_CUSTOMER_HOSTNAME', 'cashier.localhost');

// Transaction specific parameters.
define('CONSUMER_TRANSACTION_QUEUE', 'transaction');
define('CONSUMER_TRANSACTION_URL', 'http://10.0.223.148:8080/wscashier/wsController.php');
define('CONSUMER_TRANSACTION_HOSTNAME', 'cashier.localhost');

// Process specific parameters.
define('CONSUMER_PROCESS_QUEUE', 'process');
define('CONSUMER_PROCESS_URL', 'http://10.0.223.148:8080/wscashier/wsController.php');
define('CONSUMER_PROCESS_HOSTNAME', 'cashier.localhost');

// Backend specific parameters.
define('CONSUMER_BACKEND_QUEUE', 'backend');
define('CONSUMER_BACKEND_URL', 'http://10.0.223.148:8080/ws/wsBEController.php');
define('CONSUMER_BACKEND_HOSTNAME', 'cashier.backend.localhost');

// Bonus specific parameters.
define('CONSUMER_BONUS_QUEUE', 'bonus');
define('CONSUMER_BONUS_URL', 'http://10.0.223.148:8080/ws/wsBonus.php');
define('CONSUMER_BONUS_HOSTNAME', 'cashier.bonus.localhost');

// general setup const
define('DEFAULT_QUEUES', serialize(array('process'=>1, 'transaction'=>1, 'customer'=>1, 'backend'=>1, 'bonus'=>1)));
define('DEFAULT_EXCHANGE', serialize(array('name' => 'work_request', 'type' => 'topic')));

// cashier request params
define('SYS_ACCESS_PASS', 1);
define('IS_DEV', 1);

?>