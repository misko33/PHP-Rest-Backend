<?php 
require_once('src/sys/rest.php');

class testcurl {

    private $rest = null;

    function __construct(){
        $this->rest = new rest();
    }

    public function list(){
        
        $params = [
            'per_page' => 20,
            'page' => 0
        ];
        
        echo $this->rest->get("/backend/index.php/example/example/list", $params);
    }

    public function add(){
        
        $params = [
            'ime' => 'Druce',
            'prezime' => 'Doe',
            'oib' => '33483046911'
        ];
        
        echo $this->rest->post("/backend/index.php/example/example/add", "", $params);
    }

    public function edit(){
        
        $params = [
            'id' => 236,
            'ime' => 'Druce',
            'prezime' => 'Doe',
            'oib' => '00000046911'
        ];

        echo $this->rest->post("/backend/index.php/example/example/edit", "", $params);
    }

    public function delete(){
        
        $params = [
            'id' => 232,
        ];

        echo $this->rest->post("/backend/index.php/example/example/delete", "", $params);
    }

}