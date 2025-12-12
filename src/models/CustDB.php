<?php
class CustDB
{
    private PDO $conn;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    public function findCustomer(string $firstName, string $lastName, string $phone, string $email): ?array
    {
        $sql = "SELECT customer_id 
                FROM customers 
                WHERE first_name = ?
                AND last_name = ?
                AND phone = ?
                AND email = ?
                LIMIT 1";

        //Verify customer exists
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$firstName, $lastName, $phone, $email]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        //return customer
        return $customer ?: null;
    }

    public function insertCustomer(string $firstName,string $lastName,string $phone,string $email): int
    {
        $insertCustomer = $this->conn->prepare("INSERT INTO customers(first_name, last_name, phone, email) VALUES(?,?,?,?)");
        $insertCustomer->execute([$firstName,$lastName,$phone,$email]);
        return (int)$this->conn->lastInsertId();
    }

    public function updateCustomer(string $cid, string $phone): void
    {
        //Update customer phone
        $updateCustomer = $this->conn->prepare("UPDATE customers
            SET phone = :phone
            WHERE customer_id = :cid LIMIT 1");

        $updateCustomer->execute([
            ':phone' => $phone,
            ':cid' => (int)$cid
        ]);
    }

    //Do we want to add a delete customer?
    public function beginTransaction(): void
    {
        $this->conn->beginTransaction();
    }

}
?>