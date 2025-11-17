<?php
require_once 'db/connect.php';

/* ============================================
   BOOKING MANAGER CLASS 
============================================ */
class BookingManager
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /* Fetch all bookings with filters */
    public function getAllBookings($filters = [])
    {
        $sql = "SELECT * FROM bookings";
        $conditions = [];
        $params = [];

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $conditions[] = "status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['date']) && $filters['date'] !== 'all') {
            if ($filters['date'] === 'today') {
                $conditions[] = "reservation_date = CURDATE()";
            } elseif ($filters['date'] === 'upcoming') {
                $conditions[] = "reservation_date > CURDATE()";
            } elseif ($filters['date'] === 'past') {
                $conditions[] = "reservation_date < CURDATE()";
            } else {
                $conditions[] = "reservation_date = :date";
                $params[':date'] = $filters['date'];
            }
        }

        if (!empty($filters['search'])) {
            $conditions[] = "(name LIKE :search OR student_id LIKE :search OR type LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY reservation_date DESC, start_time ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* Change Booking Status */
    public function updateBookingStatus($id, $status)
    {
        $stmt = $this->pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
    }
}

/* ============================================
   INITIALIZE BOOKING MANAGER
============================================ */
$manager = new BookingManager($pdo);

/* ============================================
   HANDLE ADMIN ACTIONS (Confirm / Cancel / Revert)
============================================ */
if (isset($_POST['action'])) {
    $id = intval($_POST['booking_id']);
    $action = $_POST['action'];

    if ($action === "confirm") {
        $manager->updateBookingStatus($id, "confirmed");
    } elseif ($action === "cancel") {
        $manager->updateBookingStatus($id, "cancelled");
    } elseif ($action === "revert") {
        $manager->updateBookingStatus($id, "pending");
    }
}

// AJAX request to fetch filtered bookings
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');
    $filters['status'] = [
        'search' => $_GET['search'] ?? '',
        'status' => $_GET['status'] ?? 'all',
        'date' => $_GET['date'] ?? 'all'
    ];
    $bookings = $manager->getAllBookings($filters['status']);
    echo json_encode($bookings);
    exit;
}


/* ============================================
   FETCH BOOKINGS WITH FILTERS
============================================ */
$filters['status'] = [
    'search' => $_GET['search'] ?? '',
    'status' => $_GET['status'] ?? 'all',
    'date'   => $_GET['date'] ?? 'all'
];

$bookings = $manager->getAllBookings($filters['status']);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <script src="/assets/js/script.js"></script>
    <meta charset="UTF-8">
    <title>Café Admin Dashboard</title>
    <link rel="stylesheet" href="/assets/css/admin-booking.css">
    <link rel="stylesheet" href="/assets/css/adminGlobalStyles.css">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
</head>

<body>
    <div class="admin-container">

        <!-- SIDEBAR -->
        <?php include 'asideNavbar.php'; ?>

        <!-- MAIN CONTENT -->
        <main class="main-content">

            <!-- HEADER -->

            <header class="admin-header">
                <div class="header-left">
                    <h1>Dashboard</h1>
                    <p>Overview of your cafe's performance</p>
                </div>

                <div class="quick-info-action-wrapper">
                    <div class="theme-toggle-wrapper">
                        <label>Light Mode</label>
                        <button class="theme-toggle" id="theme-toggle">
                            <span class="moon"><img src="/assets/images/icons/night.svg" alt="Dark Mode"></span>
                            <span class="sun"><img src="/assets/images/icons/light.svg" alt="Light Mode"></span>
                        </button>
                    </div>

                    <div class="user-short-info-wrapper">
                        <span class="user-name">Admin User</span>
                        <img src="/assets/images/icons/user.png" alt="Admin Avatar" class="admin-avatar">
                    </div>
                </div>
            </header>

            <!-- 🔎 CONTROLS -->
            <section class="booking-management">
                <div class="booking-controls">

                    <form method="GET" class="filters-form">

                        <input type="text" id="searchBooking" placeholder="Search by name, date, or room...">

                        <select id="filterStatus" name="status">
                            <option value="all" <?= $filters['status'] === "all" ? "selected" : "" ?>>All Status</option>
                            <option value="pending" <?= $filters['status'] === "pending" ? "selected" : "" ?>>Pending</option>
                            <option value="confirmed" <?= $filters['status'] === "confirmed" ? "selected" : "" ?>>Confirmed</option>
                            <option value="cancelled" <?= $filters['status'] === "cancelled" ? "selected" : "" ?>>Cancelled</option>
                        </select>

                        <select id="filterDate" name="date">
                            <option value="all" <?= $filters['status'] === "all" ? "selected" : "" ?>>All Dates</option>
                            <option value="today" <?= $filters['status'] === "today" ? "selected" : "" ?>>Today</option>
                            <option value="upcoming" <?= $filters['status'] === "upcoming" ? "selected" : "" ?>>Upcoming</option>
                            <option value="past" <?= $filters['status'] === "past" ? "selected" : "" ?>>Past</option>
                        </select>

                    </form>
                </div>

                <!-- 📋 BOOKINGS TABLE -->
                <div class="table-container">
                    <table class="booking-table">
                        <thead>
                            <tr class="table-row">
                                <th id="th-customer">Customer</th>
                                <th id="th-date">Date</th>
                                <th id="th-time">Time</th>
                                <th id="th-room">Room</th>
                                <th id="th-status">Status</th>
                                <th id="th-actions">Actions</th>
                            </tr>
                        </thead>

                        <tbody id="booking-table-body">
                            <!-- Existing rows will be dynamically replaced by JS -->
                        </tbody>

                    </table>
                </div>

            </section>
        </main>
    </div>
</body>

</html>

<script src="assets/js/theme-toggle.js"></script>
<script src="assets/js/admin-booking.js"></script>