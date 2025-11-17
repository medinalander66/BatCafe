document.addEventListener("DOMContentLoaded", () => {
  const imgs = document.querySelectorAll("img.blur-load");

  imgs.forEach(img => {
    const highResSrc = img.dataset.src;
    if (!highResSrc) return;

    // Start loading high-res silently
    const highRes = new Image();
    highRes.src = highResSrc;

    highRes.onload = () => {
      // Swap in high-res
      img.src = highResSrc;
      img.classList.add("loaded");
    };
  });
});
