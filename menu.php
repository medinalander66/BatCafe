<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Our Menu | Bat Cave Café</title>
  <script src="assets/js/script.js"></script>
    <script src="assets/js/menu.js" defer></script>
  <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" as="style"
    onload="this.onload=null;this.rel='stylesheet'">
  <link rel="stylesheet" href="/assets/css/globalStyles.css">
  <link rel="stylesheet" href="/assets/css/menu.css">
</head>

<body>
  <!-- 🔹 Navigation Bar -->
  <script src="assets/js/navbar.js"></script>

  <!-- 🔹 Page Header -->
  <header class="menu-header">
    <h2 class="page-title">Our Menu</h2>
    <p class="page-subtitle">Fuel your late-night study sessions.</p>
  </header>

  <!-- 🔹 Menu Section -->
  <main class="menu-container">

    <!-- Drinks Section -->
    <!-- Drinks Section -->
    <section class="menu-section">
      <h3 class="menu-category">Drinks</h3>
      <div class="menu-grid">

        <div class="menu-card">
          <div class="img-wrapper">
            <img
              src="assets/images/menu/iced-latte-blur.jpg"
              data-src="assets/images/menu/iced-latte.jpg"
              alt="Iced Latte"
              loading="lazy"
              class="blur-load">
            <p class="price">₱90</p>
          </div>
          <h4>Iced Latte</h4>
        </div>

        <div class="menu-card">
          <div class="img-wrapper">
            <img
              src="assets/images/menu/special-bat-brew-blur.jpg"
              data-src="assets/images/menu/special-bat-brew.jpg"
              alt="Special Bat Brew"
              loading="lazy"
              class="blur-load">
            <p class="price">₱110</p>
          </div>
          <h4>Special Bat Brew</h4>
        </div>

        <div class="menu-card">
          <div class="img-wrapper">
            <img
              src="assets/images/menu/midnight-mocha-blur.jpg"
              data-src="assets/images/menu/midnight-mocha.jpg"
              alt="Midnight Mocha"
              loading="lazy"
              class="blur-load">
            <p class="price">₱120</p>
          </div>
          <h4>Midnight Mocha</h4>
        </div>

        <div class="menu-card">
          <div class="img-wrapper">
            <img
              src="assets/images/menu/caramel-shadow-latte-blur.jpg"
              data-src="assets/images/menu/caramel-shadow-latte.jpg"
              alt="Caramel Shadow Latte"
              loading="lazy"
              class="blur-load">
            <p class="price">₱120</p>
          </div>
          <h4>Caramel Shadow Latte</h4>
        </div>

        <div class="menu-card">
          <div class="img-wrapper">
            <img
              src="assets/images/menu/espresso-blur.jpg"
              data-src="assets/images/menu/espresso.jpg"
              alt="Espresso"
              loading="lazy"
              class="blur-load">
            <p class="price">₱120</p>
          </div>
          <h4>Espresso</h4>
        </div>

        <div class="menu-card">
          <div class="img-wrapper">
            <img
              src="assets/images/menu/green-tea-milkshake-blur.jpg"
              data-src="assets/images/menu/green-tea-milkshake-with-whipped-cream.jpg"
              alt="Green Tea Milkshake"
              loading="lazy"
              class="blur-load">
            <p class="price">₱120</p>
          </div>
          <h4>Green Tea Milkshake</h4>
        </div>

      </div>
    </section>


    <!-- Food Section -->
    <section class="menu-section">
      <h3 class="menu-category">Food</h3>
      <div class="menu-grid">

        <div class="menu-card">
          <div class="img-wrapper">
            <img
              src="assets/images/menu/red-velvet-muffin-blur.jpg"
              data-src="assets/images/menu/red-velvet-muffin.jpg"
              alt="Red Velvet Muffin"
              loading="lazy"
              class="blur-load">
            <p class="price">₱120</p>
          </div>
          <h4>Red Velvet Muffin</h4>
        </div>

        <div class="menu-card">
          <div class="img-wrapper">
            <img
              src="assets/images/menu/nachos-grande-blur.jpg"
              data-src="assets/images/menu/nachos-grande.jpg"
              alt="Nachos Grande"
              loading="lazy"
              class="blur-load">
            <p class="price">₱150</p>
          </div>
          <h4>Nachos Grande</h4>
        </div>

        <div class="menu-card">
          <div class="img-wrapper">
            <img
              src="assets/images/menu/garlic-bread-blur.jpg"
              data-src="assets/images/menu/garlic-bread.jpg"
              alt="Garlic Bread"
              loading="lazy"
              class="blur-load">
            <p class="price">₱100</p>
          </div>
          <h4>Garlic Bread</h4>
        </div>

        <div class="menu-card">
          <div class="img-wrapper">
            <img
              src="assets/images/menu/chocolate-cave-croissant-blur.jpg"
              data-src="assets/images/menu/chocolate-cave-croissant.jpg"
              alt="Chocolate Cave Croissant"
              loading="lazy"
              class="blur-load">
            <p class="price">₱100</p>
          </div>
          <h4>Chocolate Cave Croissant</h4>
        </div>

        <div class="menu-card">
          <div class="img-wrapper">
            <img
              src="assets/images/menu/cheese-sticks-blur.jpg"
              data-src="assets/images/menu/cheese-sticks.jpg"
              alt="Cheese Sticks"
              loading="lazy"
              class="blur-load">
            <p class="price">₱100</p>
          </div>
          <h4>Cheese Sticks</h4>
        </div>

        <div class="menu-card">
          <div class="img-wrapper">
            <img
              src="assets/images/menu/special-night-cone-blur.jpg"
              data-src="assets/images/menu/special-night-scone.jpg"
              alt="Special Night Cone"
              loading="lazy"
              class="blur-load">
            <p class="price">₱100</p>
          </div>
          <h4>Special Night Cone</h4>
        </div>

      </div>
    </section>



    <button class="cta-book-room" id="book-room-btn">Book A Room Now</button>
  </main>

  <script src="assets/js/footer.js" defer></script>
  <script src="/assets/js/theme-toggle.js"></script>
  <script src="assets/js/script.js"></script>
</body>
</html>