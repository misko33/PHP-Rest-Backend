<?php
class baza {
    
    private $veza;
    private $sqlQuery;

    public function __construct($host, $username, $password, $ime_baze) {
        $this->veza = new mysqli($host, $username, $password, $ime_baze);
        $this->sqlQuery = '';

        if (mysqli_connect_error()) {
            echo 'GREŠKA: Dogodila se greška kod povezivanja s bazom!'; 
            return false;
        }

        $this->veza->set_charset("utf8");
        $this->veza->query("SET SQL_MODE = ''");
    }

    public function query($sql) {
        $query = $this->veza->query($sql);
        if (!$this->veza->errno){
            if (isset($query->num_rows)) {
                $podaci = array();

                while ($red = $query->fetch_assoc()) {
                    $podaci[] = $red;
                }

                $rezultat = new stdClass();
                $rezultat->num_rows = $query->num_rows;
                $rezultat->row = isset($podaci[0]) ? $podaci[0] : array();
                $rezultat->rows = $podaci;

                unset($podaci);

                $query->close();

                return $rezultat;
            } else {
            return true;
            }
        } else {
            echo 'Greska kod upita [' .$sql. '] prema bazi!';
            return false;
        }
    }
    
}
?>