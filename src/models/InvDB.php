<?php
class InvDB
{
    private PDO $conn;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }
    //--------------------------------------------------------------

    public function inventoryLock(string $item): ?array
    {
        //Look up the inventory item by product_name
        //Lock inventory row for this item so stock & availability
        // cannot change under us while we compute availability
        $inventoryStmt = $this->conn->prepare("SELECT inventory_id, stock, product_name 
                    FROM inventory WHERE product_name = ? LIMIT 1 FOR UPDATE");
        $inventoryStmt->execute([$item]);
        $inventory = $inventoryStmt->fetch(PDO::FETCH_ASSOC);
        return $inventory ?: null;
    }

    public function inventoryOverlap(int $inventoryId, string $startDate, string $endDate): int
    {
        //Check how many units are already reserved for overlapping dates
        //Overlap logic: conditions cannot be true (existing.end < new.start || exisiting.start > new.end)
        $overLapStmt = $this->conn->prepare(
                "SELECT COALESCE(SUM(d.quantity), 0) 
                AS reserved_qty
                FROM rsv_details d
                JOIN reservations r 
                ON r.reservation_id = d.reservation_id
                WHERE d.inventory_id = :inv_id
                AND NOT (r.end_date < :start_date OR r.start_date > :end_date)
                AND r.order_status <> 'Canceled' 
                FOR UPDATE");

        $overLapStmt->execute([
            ':inv_id'     => $inventoryId,
            ':start_date' => $startDate,
            ':end_date'   => $endDate
            ]);

        //Calculate reserved amount
        return (int)$overLapStmt->fetchColumn();
    }
    public function updateItemCid(int $id): ?int
    {
        //Lock reservation row; get its customer_id
        $lock = $this->conn->prepare("SELECT customer_id
                FROM reservations
                WHERE reservation_id = :id
                FOR UPDATE");
        $lock->bindValue(':id',(int)$id, PDO::PARAM_INT);
        $lock->execute();
        $cid = $lock->fetchColumn();
        return $cid !== false ? (int)$cid : null;
    }

    public function populateItemsList()
    {
        $q = $this->conn->query("SELECT product_name FROM inventory ORDER BY product_name");
        return $q->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getAll() {
        $stmt = $this->conn->query("SELECT *
                                    FROM inventory");
        $inventory = $stmt->fetchAll();
        return $inventory ?: [];
    }
}
    /* Delete an inventory item (and related rsv_details) by product_name.
     * Blocks delete if the item is used in any non-canceled reservations.
     * @throws Exception on not found or in-use item
     * @return int number of inventory rows deleted (0 or 1)
     s*/
    public function deleteByProductName(string $productName): int
    {
        try {
            $this->conn->beginTransaction();

            // 1) Find the inventory row
            $stmt = $this->conn->prepare(
                "SELECT inventory_id, product_name
                 FROM inventory
                 WHERE product_name = :name
                 LIMIT 1"
            );
            $stmt->execute([':name' => $productName]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                throw new Exception("Item '{$productName}' not found in inventory.");
            }

            $inventoryId = (int)$row['inventory_id'];

            // 2) Check if the item is used in any non-canceled reservations
            $check = $this->conn->prepare(
                "SELECT COUNT(*)
                 FROM rsv_details d
                 JOIN reservations r ON r.reservation_id = d.reservation_id
                 WHERE d.inventory_id = :inv_id
                   AND r.order_status <> 'Canceled'"
            );
            $check->execute([':inv_id' => $inventoryId]);
            $inUse = (int)$check->fetchColumn();

            if ($inUse > 0) {
                throw new Exception(
                    "Cannot delete '{$productName}' because it is used in active reservations."
                );
            }

            // 3) Delete any rsv_details for this item (for completed/canceled reservations)
            $delDetails = $this->conn->prepare(
                "DELETE FROM rsv_details WHERE inventory_id = :inv_id"
            );
            $delDetails->execute([':inv_id' => $inventoryId]);

            // 4) Delete the inventory row
            $delInv = $this->conn->prepare(
                "DELETE FROM inventory WHERE inventory_id = :inv_id"
            );
            $delInv->execute([':inv_id' => $inventoryId]);

            $this->conn->commit();

            return $delInv->rowCount(); // should be 1
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }
?>