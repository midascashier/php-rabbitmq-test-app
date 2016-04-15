<?php

namespace cashier\data;

class cashier
{

  public $sid;

  public $url;

  public function __construct()
  {
    $this->url = "http://cashier.backend.localhost:8080/ws/test.php";
    $this->sid = $this->login();
  }

  private function execPost($url, $params)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
  }

  private function login()
  {
    $params = array();
    $params['f'] = "login";
    $params['module'] = "security";
    $params['login'] = "test";
    $params['password'] = "test";
    $params['sys_access_pass'] = 1;
    $params['platform'] = "Backend";
    $params['userAgent'] = "Chrome";
    $params['isProxy'] = "1";
    try
    {
      $response = json_decode($this->execPost($this->url, $params));
      return $response->response->sid;
    }
    catch (Exception $e)
    {
      echo $e;
    }
  }

  public function getData($info)
  {
    $params = json_decode($info, true);
    $params['sys_access_pass'] = 1;
    $params['sid'] = $this->sid;
    //echo "- MODULE: " . $params['module'] . "\n";
    //echo "- FUNCTION: " . $params['f'] . "\n";
    try
    {
      $response = json_decode($this->execPost($this->url, $params));
      return $response;
    }
    catch (Exception $e)
    {
      echo $e;
    }
  }
}

?>