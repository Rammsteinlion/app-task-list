<?php
namespace App\DB;

require_once (__DIR__ . '/../../vendor/autoload.php');

use Dotenv\Dotenv;
use PDO;
use PDOException;

class Database {
    private $host;
    private $db;
    private $user;
    private $password;
    private $charset;

    public function __construct() {
        // Cargar el archivo .env
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $this->host = $_ENV['IP']; 
        $this->db = $_ENV['DB']; 
        $this->user = $_ENV['USER']; 
        $this->password = $_ENV['PASSWORD'];
        $this->charset = 'utf8mb4'; 
      
    }

    public function connect() {
        try {
            $connection = "mysql:host=" . $this->host . ";dbname=" . $this->db . ";charset=" . $this->charset;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $pdo = new PDO($connection, $this->user, $this->password, $options);
            return $pdo;
        } catch (PDOException $e) {
            echo 'Error de conexión: ' . $e->getMessage();
            return null;
        }
    }
}
