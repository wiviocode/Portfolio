// Update footer year dynamically
document.getElementById("year").textContent = new Date().getFullYear();

document.addEventListener('DOMContentLoaded', function() {
  // Get all portfolio images as an array
  var images = Array.from(document.querySelectorAll('.portfolio-item img'));

  // Sort images based on their vertical position (top-to-bottom)
  var sortedImages = images.slice().sort(function(a, b) {
    return a.getBoundingClientRect().top - b.getBoundingClientRect().top;
  });

  // Assign a delay (in milliseconds) for each image based on its sorted order
  sortedImages.forEach(function(img, index) {
    img.dataset.delay = index * 150; // 150ms extra delay per image
  });

  // For each image, add a loading spinner and then fade it in after its delay once loaded
  images.forEach(function(img) {
    var spinner = document.createElement('div');
    spinner.classList.add('loading-spinner');
    // Ensure the parent container is relatively positioned for proper spinner overlay
    if (img.parentElement) {
      img.parentElement.style.position = 'relative';
      img.parentElement.appendChild(spinner);
    }
    function onImageLoaded() {
      var delay = parseInt(img.dataset.delay) || 0;
      setTimeout(function(){
         img.classList.add('loaded');
         spinner.remove();
      }, delay);
    }
    if (img.complete) {
      onImageLoaded();
    } else {
      img.addEventListener('load', onImageLoaded);
    }
  });

  // Existing lightbox functionality for portfolio images
  var imagesArray = images;
  var currentIndex = -1;
  var lightbox = document.getElementById('lightbox');
  var lightboxImg = document.getElementById('lightbox-img');
  var closeBtn = document.querySelector('.lightbox .close');
  var prevArrow = document.querySelector('.lightbox .prev');
  var nextArrow = document.querySelector('.lightbox .next');

  function updateLightboxInstant() {
    lightboxImg.src = imagesArray[currentIndex].src;
  }

  imagesArray.forEach(function(img, index) {
    img.addEventListener('click', function() {
      currentIndex = index;
      lightboxImg.style.opacity = 0; // start hidden
      lightboxImg.src = this.src;
      lightbox.classList.add('active');
      // Brief fade-in for initial open
      setTimeout(function(){
         lightboxImg.style.opacity = 1;
      }, 50);
    });
  });

  closeBtn.addEventListener('click', function() {
    lightbox.classList.remove('active');
  });

  lightbox.addEventListener('click', function(e) {
    if (e.target === lightbox) {
      lightbox.classList.remove('active');
    }
  });

  document.addEventListener('keydown', function(e) {
    if (!lightbox.classList.contains('active')) return;
    if (e.key === "ArrowRight") {
      currentIndex = (currentIndex + 1) % imagesArray.length;
      updateLightboxInstant();
    } else if (e.key === "ArrowLeft") {
      currentIndex = (currentIndex - 1 + imagesArray.length) % imagesArray.length;
      updateLightboxInstant();
    } else if (e.key === "Escape") {
      lightbox.classList.remove('active');
    }
  });

  prevArrow.addEventListener('click', function(e) {
    e.stopPropagation();
    currentIndex = (currentIndex - 1 + imagesArray.length) % imagesArray.length;
    updateLightboxInstant();
  });

  nextArrow.addEventListener('click', function(e) {
    e.stopPropagation();
    currentIndex = (currentIndex + 1) % imagesArray.length;
    updateLightboxInstant();
  });
});
