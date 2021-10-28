<?php

class rest {

    protected $conf = null;
    protected $rest = null;
    protected $url = null;
    protected $auth = null;
    protected $user = null;
    protected $pass = null;
    protected $cert = null;
    protected $token = null;

    public function __construct($conf = 'default') {
        $this->conf = $conf;
        $this->rest = [ 
            'get' => array(
                CURLOPT_URL => null,
                CURLOPT_RETURNTRANSFER  => 1,
                CURLOPT_TIMEOUT => 3
            ),
            'post' => array(
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => null
            )
        ];

        if ( ! file_exists($file_path = 'src/config/rest.php'))
        {
            show_error('1', 'The configuration file rest.php does not exist.');
        }
    
        require_once($file_path);

        $this->url = $config[$conf]['url'];
        $this->auth = $config[$conf]['auth'];
        $this->user = $config[$conf]['username'];
        $this->pass = $config[$conf]['password'];
        $this->token = $config[$conf]['token'];
        $this->cert = $config[$conf]['cert'];
    }

    public function get($url, $query = "", $type = "json"){
        return $this->curl($this->url.$url, $query, "", $type);
    }

    public function post($url, $query = "", $body = "", $type = "json"){
        return $this->curl($this->url.$url, $query, $body, $type);
    }

    public function set_config($method = "get", $params = []){
        $this->rest[$method] = $params;
    }

    public function basic($cURLConnection){
        curl_setopt($cURLConnection, CURLOPT_USERPWD, $this->user . ":" . $this->pass);
    }

    public function digest($cURLConnection){
        curl_setopt($cURLConnection, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($cURLConnection, CURLOPT_USERPWD, $this->user . ":" . $this->pass);
    }

    public function bearer($cURLConnection){
        curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer '.$this->token));
    }

    public function apikey($cURLConnection){
        curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: ' . $this->token));
    }
    
    private function curl($url = false, $query = "", $body = "", $type = 'json') {
        $supported_formats = array(
            'xml' => 'application/xml',
            'json' => 'application/json',
            'jsonp' => 'application/javascript',
            'serialized' => 'application/vnd.php.serialized',
            'php' => 'text/plain',
            'html' => 'text/html',
            'csv' => 'application/csv'
        );
        $cURLConnection = curl_init();

        $this->rest['get'][CURLOPT_HTTPHEADER] = array('Content-Type: '.$supported_formats[$type]);
        $this->rest['get'][CURLOPT_URL] = $url;
        if ($query) $this->rest['get'][CURLOPT_URL] = $url."?".http_build_query($query);
        if ($body) $this->rest['post'][CURLOPT_POSTFIELDS] = json_encode($body);

        if ($body) {
            $post_array = $this->rest['get'] + $this->rest['post'];
            curl_setopt_array($cURLConnection, $post_array);
        } else {
            curl_setopt_array($cURLConnection, $this->rest['get']); 
        }
    
        $this->{$this->auth}($cURLConnection);

        $apiResponse = curl_exec($cURLConnection);
        $curl_info = curl_getinfo($cURLConnection);

        $return = false;

        if($curl_info['http_code'] == '200' || $curl_info['http_code'] == '201' || $curl_info['http_code'] == '0')
        {            
            $return = $apiResponse;     
        }
        curl_close($cURLConnection);

        return $return;
        
    }

}