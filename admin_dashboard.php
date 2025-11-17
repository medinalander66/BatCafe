<?php
require_once "db/connect.php";

/* ============================================================
   DASHBOARD MANAGER CLASS  (Reusable, OOP, Clean)
============================================================ */
class DashboardManager
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /* 📌 Total number of bookings */
    public function getTotalBookings()
    {
        return $this->fetchValue("SELECT COUNT(*) FROM bookings");
    }

    /* 📌 Number of upcoming bookings */
    public function getUpcomingBookings()
    {
        return $this->fetchValue("SELECT COUNT(*) FROM bookings WHERE reservation_date > CURDATE()");
    }

    /* 📌 Today's bookings */
    public function getTodaysBookings()
    {
        return $this->fetchValue("SELECT COUNT(*) FROM bookings WHERE reservation_date = CURDATE()");
    }

    /* 📌 Total revenue (assumes column: total_price) */
    public function getTotalRevenue()
    {
        return $this->fetchValue("SELECT SUM(total_price) FROM bookings");
    }

    /* 📌 Equipment revenue (assumes column: equipment_price) */
    public function getEquipmentRevenue()
    {
        return $this->fetchValue("SELECT SUM(equipment_price) FROM bookings");
    }

    /* 📌 Most popular room */
    public function getMostPopularRoom()
    {
        $stmt = $this->pdo->query("
            SELECT room_type, COUNT(*) AS count
            FROM bookings
            GROUP BY room_type
            ORDER BY count DESC
            LIMIT 1
        ");

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['room_type'] : "No Data";
    }

    /* 📌 Helper: fetch single value */
    private function fetchValue($sql)
    {
        $stmt = $this->pdo->query($sql);
        $value = $stmt->fetchColumn();
        return $value ? $value : 0;
    }

    /* 📌 Return all dashboard data */
    public function getDashboardData()
    {
        return [
            "totalBookings"      => $this->getTotalBookings(),
            "upcomingBookings"   => $this->getUpcomingBookings(),
            "todaysBookings"     => $this->getTodaysBookings(),
            "totalRevenue"       => $this->getTotalRevenue(),
            "equipmentRevenue"   => $this->getEquipmentRevenue(),
            "popularRoom"        => $this->getMostPopularRoom(),
        ];
    }
}

/* ============================================================
   AJAX REQUEST HANDLER
============================================================ */
$dashboard = new DashboardManager($pdo);

if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header("Content-Type: application/json");
    echo json_encode($dashboard->getDashboardData());
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Café Admin Dashboard</title>
    <link rel="stylesheet" href="/assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="/assets/css/adminGlobalStyles.css">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" as="style"
          onload="this.onload=null;this.rel='stylesheet'">
</head>

<body>

<div class="admin-container">

    <!-- SIDEBAR -->
    <?php include 'asideNavbar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="main-content">

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


        <!-- DASHBOARD CARDS -->
        <section class="dashboard-cards">

            <div class="card" id="card-total-bookings">
                <div class="card-info">
                    <p>Total Bookings</p>
                    <h2 class="card-h2" id="totalBookings">0</h2>
                </div>
                <i class="fa-solid fa-calendar card-icon"></i>
            </div>

            <div class="card" id="card-upcoming-bookings">
                <div class="card-info" >
                    <p>Upcoming Bookings</p>
                    <h2 class="card-h2" id="upcomingBookings">0</h2>
                </div>
                <i class="fa-solid fa-clock card-icon"></i>
            </div>

            <div class="card" id="card-total-revenue" >
                <div class="card-info" >
                    <p>Total Revenue</p>
                    <h2 class="card-h2" id="totalRevenue">₱0</h2>
                </div>
                <i class="fa-solid fa-peso-sign card-icon"></i>
            </div>

            <div class="card" id="card-todays-bookings">
                <div class="card-info">
                    <p>Today's Bookings</p>
                    <h2 class="card-h2" id="todaysBookings">0</h2>
                </div>
                <i class="fa-solid fa-calendar-day card-icon"></i>
            </div>

            <div class="card" id="card-most-pplr-room">
                <div class="card-info">
                    <p>Most Popular Room</p>
                    <h2 class="card-h2" id="popularRoom">None</h2>
                </div>
                <i class="fa-solid fa-star card-icon"></i>
            </div>

            <div class="card" id="card-equipment-revenue">
                <div class="card-info">
                    <p>Equipment Revenue</p>
                    <h2 class="card-h2" id="equipmentRevenue">₱0</h2>
                </div>
                <i class="fa-solid fa-plug card-icon"></i>
            </div>
        </section>

        <!-- KEY STATISTICS -->
        <section class="key-stats">
            <h2>Revenue by Room Type</h2>
            <div class="stats-container">
                <p>Chart goes here...</p>
            </div>
        </section>

    </main>
</div>


<script src="/assets/js/theme-toggle.js"></script>

<!-- ============================================================
     AJAX SCRIPT — AUTO REFRESH DASHBOARD
============================================================ -->
<script>
    function loadDashboardData() {
        fetch("admin_dashboard.php?ajax=1")
            .then(res => res.json())
            .then(data => {
                document.getElementById("totalBookings").textContent = data.totalBookings;
                document.getElementById("upcomingBookings").textContent = data.upcomingBookings;
                document.getElementById("todaysBookings").textContent = data.todaysBookings;
                document.getElementById("totalRevenue").textContent = "₱" + data.totalRevenue;
                document.getElementById("equipmentRevenue").textContent = "₱" + data.equipmentRevenue;
                document.getElementById("popularRoom").textContent = data.popularRoom;
            });
    }

    // Load immediately
    loadDashboardData();

    // Refresh every 10 seconds automatically
    setInterval(loadDashboardData, 10000);
</script>

</body>
</html>
