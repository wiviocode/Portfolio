// Update footer year dynamically
document.getElementById("year").textContent = new Date().getFullYear();

document.addEventListener('DOMContentLoaded', function() {
  // Get all images in the portfolio grid and convert NodeList to array
  var images = document.querySelectorAll('.portfolio-item img');
  var imagesArray = Array.from(images);
  var currentIndex = -1;
  var lightbox = document.getElementById('lightbox');
  var lightboxImg = document.getElementById('lightbox-img');
  var closeBtn = document.querySelector('.lightbox .close');
  var prevArrow = document.querySelector('.lightbox .prev');
  var nextArrow = document.querySelector('.lightbox .next');

  // Function to update lightbox image instantly
  function updateLightboxInstant() {
    lightboxImg.src = imagesArray[currentIndex].src;
  }

  // Open lightbox when an image is clicked (with a slight fade-in on open)
  imagesArray.forEach(function(img, index) {
    img.addEventListener('click', function() {
      currentIndex = index;
      lightboxImg.style.opacity = 0; // start hidden
      lightboxImg.src = this.src;
      lightbox.classList.add('active');
      // Trigger a very brief fade-in for initial open
      setTimeout(function(){
         lightboxImg.style.opacity = 1;
      }, 50);
    });
  });

  // Close lightbox when clicking the close button or outside the image
  closeBtn.addEventListener('click', function() {
    lightbox.classList.remove('active');
  });

  lightbox.addEventListener('click', function(e) {
    if (e.target === lightbox) {
      lightbox.classList.remove('active');
    }
  });

  // Keyboard navigation (instant update)
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

  // On-screen arrow button navigation (instant update)
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
