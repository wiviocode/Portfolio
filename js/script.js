// Update footer year dynamically
document.getElementById("year").textContent = new Date().getFullYear();

document.addEventListener('DOMContentLoaded', function() {
  // ===== PERFORMANCE OPTIMIZATION FOR IMAGES =====
  const images = Array.from(document.querySelectorAll('.portfolio-item img'));
  
  if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          if (img.dataset.src) {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
          }
          
          function onImageLoaded() {
            const spinner = img.parentElement.querySelector('.loading-spinner');
            img.classList.add('loaded');
            if (spinner) spinner.remove();
          }
          
          if (img.complete) {
            onImageLoaded();
          } else {
            img.addEventListener('load', onImageLoaded);
          }
          
          observer.unobserve(img);
        }
      });
    }, {
      rootMargin: '0px 0px 200px 0px'
    });
    
    images.forEach(function(img) {
      const spinner = document.createElement('div');
      spinner.classList.add('loading-spinner');
      
      if (img.parentElement) {
        img.parentElement.style.position = 'relative';
        img.parentElement.appendChild(spinner);
      }
      
      imageObserver.observe(img);
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
    const imgSrc = images[currentIndex].dataset.src || images[currentIndex].src;
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

// Debounced scroll listener
let scrollTimeout;
window.addEventListener('scroll', () => {
  if (!scrollTimeout) {
    scrollTimeout = setTimeout(() => {
      handleScroll();
      scrollTimeout = null;
    }, 10);
  }
});
