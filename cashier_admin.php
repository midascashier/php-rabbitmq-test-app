<?php
require_once (__DIR__ . '/config.php');
use PhpAmqpLib\Connection\AMQPStreamConnection;

class cashier_admin
{

  protected $conn;

  protected function fork()
  {
    if (!function_exists('pcntl_fork'))
    {
      return -1;
    }
    $pid = pcntl_fork();
    if ($pid === -1)
    {
      throw new RuntimeException('Unable to fork child worker.');
    }
    return $pid;
  }

  /**
   * start rabbit connection
   */
  private function connection()
  {
    $this->conn = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST);
  }

  /**
   * create new channel
   *
   * @return \PhpAmqpLib\Channel\AMQPChannel
   */
  private function get_channel()
  {
    $channel = $this->conn->channel();
    return $channel;
  }

  public function setup()
  {
    $exchange_info = DEFAULT_EXCHANGE;
    $exchange_name = $exchange_info["name"];
    $exchange_type = $exchange_info["type"];
    
    echo "Starting Connection... \n";
    $this->connection();
    echo "Getting Channel... \n";
    $channel = $this->get_channel();
    foreach (DEFAULT_QUEUES as $queue=>$qty)
    {
      $routing_key = $queue . ".*";
      $channel->exchange_declare($exchange_name, $exchange_type, false, true, false);
      echo "--- Creating Exchange " . $exchange_name . "\n";
      $channel->queue_declare($queue, false, true, false, false);
      echo "--- Creating queue $queue\n";
      $channel->queue_bind($queue, $exchange_name, $routing_key);
      echo "--- Binding $queue to $exchange_name with routing key: $routing_key\n";
    }
    $channel->close();
    echo "Closing Channel...\n";
    $this->conn->close();
    echo "Closing Connection...\n";
    echo "Done.\n";
  }

  protected function get_consumer_validation_format()
  {
    return "|([^:]+):([\\d]+)|i";
  }

  protected function validate_consumer($consumer_info)
  {
    $isValid = preg_match($this->get_consumer_validation_format(), $consumer_info, $info);
    if (!$isValid)
    {
      echo "Error: Bad parameters format\n";
      return false;
    }
    elseif (!array_key_exists($info[1], DEFAULT_QUEUES))
    {
      echo "No Action Allowed\n";
      return false;
    }
    else
    {
      return true;
    }
  }

  public function start($argv)
  {
    if (strtolower($argv[2] != "all"))
    {
      unset($argv[0]);
      unset($argv[1]);
      $argv = array_values($argv);
      for ($i = 0; $i < count($argv); $i ++)
      {
        $isValid = $this->validate_consumer($argv[$i]);
      }
      
      if (!$isValid)
      {
        return false;
      }
    }
    else
    {
      $argv=array();
      foreach (DEFAULT_QUEUES as $queue=>$qty)
      {
        $argv[]="$queue:$qty";
      }
    }

    for ($i = 0; $i < count($argv); $i ++)
    {
      preg_match($this->get_consumer_validation_format(), $argv[$i], $info);
      $qty = $info[2];
      $consumer_name = $info[1];
      
      for ($x = 0; $x < $qty; $x ++)
      {
        $pid = pcntl_fork();
        if ($pid == 0)
        {
          $class_name = "cashier_consumer_" . $consumer_name;
          $class_file = "$class_name.php";
          if (file_exists($class_file))
          {
            require_once ($class_file);
            $worker = new $class_name();
            $worker instanceof cashier_consumer;
            $worker->consume();
          }
          else
          {
            throw new RuntimeException("Class $class_name not found");
          }
        }
      }
    }
    pcntl_wait($status);
  }
}

if (count($argv) == 1)
{
  echo "Error: No Parameters Provided\n";
}
else
{
  $consumers = new cashier_admin();
  $argv[1] = strtolower($argv[1]);
  switch ($argv[1])
  {
    case "setup":
      $consumers->setup();
      break;
    case "start":
      if (count($argv) > 2)
      {
        $consumers->start($argv);
      }
      else
      {
        echo "Error: No consumer Provided\n";
      }
      break;
    default:
      echo "Error: Invalid option\n";
  }
}

?>