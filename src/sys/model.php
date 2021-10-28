<?php
require_once(SYSPATH.'database/DB.php');

class base_model {

  protected $db = null;
  protected $table = null;
  protected $post = null;
  protected $auth = null;
  private $token = null;
  private $headers = null;
  
  public function __construct($conf = 'default') {
    $this->db = DB($conf);
    if ($this->auth == true){
      require_once('src/config/keys.php');
      $this->headers = getallheaders();
      if(isset($this->headers['Authorization'])){
        $this->token = $this->headers['Authorization'];
      } 
      if (!in_array($this->token, $ips)){
        show_error('500', 'Unauthorized.');
      }
    }

    $json = file_get_contents('php://input');
    $this->post = json_decode($json, true);
  }
}

class crud_model extends base_model{

  protected $per_page = 10;
  protected $page = 0;
  protected $id = null;
  
  public function __construct($conf) {
    parent::__construct($conf);

    if(isset($_GET['per_page']))  $this->per_page = clean($_GET['per_page']);
    if(isset($_GET['page']))      $this->page = clean($_GET['page']);

    if(isset($this->post['id'])) 
    {
      $this->id = $this->post['id'];
      unset($this->post['id']);
    }
  }

  public function set_params($params){
    if (isset($params['per_page'])) $this->per_page = $params['per_page'];
    if (isset($params['page'])) $this->page = $params['page'];
    if (isset($params['id'])) {
      $this->id = $params['id'];
      unset($params['id']);
    }
    $this->post = $params;
    return $this;
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