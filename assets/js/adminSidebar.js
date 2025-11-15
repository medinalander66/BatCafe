// admin-sidebar.js
document.addEventListener("DOMContentLoaded", () => {
  // 1️⃣ Define sidebar HTML
  const sidebarHTML = `
    <aside class="sidebar">
      <div class="sidebar-logo">
        <img src="/assets/images/batcavecafe.png" alt="Logo">
        <div class="logo-text">
          <h2>BatCave Café</h2>
          <p>Admin Panel</p>
        </div>
      </div>

      <nav class="sidebar-nav">
        <a href="admin_dashboard.php" class="nav-item">
          <i class="fa-solid fa-chart-line"></i>
          <span>Dashboard</span>
        </a>

        <a href="booking_management.php" class="nav-item">
          <i class="fa-solid fa-calendar-check"></i>
          <span>Booking Management</span>
        </a>

        <a href="product_management.php" class="nav-item">
          <i class="fa-solid fa-box"></i>
          <span>Product Management</span>
        </a>

        <a href="settings.php" class="nav-item">
          <i class="fa-solid fa-gear"></i>
          <span>Settings</span>
        </a>

        <a href="../index.php" class="nav-item exit">
          <i class="fa-solid fa-right-from-bracket"></i>
          <span>Exit</span>
        </a>
      </nav>
    </aside>
  `;

  // 2️⃣ Inject sidebar at top of body
  document.body.insertAdjacentHTML("afterbegin", sidebarHTML);

  // 3️⃣ Highlight active link
  const sidebarLinks = document.querySelectorAll(".sidebar .nav-item");
  const currentPage = window.location.pathname.split("/").pop();

  sidebarLinks.forEach(link => {
    const linkPage = link.getAttribute("href").split("/").pop();
    if (linkPage === currentPage) {
      link.classList.add("active");
    }
  });
});
