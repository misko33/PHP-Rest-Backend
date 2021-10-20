<?php
require_once(SYSPATH.'database/DB.php');

class base_model {

  protected $db = null;
  protected $table = null;
  protected $post = null;
  
  public function __construct($conf = 'default') {
    $this->db = DB($conf);

    $json = file_get_contents('php://input');
    $this->post = json_decode($json);
  }
}

class crud_model extends base_model{

  protected $per_page = 10;
  protected $page = 1;
  protected $id = null;
  
  public function __construct($conf) {
    parent::__construct($conf);

    if(isset($_GET['per_page']))  $this->per_page = clean($_GET['per_page']);
    if(isset($_GET['page']))      $this->page = clean($_GET['page']);

    if(isset($this->post->id)) 
    {
      $this->id = $this->post->id;
      unset($this->post->id);
    }
  }

  public function add(){
    $this->db->insert($this->table, $this->post);
    show_success();
  }

  public function list(){
    echo json_encode($this->db->get($this->table, $this->per_page, $this->per_page * $this->page)->result());
  }

  public function edit(){
    if (is_null($this->id)) show_error();
    else 
    {
      $this->db->where('id', $this->id)->update($this->table, $this->post);
      show_success();
    }
  }

  public function delete(){
    if (is_null($this->id)) show_error();
    else 
    {
      $this->db->where('id', $this->id)->delete($this->table);
      show_success();
    }
  }
}