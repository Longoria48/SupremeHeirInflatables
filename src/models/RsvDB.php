<?php
class RsvDB
{
    private PDO $conn;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }
//--------------------------------------------------------------

//Helper methods for transactions
function beginTransaction(): void
{
    $this->conn->beginTransaction();
}

function commit(): void
{
    $this->conn->commit();
}

function rollBack(): void
{
    $this->conn->rollBack();
}

    public function createRSV(int $customerId,string $startDate,string $endDate,string $address,string $city,string $zip): int 
    {
        //Insert reservation
        $insertRsv = $this->conn->prepare("INSERT INTO reservations (customer_id, start_date, end_date, order_status, address, city, zip)
                                VALUES (:customer_id, :start_date, :end_date, :status, :address, :city, :zip)");
        
        $insertRsv->execute([
                ':customer_id'=> $customerId,
                ':start_date' => $startDate,
                ':end_date'   => $endDate,
                ':status'     => 'Pending',
                ':address'    => $address,
                ':city'       => $city,
                ':zip'        => $zip
            ]);
            return (int)$this->conn->lastInsertId();
    }

    public function updateRsvDetails(int $reservationId,int $inventoryId,int $quantity): void
    {
            //Insert Reservation Details
            $insertRsvDetail = $this->conn->prepare("INSERT INTO rsv_details (reservation_id, inventory_id, quantity)
                                    VALUES (?, ?, ?)");
            $insertRsvDetail->execute([$reservationId, $inventoryId, $quantity]);
    }

    public function updateRSV(string $address,string $city,string $zip,string $start,string $end,string $status,string $id)
    {
        //Update Reservation fields(addy,city,zip,dates,status)
        $updateRsv = $this->conn->prepare("UPDATE reservations 
            SET address = :address, 
            city = :city, 
            zip = :zip,
            start_date = :start,
            end_date = :end,
            order_status = :status
            WHERE reservation_id = :id 
            LIMIT 1");

        $updateRsv->execute([
            ':address' => $address,
            ':city' => $city,
            ':zip' => $zip,
            ':start' => $start,
            ':end' => $end,
            ':status' => $status,
            ':id' => $id
        ]);
    }

    public function deleteCheck(int $reservation_id): ?array
    {
        //Lock the row to prevent conocurrent changes while we decide/delete
        $checkstmt = $this->conn->prepare("SELECT order_status 
        FROM reservations WHERE reservation_id = :reservation_id FOR UPDATE");
        $status = $checkstmt->fetch(PDO::FETCH_ASSOC);
        return $status ?: null;
    }

    public function deleteRSVChild(int $reservation_id): int
    {
        //Delete Children (rsv details table)
        $deleteRsvDetails = $this->conn->prepare("DELETE FROM rsv_details WHERE reservation_id = :reservation_id");
        $deleteRsvDetails->bindParam(':reservation_id', $reservation_id);
        $deleteRsvDetails->execute();
        return $deleteRsvDetails->rowCount();
    }

    public function deleteRSVParent(int $reservation_id): int
    {
        //Delete Parent (reservation table)
        $deleteRsv = $this->conn->prepare("DELETE FROM reservations WHERE reservation_id = :reservation_id");
        $deleteRsv->bindParam(":reservation_id", $reservation_id);
        $deleteRsv->execute();
        return $deleteRsv->rowCount();
    }

    public function fetchRSVInfo(): string
    {
        //Select all rsv & ph# where customerID = RsvCustomerID
        $sql = "SELECT r.*, c.phone FROM reservations r
            JOIN customers c ON c.customer_id = r.customer_id";
        return $sql;
    }

    public function fetchRSV(string $sql, array $params=[]): array
    {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $reservations;    
    }
}
?>