<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rest {
  public $conf = null;
  public $confType = null;
  public $data = null;
  public $http_code = null;

  public function __construct($conf = 'default') {
    if ( ! file_exists($file_path = 'src/config/rest.php'))
      err('The configuration file rest.php does not exist.');
    
    include($file_path);
    $this->conf = $config[$conf];
  }

  protected function override_config($config){
    $conf = $this->conf;
    
    if ($config)
      foreach($config as $key => $c)
          $conf[$key] = $c;

    return $conf;
  }

  public function get($url, $config = false){
    $conf = $this->override_config($config);
    $conf[CURLOPT_CUSTOMREQUEST]  = "GET";
    $conf[CURLOPT_URL] = (isset(parse_url($url)['scheme'])) ? $url: $conf[CURLOPT_URL].$url;

    $this->curl($conf);
    return [ 'data' => $this->data, 'status' => $this->http_code ];
  }

  public function post($url, $data, $config = false){
    $conf = $this->override_config($config);
    $conf[CURLOPT_CUSTOMREQUEST]  = "POST";
    $conf[CURLOPT_URL] = (isset(parse_url($url)['scheme'])) ? $url: $conf[CURLOPT_URL].$url;
    isset($conf[CURLOPT_POSTFIELDS]) || $conf[CURLOPT_POSTFIELDS] = json_encode($data);

    $this->curl($conf);
    return [ 'data' => $this->data, 'status' => $this->http_code ];
  }

  public function put($url, $data, $config = false){
    $conf = $this->override_config($config);
    $conf[CURLOPT_CUSTOMREQUEST]  = "PUT";
    $conf[CURLOPT_URL] = (isset(parse_url($url)['scheme'])) ? $url: $conf[CURLOPT_URL].$url;
    $conf[CURLOPT_POSTFIELDS] = json_encode($data);

    $this->curl($conf);
    return [ 'data' => $this->data, 'status' => $this->http_code ];
  }

  public function curl($curlopt = false) { 
    $return = [];

    if ($curlopt) {
      $curl_session = null;
      
      try {
        $curl_session = curl_init();
        
        if ($curl_session === false) {
          throw new Exception('failed to initialize curl object!');
        }

        curl_setopt_array($curl_session, $curlopt);
        $api_res = json_decode(curl_exec($curl_session));

        if ($api_res === false) {
          throw new Exception(curl_error($curl_session), curl_errno($curl_session));
        }

        $curl_info = curl_getinfo($curl_session);
        $this->data = $api_res;
        $this->http_code = $curl_info['http_code'];
      } 
      catch (Exception $e) {
        $this->data = [ 'message' => $e->getMessage(), 'line' => $e->getCode() ];
        $this->http_code = 500;
      } 
      finally {
        if ($curl_session) curl_close($curl_session);
      }

      return;
    }

    $this->data = [ 'message' => 'No curl options provided!' ];
    $this->http_code = 500;
  }
}
