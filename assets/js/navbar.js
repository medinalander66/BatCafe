document.addEventListener("DOMContentLoaded", () => {

    /* -------------------------------
        Inject NAVBAR HTML into page
    --------------------------------*/
    const navbarHTML = `
      <nav class="navbar">
        <div class="nav-left">
          <img src="assets/images/batcavecafe.png" alt="Bat Cave Café Logo" class="logo">
          <div class="brand">
            <h1>Bat Cave Café</h1>
            <p>Where Study Meets Serenity</p>
          </div>
        </div>

        <div class="nav-right">
          <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="menu.php">Menu</a></li>
            <li><a href="booking.php">Book Now</a></li>
          </ul>

          <button class="theme-toggle" id="theme-toggle" aria-label="Toggle theme">
            <span class="moon"><img src="/assets/images/icons/night.svg" alt="Dark Mode"></span>
            <span class="sun"><img src="/assets/images/icons/light.svg" alt="Light Mode"></span>
          </button>

          <button class="hamburger" id="hamburger">
            <img src="assets/images/icons/burger-bar.png" alt="Menu">
          </button>
        </div>
      </nav>
    `;

    // Insert navbar at the top of <body>
    document.body.insertAdjacentHTML("afterbegin", navbarHTML);

    const navbar = document.querySelector(".navbar");
    const desktopLinks = document.querySelectorAll(".nav-links a");

    /* -----------------------------------------
       Build MOBILE NAVIGATION dynamically
    ------------------------------------------*/
    let mobileLinksHTML = "";
    desktopLinks.forEach(link => {
        const href = link.getAttribute("href");
        const text = link.textContent;
        mobileLinksHTML += `<li><a href="${href}">${text}</a></li>`;
    });

    const mobileNavHTML = `
      <div class="mobile-nav-modal" id="mobile-nav">
        <div class="mobile-nav-content">
          <button class="close-btn" id="close-nav">✖</button>
          <ul class="mobile-links">${mobileLinksHTML}</ul>
          <div class="mobile-theme">
            <button class="theme-toggle" id="theme-toggle-mobile" aria-label="Toggle theme">
              <span class="sun">☀️</span>
              <span class="moon">🌙</span>
            </button>
          </div>
        </div>
      </div>
    `;

    navbar.insertAdjacentHTML("afterend", mobileNavHTML);

    const mobileNav = document.getElementById("mobile-nav");
    const hamburger = document.getElementById("hamburger");
    const closeNav = document.getElementById("close-nav");

    /* -------------------------------
        Mobile nav show/hide
    --------------------------------*/
    hamburger.addEventListener("click", () => mobileNav.classList.add("active"));
    closeNav.addEventListener("click", () => mobileNav.classList.remove("active"));

    window.addEventListener("click", e => {
        if (e.target === mobileNav) mobileNav.classList.remove("active");
    });

    /* -------------------------------
        Highlight ACTIVE PAGE
    --------------------------------*/
    const currentPage = window.location.pathname.split("/").pop();

    desktopLinks.forEach(link => {
        link.classList.toggle("active", link.getAttribute("href") === currentPage);
    });

    const mobileLinks = document.querySelectorAll(".mobile-links a");
    mobileLinks.forEach(link => {
        link.classList.toggle("active", link.getAttribute("href") === currentPage);
    });

    /* -------------------------------
        THEME TOGGLE (Mobile + Desktop)
        Now applied on <html> to prevent flash
    --------------------------------*/
    const themeToggleDesktop = document.getElementById("theme-toggle");
    const themeToggleMobile = document.getElementById("theme-toggle-mobile");

    const applyTheme = (mode) => {
        const root = document.documentElement;
        if (mode === "dark") {
            root.classList.add("dark-mode");
            localStorage.setItem("theme", "dark");
        } else {
            root.classList.remove("dark-mode");
            localStorage.setItem("theme", "light");
        }
    };

    const toggleTheme = () => {
        const isDark = document.documentElement.classList.toggle("dark-mode");
        localStorage.setItem("theme", isDark ? "dark" : "light");
    };

    if (themeToggleDesktop) themeToggleDesktop.addEventListener("click", toggleTheme);
    if (themeToggleMobile) themeToggleMobile.addEventListener("click", toggleTheme);

    // Ensure theme is correctly applied after dynamic HTML loads
    const savedTheme = localStorage.getItem("theme");
    if (savedTheme === "dark") {
        document.documentElement.classList.add("dark-mode");
    } else {
        document.documentElement.classList.remove("dark-mode");
    }

});
