<?php
/* ============================================
   BOOKING MANAGER CLASS (REUSABLE / OOP)
============================================ */
require_once "Booking.php";

class BookingManager {
    private $pdo;
    private $conn;


    public function __construct($pdo, $db) {
        $this->pdo = $pdo;
         $this->conn = $db;
    }

    /* Fetch all bookings with filters */
    public function getBookings($search = "", $status = "all", $dateFilter = "all"){
        $query = "SELECT * FROM bookings WHERE 1";
        $params = [];

        // Search Filter
        if (!empty($search)) {
            $query .= " AND (name LIKE ? OR reservation_date LIKE ? OR type LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Status Filter
        if ($status !== "all") {
            $query .= " AND status = ?";
            $params[] = $status;
        }

        // Date Filter
        if ($dateFilter === "today") {
            $query .= " AND reservation_date = CURDATE()";
        } elseif ($dateFilter === "upcoming") {
            $query .= " AND reservation_date > CURDATE()";
        } elseif ($dateFilter === "past") {
            $query .= " AND reservation_date < CURDATE()";
        }

        $query .= " ORDER BY reservation_date ASC, start_time ASC";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

       public function getAllBookings() {
        $query = "SELECT * FROM bookings ORDER BY date ASC";
        $result = $this->conn->query($query);

        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = new Booking(
                $row['id'],
                $row['customer'],
                $row['date'],
                $row['time'],
                $row['room'],
                $row['status']
            );
        }
        return $bookings;
    }

    public function updateStatus($id, $status) {
        $stmt = $this->conn->prepare("UPDATE bookings SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    /* Change Booking Status */
    public function updateBookingStatus($id, $newStatus){
        $stmt = $this->pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        return $stmt->execute([$newStatus, $id]);
    }
}
 ?>