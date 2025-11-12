document.addEventListener("DOMContentLoaded", () => {
  // Create mobile nav modal dynamically
  const mobileNavHTML = `
    <div class="mobile-nav-modal" id="mobile-nav">
      <button class="close-btn" id="close-nav">✖</button>
      <ul class="mobile-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="menu.php">Menu</a></li>
        <li><a href="booking.php" class="active">Book Now</a></li>
      </ul>
      <div class="mobile-theme">
        <button class="theme-toggle" id="theme-toggle-mobile" aria-label="Toggle theme">
          <span class="sun">☀️</span>
          <span class="moon">🌙</span>
        </button>
      </div>
    </div>
  `;

  // Insert modal after the navbar
  const navbar = document.querySelector(".navbar");
  navbar.insertAdjacentHTML("afterend", mobileNavHTML);

  // Setup interactivity
  const hamburger = document.getElementById("hamburger");
  const mobileNav = document.getElementById("mobile-nav");
  const closeNav = document.getElementById("close-nav");

  hamburger.addEventListener("click", () => {
    mobileNav.classList.add("active");
  });

  closeNav.addEventListener("click", () => {
    mobileNav.classList.remove("active");
  });

  // Optional: close when clicking outside
  window.addEventListener("click", (e) => {
    if (e.target === mobileNav) mobileNav.classList.remove("active");
  });
});


