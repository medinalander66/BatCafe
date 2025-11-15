document.addEventListener("DOMContentLoaded", () => { 
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

