// Update footer year dynamically
document.getElementById("year").textContent = new Date().getFullYear();

document.addEventListener('DOMContentLoaded', function() {
  // ===== PERFORMANCE OPTIMIZATION FOR IMAGES =====
  // Get all portfolio images as an array
  var images = Array.from(document.querySelectorAll('.portfolio-item img'));
  
  // Set up Intersection Observer for lazy loading
  if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          // Get the data-src from the attribute and set it as the src
          if (img.dataset.src) {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
          }
          
          // When image is loaded, fade it in
          function onImageLoaded() {
            var spinner = img.parentElement.querySelector('.loading-spinner');
            img.classList.add('loaded');
            if (spinner) spinner.remove();
          }
          
          if (img.complete) {
            onImageLoaded();
          } else {
            img.addEventListener('load', onImageLoaded);
          }
          
          // Stop observing the image after it's loaded
          observer.unobserve(img);
        }
      });
    }, {
      rootMargin: '0px 0px 200px 0px' // Load images when they're within 200px of the viewport
    });
    
    // Add loading spinner to each image container and start observing
    images.forEach(function(img) {
      var spinner = document.createElement('div');
      spinner.classList.add('loading-spinner');
      
      // Ensure the parent container is relatively positioned for proper spinner overlay
      if (img.parentElement) {
        img.parentElement.style.position = 'relative';
        img.parentElement.appendChild(spinner);
      }
      
      // Start observing the image
      imageObserver.observe(img);
    });
  } else {
    // Fallback for browsers that don't support Intersection Observer
    images.forEach(function(img) {
      var spinner = document.createElement('div');
      spinner.classList.add('loading-spinner');
      
      if (img.parentElement) {
        img.parentElement.style.position = 'relative';
        img.parentElement.appendChild(spinner);
      }
      
      function onImageLoaded() {
        setTimeout(function(){
           img.classList.add('loaded');
           spinner.remove();
        }, 150);
      }
      
      if (img.complete) {
        onImageLoaded();
      } else {
        img.addEventListener('load', onImageLoaded);
      }
    });
  }

  // ===== ENHANCED LIGHTBOX FUNCTIONALITY =====
  var imagesArray = images;
  var currentIndex = -1;
  var lightbox = document.getElementById('lightbox');
  var lightboxImg = document.getElementById('lightbox-img');
  var closeBtn = document.querySelector('.lightbox .close');
  var prevArrow = document.querySelector('.lightbox .prev');
  var nextArrow = document.querySelector('.lightbox .next');

  // Preload adjacent images when an image is selected
  function preloadAdjacentImages(index) {
    const nextIndex = (index + 1) % imagesArray.length;
    const prevIndex = (index - 1 + imagesArray.length) % imagesArray.length;
    
    // Preload next and previous images
    if (imagesArray[nextIndex].dataset.src) {
      const nextImg = new Image();
      nextImg.src = imagesArray[nextIndex].dataset.src;
    } else if (imagesArray[nextIndex].src) {
      const nextImg = new Image();
      nextImg.src = imagesArray[nextIndex].src;
    }
    
    if (imagesArray[prevIndex].dataset.src) {
      const prevImg = new Image();
      prevImg.src = imagesArray[prevIndex].dataset.src;
    } else if (imagesArray[prevIndex].src) {
      const prevImg = new Image();
      prevImg.src = imagesArray[prevIndex].src;
    }
  }

  function updateLightboxInstant() {
    lightboxImg.style.opacity = 0;
    
    // Use dataset.src if available, otherwise use src
    const imgSrc = imagesArray[currentIndex].dataset.src || imagesArray[currentIndex].src;
    lightboxImg.src = imgSrc;
    
    // Get the alt text from the original image if available
    if (imagesArray[currentIndex].alt) {
      lightboxImg.alt = imagesArray[currentIndex].alt;
    }
    
    // Fade in the image
    setTimeout(function() {
      lightboxImg.style.opacity = 1;
    }, 50);
    
    // Preload adjacent images
    preloadAdjacentImages(currentIndex);
  }

  imagesArray.forEach(function(img, index) {
    img.addEventListener('click', function() {
      currentIndex = index;
      updateLightboxInstant();
      lightbox.classList.add('active');
      
      // Add class to body to prevent scrolling when lightbox is open
      document.body.classList.add('lightbox-open');
    });
  });

  closeBtn.addEventListener('click', function() {
    lightbox.classList.remove('active');
    document.body.classList.remove('lightbox-open');
  });

  lightbox.addEventListener('click', function(e) {
    if (e.target === lightbox) {
      lightbox.classList.remove('active');
      document.body.classList.remove('lightbox-open');
    }
  });

  // Enhanced keyboard navigation with swipe support
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
      document.body.classList.remove('lightbox-open');
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

  // ===== TOUCH SWIPE SUPPORT FOR LIGHTBOX =====
  let touchStartX = 0;
  let touchEndX = 0;
  
  lightbox.addEventListener('touchstart', function(e) {
    touchStartX = e.changedTouches[0].screenX;
  }, false);
  
  lightbox.addEventListener('touchend', function(e) {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
  }, false);
  
  function handleSwipe() {
    if (touchEndX < touchStartX - 50) {
      // Swipe left: next image
      currentIndex = (currentIndex + 1) % imagesArray.length;
      updateLightboxInstant();
    }
    
    if (touchEndX > touchStartX + 50) {
      // Swipe right: previous image
      currentIndex = (currentIndex - 1 + imagesArray.length) % imagesArray.length;
      updateLightboxInstant();
    }
  }
  
  // ===== LAZY LOADING FOR VIDEOS =====
  const videos = document.querySelectorAll('.video-item video');
  
  if ('IntersectionObserver' in window) {
    const videoObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const video = entry.target;
          const source = video.querySelector('source');
          
          if (source && source.dataset.src) {
            source.src = source.dataset.src;
            source.removeAttribute('data-src');
            video.load();
          }
          
          observer.unobserve(video);
        }
      });
    }, {
      rootMargin: '0px 0px 200px 0px'
    });
    
    videos.forEach(video => {
      videoObserver.observe(video);
    });
  }
});

// ===== HIDE/SHOW HEADER ON SCROLL =====
let lastScrollPosition = 0;
const header = document.querySelector('.site-header');
const scrollThreshold = 50;

function handleScroll() {
  const currentScrollPosition = window.pageYOffset;
  
  // Only apply the effect after scrolling down a bit
  if (currentScrollPosition > scrollThreshold) {
    // Scrolling down - hide header
    if (currentScrollPosition > lastScrollPosition) {
      header.classList.add('hide');
    } 
    // Scrolling up - show header
    else {
      header.classList.remove('hide');
    }
  }
  
  lastScrollPosition = currentScrollPosition;
}

// Add the scroll listener with debounce for performance
let scrollTimeout;
window.addEventListener('scroll', function() {
  if (!scrollTimeout) {
    scrollTimeout = setTimeout(function() {
      handleScroll();
      scrollTimeout = null;
    }, 10);
  }
});
