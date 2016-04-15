<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/cashier_data.php';

define('HOST', '127.0.0.1');
define('PORT', 5672);
define('USER', 'guest');
define('PASS', 'guest');
define('VHOST', 'chat');

//If this is enabled you can see AMQP output on the CLI
define('AMQP_DEBUG', false);


?>