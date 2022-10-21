<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(SYSPATH.'database/DB.php');
require_once(SYSPATH.'rest.php');

class Base {

  protected $req_data = null;
  protected $auth     = null;
  private   $token    = null;
  private   $headers  = null;

  public function __construct(){
  }

  public function index($func){
    if (method_exists($this, $func)) return $this->$func();
    else err("Can't resolve ".$_SERVER['REQUEST_URI']);
  }
}

class Base_model extends Base {

  protected $db = null;
  protected $table = null;
  
  public function __construct($conf = 'default') {
    parent::__construct();
    $this->db =& load_class('db', DB('radius'));
  }
}

class Crud_model extends Base_model{

  public function __construct($conf) {
    parent::__construct($conf);
  }

  public function add(){
    res($this->db->insert($this->table, $this->post));
  }

  public function list(){
    res($this->db->get($this->table, $this->per_page, $this->per_page * $this->page)->result());
  }

  public function edit(){
    if (isset($_POST->id)) err("No ID");
    else res($this->db->where('id', $_POST->id)->update($this->table, $this->post));
  }

  public function delete(){
    if (isset($_POST->id)) err("No ID");
    else res($this->db->where('id', $this->id)->delete($this->table));
  }
}