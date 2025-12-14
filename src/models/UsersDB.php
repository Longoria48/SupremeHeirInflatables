<?php
//UsersDB.php
class UsersDB {
    private PDO $pdo;


    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getUserByUsername(string $username): ?array {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
 
    }
}
?>
