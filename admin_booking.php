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
    $filters = [
        'search' => $_GET['search'] ?? '',
        'status' => $_GET['status'] ?? 'all',
        'date' => $_GET['date'] ?? 'all'
    ];
    $bookings = $manager->getAllBookings($filters);
    echo json_encode($bookings);
    exit;
}


/* ============================================
   FETCH BOOKINGS WITH FILTERS
============================================ */
$filters = [
    'search' => $_GET['search'] ?? '',
    'status' => $_GET['status'] ?? 'all',
    'date'   => $_GET['date'] ?? 'all'
];

$bookings = $manager->getAllBookings($filters);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Café Admin Dashboard</title>
    <link rel="stylesheet" href="/assets/css/admin-booking.css">
    <link rel="stylesheet" href="/assets/css/adminGlobalStyles.css">
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
                    <h1>Booking Management</h1>
                    <p>Manage, view, and update all customer reservations</p>
                </div>
            </header>

            <!-- 🔎 CONTROLS -->
            <section class="booking-management">
                <div class="booking-controls">

                    <form method="GET" class="filters-form">

                        <input type="text" id="searchBooking" placeholder="Search by name, date, or room...">

                        <select id="filterStatus" name="status">
                            <option value="all" <?= $filters === "all" ? "selected" : "" ?>>All Status</option>
                            <option value="pending" <?= $filters === "pending" ? "selected" : "" ?>>Pending</option>
                            <option value="confirmed" <?= $filters === "confirmed" ? "selected" : "" ?>>Confirmed</option>
                            <option value="cancelled" <?= $filters === "cancelled" ? "selected" : "" ?>>Cancelled</option>
                        </select>

                        <select id="filterDate" name="date">
                            <option value="all" <?= $filters === "all" ? "selected" : "" ?>>All Dates</option>
                            <option value="today" <?= $filters === "today" ? "selected" : "" ?>>Today</option>
                            <option value="upcoming" <?= $filters === "upcoming" ? "selected" : "" ?>>Upcoming</option>
                            <option value="past" <?= $filters === "past" ? "selected" : "" ?>>Past</option>
                        </select>


                        <button type="submit" class="filter-btn">Apply Filters</button>
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

<script>
    document.addEventListener("DOMContentLoaded", () => {

        const searchInput = document.getElementById("searchBooking");
        const statusFilter = document.getElementById("filterStatus");
        const dateFilter = document.getElementById("filterDate");
        const tableBody = document.getElementById("booking-table-body");

        const fetchBookings = () => {
            const search = searchInput.value;
            const status = statusFilter.value;
            const date = dateFilter.value;

            const params = new URLSearchParams({
                ajax: 1,
                search,
                status,
                date
            });
            fetch("admin_booking.php?" + params.toString())
                .then(res => res.json())
                .then(data => {
                    tableBody.innerHTML = "";

                    if (data.length === 0) {
                        tableBody.innerHTML = `<tr><td colspan="6" style="text-align:center;">No bookings found.</td></tr>`;
                        return;
                    }

                    data.forEach(b => {
                        const statusClass = b.status === "pending" ? "pending" : (b.status === "confirmed" ? "confirmed" : "cancelled");

                        const row = document.createElement("tr");
                        row.innerHTML = `
                        <td>${b.name}</td>
                        <td>${new Date(b.reservation_date).toLocaleDateString()}</td>
                        <td>${b.start_time} - ${b.end_time}</td>
                        <td>${b.type}</td>
                        <td><p class="status-pill ${statusClass}">${b.status.charAt(0).toUpperCase() + b.status.slice(1)}</p></td>
                        <td class="actions-td">
                            <button class="btn-view">View</button>
                            ${b.status === "pending" ? `<form method="POST" style="display:inline;">
                                <input type="hidden" name="booking_id" value="${b.id}">
                                <input type="hidden" name="action" value="confirm">
                                <button class="btn-confirm">Confirm</button>
                            </form>` : ""}
                            ${b.status !== "cancelled" ? `<form method="POST" style="display:inline;">
                                <input type="hidden" name="booking_id" value="${b.id}">
                                <input type="hidden" name="action" value="cancel">
                                <button class="btn-cancel">Cancel</button>
                            </form>` : ""}
                            ${(b.status === "confirmed" || b.status === "cancelled") ? `<form method="POST" style="display:inline;">
                                <input type="hidden" name="booking_id" value="${b.id}">
                                <input type="hidden" name="action" value="revert">
                                <button class="btn-revert">Revert</button>
                            </form>` : ""}
                        </td>
                    `;
                        tableBody.appendChild(row);
                    });
                });
        };

        // Event listeners
        searchInput.addEventListener("input", fetchBookings);
        statusFilter.addEventListener("change", fetchBookings);
        dateFilter.addEventListener("change", fetchBookings);

        // Load bookings initially
        fetchBookings();
    });
</script>