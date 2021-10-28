<?php 
require_once('src/sys/rest.php');

class testcurl {

    private $rest = null;

    function __construct(){
        $this->rest = new rest();
    }

    public function test(){
        $params = [
            'id' => 12,
            'ime' => 'Druce',
            'prezime' => 'Doe',
            'oib' => '13483046911'
        ];
        
        $params2 = [
            'firstname' => 'Bruce',
            'surname' => 'Doe',
            'email' => 'bruce@bruce.com',
            'phone' => '05 27 41 38 26',
            'birthdate' => '2022-06-02'
        ];
        
        $array1 = [
            'per_page' => 2,
            'page' => 0
        ];
        
        echo $this->rest->get("/backend/index.php/example/example/list", $array1);
    }

}