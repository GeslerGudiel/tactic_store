<?php
class User {
    private $db;

    public function __construct($db) {
        if ($db instanceof PDO) {
            $this->db = $db;
        } else {
            throw new Exception('Database connection is not valid.');
        }
    }

    public function findUserByEmail($email) {
        $query = "SELECT * FROM usuarios WHERE correo = :correo";
        $stmt = $this->db->prepare($query);  // Asegúrate de que $this->db es un objeto PDO
        $stmt->bindParam(':correo', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Otras funciones relacionadas con el usuario
}
?>
