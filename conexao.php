<?php
class Database {
    private static $instance = null;
    private $conn;

    // CREDENCIAIS ATUALIZADAS PARA A HOSPEDAGEM INFINITYFREE
    private $host = "sql112.infinityfree.com";
    private $db   = "if0_40144537_financeiro";
    private $user = "if0_40144537";
    private $pass = "iHJ1NsqdDDoME8";
    private $charset = "utf8mb4";

    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>