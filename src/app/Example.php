<?php 
require_once('src/sys/model.php');

class Example extends Base{

    public function ok(){
        return ['data' => 'ok'];
    }

}