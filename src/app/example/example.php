<?php 
require_once('src/sys/model.php');
require_once('src/sys/rest.php');

class example extends crud_model{

    private $rest;

    function __construct(){
        $this->auth = true;
        parent::__construct('default');
        $this->table = "popisljudi";

        $this->rest = new rest();
    }

    public function test(){
        #echo $this->rest->get("/backend/index.php/example/example/list");

        $array1 = [
            'per_page' => 30,
            'page' => 0
        ];
        $this->set_params($array1)->list();
    }

    public function add(){
        $columns = ['ime', 'prezime', 'oib'];
        if(isset($this->post)) {
            if(!$this->postIncomplete($columns)){
                if ($this->validateOib() && $this->validateName()){
                    $this->db->insert($this->table, $this->post);
                    show_success();
                }
            }
            else {
                $notset = $this->postIncomplete($columns);
                foreach ($notset as $notsetVal) {
                    show_error('0', 'Post ne sadrzi '.$notsetVal.'.');
                }
            }
        }
        else show_error('0', 'Post ne postoji.');
    }

    private function validateOib(){
        if(strlen($this->post['oib']) != 11) show_error('0', 'OIB treba imati 11 znakova.');
        else return True;
    }

    public function validateName(){
        if (preg_match('~^\p{Lu}~u', $this->post['ime']) && preg_match('~^\p{Lu}~u', $this->post['prezime'])) {
            return True;
        } 
        else show_error('0', 'Ime i prezime treba pocinjati velikim slovom.');
    }

    protected function postIncomplete($columns){
        $notset = [];
        foreach ($columns as $val) {
            if(!isset($this->post[$val])){
                array_push($notset, $val);
            }
        }

        if (!empty($notset)){
            return $notset;
        }
        else return False;
    }

}