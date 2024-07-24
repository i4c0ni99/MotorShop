document.addEventListener('DOMContentLoaded', function() {
  var body_event = document.querySelector("body");

  body_event.addEventListener("click", function(event) {
    if (event.target.classList.contains("btn-dark-setting")) {
      event.target.classList.toggle('dark');
      document.body.classList.toggle('dark');

      if (event.target.classList.contains('dark')) {
        event.target.textContent = 'Light';
        sessionStorage.setItem('themeState', 'dark');
      } else {
        event.target.textContent = 'Dark';
        sessionStorage.setItem('themeState', 'light');
      }
    }
  });
});
