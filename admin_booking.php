<?php
require_once 'db/connect.php';

/* ============================================
   BOOKING MANAGER CLASS (REUSABLE / OOP)
============================================ */
class BookingManager
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /* Fetch all bookings with filters */
    public function getBookings($search = "", $status = "all", $dateFilter = "all")
    {
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

    /* Change Booking Status */
    public function updateBookingStatus($id, $newStatus)
    {
        $stmt = $this->pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        return $stmt->execute([$newStatus, $id]);
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

    // Refresh page after updating status
    header("Location: admin_booking.php");
    exit;
}


/* ============================================
   FETCH BOOKINGS WITH FILTERS
============================================ */
$search = $_GET['search'] ?? "";
$status = $_GET['status'] ?? "all";
$dateFilter = $_GET['date'] ?? "all";

$bookings = $manager->getBookings($search, $status, $dateFilter);

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

                        <input type="text" name="search" placeholder="Search..."
                            value="<?= htmlspecialchars($search) ?>">

                        <select name="status">
                            <option value="all" <?= $status === "all" ? "selected" : "" ?>>All Status</option>
                            <option value="pending" <?= $status === "pending" ? "selected" : "" ?>>Pending</option>
                            <option value="confirmed" <?= $status === "confirmed" ? "selected" : "" ?>>Confirmed</option>
                            <option value="cancelled" <?= $status === "cancelled" ? "selected" : "" ?>>Cancelled</option>
                        </select>

                        <select name="date">
                            <option value="all" <?= $dateFilter === "all" ? "selected" : "" ?>>All Dates</option>
                            <option value="today" <?= $dateFilter === "today" ? "selected" : "" ?>>Today</option>
                            <option value="upcoming" <?= $dateFilter === "upcoming" ? "selected" : "" ?>>Upcoming</option>
                            <option value="past" <?= $dateFilter === "past" ? "selected" : "" ?>>Past</option>
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

                        <tbody>
                            <?php if (empty($bookings)): ?>
                                <tr>
                                    <td colspan="6" class="no-results">No bookings found.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($bookings as $b): ?>
                                <tr>
                                    <td><?= htmlspecialchars($b['name']) ?></td>

                                    <td><?= date("M d, Y", strtotime($b['reservation_date'])) ?></td>

                                    <td>
                                        <?= date("h:i A", strtotime($b['start_time'])) ?>
                                        -
                                        <?= date("h:i A", strtotime($b['end_time'])) ?>
                                    </td>

                                    <td><?= $b['type'] === 'Study' ? "Study Room" : "Gathering Room" ?></td>

                                    <td>
                                        <p class="status-pill <?= $b['status'] ?>">
                                            <?= ucfirst($b['status']) ?>
                                        </p>
                                    </td>

                                    <td class="actions-td">
                                        <!-- VIEW (optional modal later) -->
                                        <button class="btn-view">View</button>

                                        <!-- CONFIRM -->
                                        <?php if ($b['status'] === "pending"): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                                <input type="hidden" name="action" value="confirm">
                                                <button class="btn-confirm">Confirm</button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- CANCEL -->
                                        <?php if ($b['status'] !== "cancelled"): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                                <input type="hidden" name="action" value="cancel">
                                                <button class="btn-cancel">Cancel</button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- REVERT BUTTON for confirmed or cancelled -->
                                        <?php if ($b['status'] === "confirmed" || $b['status'] === "cancelled"): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                                <input type="hidden" name="action" value="revert">
                                                <button class="btn-revert">Revert</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>


                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>

            </section>
        </main>
    </div>
</body>

</html>