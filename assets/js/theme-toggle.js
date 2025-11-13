const themeToggle = document.getElementById('theme-toggle');
const themeToggleMobile = document.getElementById('theme-toggle-mobile');
const body = document.body;

// 🛑 Disable transitions before applying theme
body.classList.add('no-transition');

// ⚡ Immediately apply saved theme before paint
const savedTheme = localStorage.getItem('theme');
if (savedTheme === 'dark') {
  body.classList.add('dark-mode');
} else {
  body.classList.remove('dark-mode');
}

// ✅ Re-enable transitions after the page fully loads
window.addEventListener('load', () => {
  setTimeout(() => body.classList.remove('no-transition'), 100);
});

// 🌗 Theme toggle for both desktop and mobile
document.addEventListener('click', (e) => {
  if (e.target.closest('#theme-toggle') || e.target.closest('#theme-toggle-mobile')) {
    // Enable transition for smooth effect
    body.classList.remove('no-transition');
    body.classList.toggle('dark-mode');

    const currentTheme = body.classList.contains('dark-mode') ? 'dark' : 'light';
    localStorage.setItem('theme', currentTheme);
  }
});
