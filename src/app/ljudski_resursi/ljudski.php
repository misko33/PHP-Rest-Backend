<?php 
include('src/sys/model.php');

class ljudski extends crud_model{

    function __construct(){
        parent::__construct('default');
        $this->table = 'ljudski_resursi';
    }

}