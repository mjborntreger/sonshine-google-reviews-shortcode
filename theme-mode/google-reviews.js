document.addEventListener('DOMContentLoaded', function () {
  const btn = document.querySelector('.show-more-reviews');
  if (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.hidden-review').forEach(r => {
        r.style.display = 'block';
        r.style.opacity = 0;
        setTimeout(() => r.style.opacity = 1, 50);
      });
      this.style.display = 'none';
    });
  }
});
