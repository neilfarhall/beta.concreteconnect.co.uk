/**
 * Switch between Light and Dark mode
 */

  // Select root element
const root = document.querySelector('html');

// Select the button
const btn = document.querySelector(".js-btn-toggle-theme");
const btnText = document.querySelector(".js-btn-toggle-theme .text");
const btnIcon = document.querySelector(".js-btn-toggle-theme .fas");
// Check for dark mode preference at the OS level
const prefersDarkScheme = window.matchMedia("(prefers-color-scheme: dark)");
// Get the user's theme preference from local storage, if it's available
const currentTheme = localStorage.getItem("theme");

// If the user's preference in localStorage is dark...
if (!currentTheme) {
  prefersDarkScheme.matches ? root.classList.add("themag-dark") : root.classList.remove("themag-dark");
}
else {
  currentTheme === "dark" ? root.classList.add("themag-dark") : root.classList.remove("themag-dark");
}

let isDarkTheme = root.classList.contains("themag-dark");

if (isDarkTheme) {
  btnText.textContent = Drupal.t("Light theme");
  btnIcon.classList.remove('fa-moon');
  btnIcon.classList.add('fa-sun');
}
else {
  btnText.textContent = Drupal.t("Dark theme");
  btnIcon.classList.remove('fa-sun');
  btnIcon.classList.add('fa-moon');
}

// Listen for a click on the button
btn.addEventListener("click", function () {
  isDarkTheme = root.classList.contains("themag-dark");
  const theme = isDarkTheme ? "light" : "dark";

  if (isDarkTheme) {
    btnText.textContent = Drupal.t("Dark theme");
    btnIcon.classList.remove('fa-sun');
    btnIcon.classList.add('fa-moon');
  }
  else {
    btnText.textContent = Drupal.t("Light theme");
    btnIcon.classList.remove('fa-moon');
    btnIcon.classList.add('fa-sun');
  }

  root.classList.toggle("themag-dark");
  localStorage.setItem("theme", theme);
});

