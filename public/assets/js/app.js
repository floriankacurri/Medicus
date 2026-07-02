// Global app script (e.g. slider, search)
(function () {
  'use strict';
  // Hero slider on home
  var slider = document.querySelector('.hero .slider-container');
  if (slider) {
    var imgs = slider.querySelectorAll('img');
    if (imgs.length > 1) {
      var i = 0;
      setInterval(function () {
        imgs[i].classList.remove('active');
        i = (i + 1) % imgs.length;
        imgs[i].classList.add('active');
      }, 4000);
    }
  }
})();
