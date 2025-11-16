<!DOCTYPE html>
<html lang="en">

<head>
    <script src="/assets/js/script.js"></script>
    <meta charset="UTF-8">
    <title>Café Admin Dashboard</title>
    <link rel="stylesheet" href="/assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="/assets/css/adminGlobalStyles.css">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
</head>

<body>

    <div class="admin-container">

        <!-- SIDEBAR -->
        <!-- <aside class="sidebar">
        <div class="sidebar-logo">
            <img src="/assets/images/batcavecafe.png" alt="Logo">
            <div class="logo-text">
                <h2>BatCave Café</h2>
                <p>Admin Panel</p>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="#" class="nav-item">
                <i class="fa-solid fa-chart-line"></i>
                <span>Dashboard</span>
            </a>

            <a href="#" class="nav-item">
                <i class="fa-solid fa-calendar-check"></i>
                <span>Booking Management</span>
            </a>

            <a href="#" class="nav-item">
                <i class="fa-solid fa-gear"></i>
                <span>Settings</span>
            </a>

            <a href="../index.php" class="nav-item exit">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Exit</span>
            </a>
        </nav>
    </aside> -->
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
                        <h2 class="card-h2" id="card-total-bks-h2">5</h2>
                    </div>
                    <i class="fa-solid fa-calendar card-icon"></i>
                </div>

                <div class="card" id="card-upcoming-bookings">
                    <div class="card-info">
                        <p>Upcoming Bookings</p>
                        <h2 class="card-h2" id="card-upcoming-bks-h2" >5</h2>
                    </div>
                    <i class="fa-solid fa-clock card-icon"></i>
                </div>

                <div class="card" id="card-total-revenue">
                    <div class="card-info">
                        <p>Total Revenue</p>
                        <h2 class="card-h2" id="card-total-rve-h2">5</h2>
                    </div>
                    <i class="fa-solid fa-peso-sign card-icon"></i>
                </div>

                <div class="card" id="card-todays-bookings">
                    <div class="card-info">
                        <p>Today's Bookings</p>
                        <h2 class="card-h2" id="card-todays-bks-h2">5</h2>
                    </div>
                    <i class="fa-solid fa-calendar-day card-icon"></i>
                </div>

                <div class="card" id="card-most-pplr-room">
                    <div class="card-info">
                        <p>Most Popular Room Type</p>
                        <h2 class="card-h2" id="card-total-ppl-h2">5</h2>
                    </div>
                    <i class="fa-solid fa-star card-icon"></i>
                </div>

                <div class="card" id="card-equipment-revenue">
                    <div class="card-info">
                        <p>Equipment Revenue</p>
                        <h2 class="card-h2" id="card-equipment-rve-h2">5</h2>
                    </div>
                    <i class="fa-solid fa-plug card-icon"></i>
                </div>

            </section>

            <!-- KEY STATISTICS -->
            <section class="key-stats">
                <h2>Revenue by Room Type</h2>
                <div class="stats-container">
                    <!-- Placeholder (charts later) -->
                    <p>Chart goes here...</p>
                </div>
            </section>

        </main>
    </div>

    <script src="/assets/js/theme-toggle.js"></script>

</body>

</html>