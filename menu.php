<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Our Menu | Bat Cave Café</title>
  <link rel="stylesheet" href="/assets/css/globalStyles.css">
  <link rel="stylesheet" href="/assets/css/menu.css">
</head>
<body>
  <!-- 🔹 Navigation Bar -->
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
        <li><a href="menu.php" class="active">Menu</a></li>
        <li><a href="booking.php">Book Now</a></li>
      </ul>

      <button class="theme-toggle" id="theme-toggle" aria-label="Toggle theme">
        <span class="moon"><img src="/assets/images/icons/night.svg"></span>
        <span class="sun"><img src="/assets/images/icons/light.svg"></span>
      </button>

      <button class="hamburger" id="hamburger">
        <img src="assets/images/icons/burger-bar.png" alt="Menu">
      </button>
    </div>
  </nav>

  <!-- 🔹 Page Header -->
  <header class="menu-header">
    <h2 class="page-title">Our Menu</h2>
    <p class="page-subtitle">Fuel your late-night study sessions ☕</p>
  </header>

  <!-- 🔹 Menu Section -->
  <main class="menu-container">
    
    <!-- Drinks Section -->
    <section class="menu-section">
      <h3 class="menu-category">Drinks</h3>
      <div class="menu-grid">
        <div class="menu-card">
          <img src="assets/images/menu/iced-latte.jpg" alt="Iced Latte" loading="lazy">
          <h4>Iced Latte</h4>
          <p>₱90</p>
        </div>
        <div class="menu-card">
          <img src="assets/images/menu/special-bat-brew.jpg" alt="Special Bat Brew" loading="lazy">
          <h4>Special Bat Brew</h4>
          <p>₱110</p>
        </div>
        <div class="menu-card">
          <img src="assets/images/menu/midnight-mocha.jpg" alt="Midnight Mocha" loading="lazy">
          <h4>Midnight Mocha</h4>
          <p>₱120</p>
        </div>
        <div class="menu-card">
          <img src="assets/images/menu/caramel-shadow-latte.jpg" alt="Caramel Shadow Latte" loading="lazy">
          <h4>Caramel Shadow Latte</h4>
          <p>₱120</p>
        </div>
        <div class="menu-card">
          <img src="assets/images/menu/espresso.jpg" alt="Espresso" loading="lazy">
          <h4>Espresso</h4>
          <p>₱120</p>
        </div>
        <div class="menu-card">
          <img src="assets/images/menu/green-tea-milkshake-with-whipped-cream.jpg" alt="Green Tea Milkshake" loading="lazy">
          <h4>Green Tea Milkshake</h4>
          <p>₱120</p>
        </div>
      </div>
    </section>

        <!-- Food Section -->
    <section class="menu-section">
      <h3 class="menu-category">Food</h3>
      <div class="menu-grid">
        <div class="menu-card">
          <img src="assets/images/menu/red-velvet-muffin.jpg" alt="Red Velvet Muffin" loading="lazy">
          <h4>Red Velvet Muffin</h4>
          <p>₱120</p>
        </div>
        <div class="menu-card">
          <img src="assets/images/menu/nachos-grande.jpg" alt="Nachos Grande" loading="lazy">
          <h4>Nachos Grande</h4>
          <p>₱150</p>
        </div>
        <div class="menu-card">
          <img src="assets/images/menu/garlic-bread.jpg" alt="Garlic Bread" loading="lazy">
          <h4>Garlic Bread</h4>
          <p>₱100</p>
        </div>
        <div class="menu-card">
          <img src="assets/images/menu/chocolate-cave-croissant.jpg" alt="Chocolate Cave Croissant" loading="lazy">
          <h4>Chocolate Cave Croissant</h4>
          <p>₱100</p>
        </div>
        <div class="menu-card">
          <img src="assets/images/menu/cheese-sticks.jpg" alt="Cheese Sticks" loading="lazy">
          <h4>Cheese Sticks</h4>
          <p>₱100</p>
        </div>
        <div class="menu-card">
          <img src="assets/images/menu/special-night-scone.jpg" alt="Special Night Cone" loading="lazy">
          <h4>Special Night Cone</h4>
          <p>₱100</p>
        </div>
      </div>
    </section>

    <button class="cta-book-room" id="book-room-btn">Book A Room Now</button>
  </main>

<!-- 🔹 Footer -->
<footer class="footer">
  <div class="footer-column">
    <div class="footer-logo">
      <img src="assets/images/batcavecafe.png" alt="Bat Cave Café Logo" />
      <h4>Bat Cave Café</h4>
    </div>
    <p class="footer-desc">
      Your premier night-time study spot near BSU Malvar Campus.
    </p>
  </div>

  <div class="footer-column">
    <h4>Quick Links</h4>
    <ul>
      <li><a href="index.php">Home</a></li>
      <li><a href="booking.php">Book Now</a></li>
      <li><a href="menu.php">Menu</a></li>
    </ul>
  </div>

  <div class="footer-column">
    <h4>Support</h4>
    <ul>
      <li><a href="#">FAQ</a></li>
      <li><a href="#">Terms of Service</a></li>
      <li><a href="#">Privacy Policy</a></li>
    </ul>
  </div>

  <div class="footer-column">
    <h4>Connect with Us</h4>
    <div class="social-links">
      <a href="#"><img src="assets/images/icons/facebook-social.png" alt="Facebook"></a>
      <a href="#"><img src="assets/images/icons/instagram-social.png" alt="Instagram"></a>
      <a href="#"><img src="assets/images/icons/twitter-social.png" alt="Twitter"></a>
    </div>
    <div class="owner-info">
      <p><span class="icon">📍</span>Location: Near BSU Malvar Campus</p>
      <p><span class="icon">🚹</span>Owner: Nina Sy</p>
    </div>
  </div>
</footer>
  <script src="/assets/js/theme-toggle.js"></script>
  <script src="/assets/js/navbar.js"></script>
  <script  src="assets/js/script.js"></script>
</body>
</html>
