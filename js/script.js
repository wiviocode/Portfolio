// Update footer year dynamically
document.getElementById("year").textContent = new Date().getFullYear();

document.addEventListener('DOMContentLoaded', function() {
  // ===== PERFORMANCE OPTIMIZATION FOR IMAGES =====
  const images = Array.from(document.querySelectorAll('.portfolio-item picture'));
  
  if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const picture = entry.target;
          const source = picture.querySelector('source');
          const img = picture.querySelector('img');
          
          if (source && source.dataset.srcset) {
            source.srcset = source.dataset.srcset;
            source.removeAttribute('data-srcset');
          }
          
          if (img && img.dataset.src) {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
          }
          
          function onImageLoaded() {
            const spinner = picture.parentElement.querySelector('.loading-spinner');
            img.classList.add('loaded');
            if (spinner) spinner.remove();
          }
          
          if (img.complete) {
            onImageLoaded();
          } else {
            img.addEventListener('load', onImageLoaded);
          }
          
          observer.unobserve(picture);
        }
      });
    }, {
      rootMargin: '100px 0px 300px 0px', // Increased margins for earlier loading
      threshold: 0 // Trigger as soon as any part enters viewport
    });
    
    images.forEach(function(picture) {
      const spinner = document.createElement('div');
      spinner.classList.add('loading-spinner');
      
      if (picture.parentElement) {
        picture.parentElement.style.position = 'relative';
        picture.parentElement.appendChild(spinner);
      }
      
      imageObserver.observe(picture);
    });
  }

  // ===== LIGHTBOX FUNCTIONALITY =====
  const lightbox = document.getElementById('lightbox');
  const lightboxImg = document.getElementById('lightbox-img');
  const closeBtn = document.querySelector('.lightbox .close');
  const prevArrow = document.querySelector('.lightbox .prev');
  const nextArrow = document.querySelector('.lightbox .next');
  let currentIndex = -1;

  function updateLightbox() {
    lightboxImg.style.opacity = 0;
    const imgSrc = images[currentIndex].querySelector('img').dataset.src || images[currentIndex].querySelector('img').src;
    lightboxImg.src = imgSrc;
    lightboxImg.alt = images[currentIndex].alt || '';
    setTimeout(() => lightboxImg.style.opacity = 1, 50);
  }

  images.forEach((img, index) => {
    img.addEventListener('click', () => {
      currentIndex = index;
      updateLightbox();
      lightbox.classList.add('active');
      document.body.classList.add('lightbox-open');
    });
  });

  closeBtn.addEventListener('click', () => {
    lightbox.classList.remove('active');
    document.body.classList.remove('lightbox-open');
  });

  lightbox.addEventListener('click', (e) => {
    if (e.target === lightbox) {
      lightbox.classList.remove('active');
      document.body.classList.remove('lightbox-open');
    }
  });

  // Keyboard navigation
  document.addEventListener('keydown', (e) => {
    if (!lightbox.classList.contains('active')) return;
    
    switch(e.key) {
      case "ArrowRight":
        currentIndex = (currentIndex + 1) % images.length;
        updateLightbox();
        break;
      case "ArrowLeft":
        currentIndex = (currentIndex - 1 + images.length) % images.length;
        updateLightbox();
        break;
      case "Escape":
        lightbox.classList.remove('active');
        document.body.classList.remove('lightbox-open');
        break;
    }
  });

  prevArrow.addEventListener('click', (e) => {
    e.stopPropagation();
    currentIndex = (currentIndex - 1 + images.length) % images.length;
    updateLightbox();
  });

  nextArrow.addEventListener('click', (e) => {
    e.stopPropagation();
    currentIndex = (currentIndex + 1) % images.length;
    updateLightbox();
  });

  // Touch swipe support
  let touchStartX = 0;
  lightbox.addEventListener('touchstart', (e) => {
    touchStartX = e.changedTouches[0].screenX;
  }, { passive: true });
  
  lightbox.addEventListener('touchend', (e) => {
    const touchEndX = e.changedTouches[0].screenX;
    const diff = touchStartX - touchEndX;
    
    if (Math.abs(diff) > 50) {
      currentIndex = (currentIndex + (diff > 0 ? 1 : -1) + images.length) % images.length;
      updateLightbox();
    }
  }, { passive: true });

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
  
  // ===== VIDEO PLAY FUNCTIONALITY =====
  const videoItems = document.querySelectorAll('.video-item');
  
  // Add click event listeners to each video item
  videoItems.forEach(videoItem => {
    videoItem.addEventListener('click', function(e) {
      e.preventDefault();
      const video = this.querySelector('video');
      
      if (!video) return;
      
      // Make sure the video source is loaded
      const source = video.querySelector('source');
      if (source && source.dataset.src && !source.src) {
        source.src = source.dataset.src;
        video.load();
      }
      
      // Add controls if not already present
      if (!video.hasAttribute('controls')) {
        video.setAttribute('controls', '');
      }
      
      // Pause all other videos
      videos.forEach(v => {
        if (v !== video && !v.paused) {
          v.pause();
          v.parentElement.classList.remove('playing');
        }
      });
      
      // Toggle play/pause for this video
      if (video.paused) {
        video.play()
          .then(() => {
            // Success - add playing class
            videoItem.classList.add('playing');
          })
          .catch(error => {
            // Handle error
            alert('There was an issue playing this video. Please try again.');
          });
      } else {
        video.pause();
        videoItem.classList.remove('playing');
      }
    });
  });

  // Add ended event to videos to reset state
  videos.forEach(video => {
    video.addEventListener('ended', function() {
      this.parentElement.classList.remove('playing');
    });
  });

  // Remove any existing tooltips
  const existingTooltip = document.querySelector('.custom-tooltip');
  if (existingTooltip) {
    existingTooltip.remove();
  }
});

// ===== HEADER SCROLL BEHAVIOR =====
const header = document.querySelector('.site-header');
const scrollThreshold = 50;
let lastScrollPosition = window.pageYOffset;

function handleScroll() {
  const currentScrollPosition = window.pageYOffset;
  
  if (currentScrollPosition > scrollThreshold) {
    header.classList.toggle('hide', currentScrollPosition > lastScrollPosition);
  } else {
    header.classList.remove('hide');
  }
  
  lastScrollPosition = currentScrollPosition;
}

// Ensure header is visible on page load
document.addEventListener('DOMContentLoaded', () => {
  header.classList.remove('hide');
});

// Optimized scroll listener using requestAnimationFrame
let ticking = false;
window.addEventListener('scroll', () => {
  if (!ticking) {
    window.requestAnimationFrame(() => {
      handleScroll();
      ticking = false;
    });
    ticking = true;
  }
});
