<?php
require_once __DIR__ . '/../../vendor/autoload.php'; // Asegura la ruta correcta

// Configurar la zona horaria
date_default_timezone_set('America/Guatemala');

use Dotenv\Dotenv;

class Database {
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            // Cargar el archivo .env desde la raíz del proyecto
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->load();

            // Leer las variables de entorno
            $host = $_ENV['DB_HOST'];
            $db_name = $_ENV['DB_NAME'];
            $username = $_ENV['DB_USER'];
            $password = $_ENV['DB_PASSWORD'];

            // Establecer la conexión con PDO
            $this->conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
