<?php 
require_once('src/app/example/example.php');

class ljudski {

    private $example = null;

    function __construct(){
        $this->example = new example();
    }

    public function test(){
        $this->example->list();
    }
}