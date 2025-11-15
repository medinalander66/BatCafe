document.addEventListener("DOMContentLoaded", () => {

  // ---------- FOOTER TEMPLATE ----------
  const footerHTML = `
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
        <ul class="footer-links">
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
  `;

  // Insert footer at the end of the body
  document.body.insertAdjacentHTML("beforeend", footerHTML);


  // ---------- ACTIVE LINK HIGHLIGHT ----------
  const currentPage = window.location.pathname.split("/").pop();
  const footerLinks = document.querySelectorAll(".footer-links a");

  footerLinks.forEach(link => {
    if (link.getAttribute("href") === currentPage) {
      link.classList.add("active");
    }
  });

});
