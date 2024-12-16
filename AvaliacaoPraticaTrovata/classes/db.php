<?php
class db {
    private $host;
    private $user;
    private $pass;
    private $db;
    private $port;
    private $conn;


    public function __construct($host = 'localhost', 
                                $user = 'root', 
                                $pass = '', 
                                $db = 'bancodados', 
                                $port = '3307') {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->db = $db;
        $this->port = $port;
    }


    public function conectar() {
        try {
            $banco = "mysql:host={$this->host};port={$this->port};dbname={$this->db}";
            $this->conn = new PDO($banco, $this->user, $this->pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }

// CONTEM A CONEXÃO COM BD
    public function conexao() {
        return $this->conn;
    }
}


?>
