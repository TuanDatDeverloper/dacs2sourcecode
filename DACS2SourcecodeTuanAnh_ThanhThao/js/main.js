// ============================================
// BookOnline - Main JavaScript
// ============================================

// Scroll Reveal Animation
const observerOptions = {
  threshold: 0.1,
  rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('active');
    }
  });
}, observerOptions);

// Make observer available globally so it can be reused for dynamically added elements
window.revealObserver = observer;

// Initialize scroll reveal
document.addEventListener('DOMContentLoaded', () => {
  const reveals = document.querySelectorAll('.reveal');
  reveals.forEach(reveal => observer.observe(reveal));

  // Mobile menu toggle
  const mobileMenuBtn = document.getElementById('mobile-menu-btn');
  const mobileMenu = document.getElementById('mobile-menu');
  
  if (mobileMenuBtn && mobileMenu) {
    mobileMenuBtn.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
    });
  }

  // Star rating interaction
  const stars = document.querySelectorAll('.star');
  stars.forEach((star, index) => {
    star.addEventListener('click', () => {
      const rating = index + 1;
      stars.forEach((s, i) => {
        if (i <= index) {
          s.classList.add('active');
        } else {
          s.classList.remove('active');
        }
      });
      // Update rating value if needed
      const ratingInput = document.getElementById('rating-value');
      if (ratingInput) {
        ratingInput.value = rating;
      }
    });
  });

  // Progress bar animation
  const progressBars = document.querySelectorAll('.progress-fill');
  progressBars.forEach(bar => {
    const width = bar.getAttribute('data-progress') || bar.style.width;
    if (width) {
      setTimeout(() => {
        bar.style.width = width;
      }, 100);
    }
  });

  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });

  // Reading page - Font size controls
  const fontSizeControls = document.querySelectorAll('.font-size-btn');
  const readingContent = document.getElementById('reading-content');
  
  if (fontSizeControls.length && readingContent) {
    fontSizeControls.forEach(btn => {
      btn.addEventListener('click', () => {
        const size = btn.getAttribute('data-size');
        readingContent.style.fontSize = size;
        localStorage.setItem('fontSize', size);
      });
    });

    // Load saved font size
    const savedSize = localStorage.getItem('fontSize');
    if (savedSize) {
      readingContent.style.fontSize = savedSize;
    }
  }

  // Theme toggle for reading page
  const themeToggle = document.getElementById('theme-toggle');
  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      document.body.classList.toggle('reading-light');
      const isLight = document.body.classList.contains('reading-light');
      localStorage.setItem('readingTheme', isLight ? 'light' : 'dark');
    });

    // Load saved theme
    const savedTheme = localStorage.getItem('readingTheme');
    if (savedTheme === 'light') {
      document.body.classList.add('reading-light');
    }
  }

  // Book search functionality
  const searchInput = document.getElementById('book-search');
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      const query = e.target.value.toLowerCase();
      const bookCards = document.querySelectorAll('.book-card');
      
      bookCards.forEach(card => {
        const title = card.querySelector('.book-title')?.textContent.toLowerCase() || '';
        const author = card.querySelector('.book-author')?.textContent.toLowerCase() || '';
        
        if (title.includes(query) || author.includes(query)) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
    });
  }

  // Filter functionality
  const filterButtons = document.querySelectorAll('.filter-btn');
  filterButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      // Remove active class from all buttons
      filterButtons.forEach(b => b.classList.remove('active'));
      // Add active class to clicked button
      btn.classList.add('active');
      
      const filter = btn.getAttribute('data-filter');
      const items = document.querySelectorAll('.filterable-item');
      
      items.forEach(item => {
        if (filter === 'all' || item.getAttribute('data-category') === filter) {
          item.style.display = 'block';
        } else {
          item.style.display = 'none';
        }
      });
    });
  });
});

// Parallax effect for hero section
window.addEventListener('scroll', () => {
  const scrolled = window.pageYOffset;
  const hero = document.querySelector('.hero-section');
  if (hero) {
    hero.style.transform = `translateY(${scrolled * 0.5}px)`;
  }
});

// Books API Integration
async function loadBooksFromAPI() {
  // Check if BooksAPI is available
  if (typeof window.BooksAPI === 'undefined') {
    console.warn('BooksAPI chưa được load');
    return;
  }

  try {
    // Load popular books for homepage
    const popularBooks = await window.BooksAPI.getPopularBooks('fiction', 12);
    displayBooksOnPage(popularBooks, '.books-grid, .books-container');
    
    // Save to localStorage for offline access
    window.BooksAPI.saveBooksToLocal('popularBooks', popularBooks);
  } catch (error) {
    console.error('Lỗi khi tải sách:', error);
    // Fallback to local storage if API fails
    const cachedBooks = window.BooksAPI.getBooksFromLocal('popularBooks');
    if (cachedBooks.length > 0) {
      displayBooksOnPage(cachedBooks, '.books-grid, .books-container');
    }
  }
}

function displayBooksOnPage(books, selector) {
  const container = document.querySelector(selector);
  if (!container) return;

  container.innerHTML = books.map(book => `
    <div class="book-card filterable-item card-modern glass rounded-2xl overflow-hidden reveal relative z-10" data-category="reading">
      <a href="book-info.html?id=${book.id}" class="block">
        <div class="relative h-64 w-full overflow-hidden bg-gradient-to-br from-[#FFB347]/10 to-[#4A7856]/10">
          <img 
            src="${book.cover}" 
            alt="${book.title}"
            class="h-full w-full object-cover"
            onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'200\\' height=\\'300\\'%3E%3Crect fill=\\'%23faf9f6\\' width=\\'200\\' height=\\'300\\'/%3E%3Ctext x=\\'50%25\\' y=\\'50%25\\' text-anchor=\\'middle\\' dy=\\'.3em\\' fill=\\'%23FFB347\\' font-family=\\'sans-serif\\' font-size=\\'14\\'%3EBook%3C/text%3E%3C/svg%3E'"
          />
          <div class="absolute top-2 right-2 px-2 py-1 rounded glass-strong text-xs bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white font-semibold">
            ${book.isFree ? 'Miễn phí' : 'Đang đọc'}
          </div>
        </div>
      </a>
      <div class="p-4 space-y-3">
        <div>
          <h3 class="book-title font-bold text-lg mb-1 line-clamp-2 text-gray-900">${book.title}</h3>
          <p class="book-author text-gray-600 text-sm">${book.author}</p>
        </div>
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-1 text-[#FFB347]">
            ${generateStars(book.rating)}
            <span class="text-gray-600 text-xs ml-1">${book.rating.toFixed(1)}</span>
          </div>
          <span class="px-2 py-1 rounded glass text-xs text-gray-700 bg-gray-50">${book.category}</span>
        </div>
        <div class="flex gap-2">
          <a href="book-info.html?id=${book.id}" class="flex-1 px-4 py-2 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg text-center text-sm font-semibold hover:shadow-lg glow-hover transition-all">
            Xem chi tiết
          </a>
          <a href="book-info.html?id=${book.id}" class="px-4 py-2 glass border border-gray-200 rounded-lg text-center text-sm hover:bg-gray-50 transition-all text-gray-700">
            <i class="fas fa-info"></i>
          </a>
        </div>
      </div>
    </div>
  `).join('');
}

function generateStars(rating) {
  const fullStars = Math.floor(rating);
  const hasHalfStar = rating % 1 >= 0.5;
  let stars = '';
  
  for (let i = 0; i < fullStars; i++) {
    stars += '<i class="fas fa-star text-xs"></i>';
  }
  
  if (hasHalfStar && fullStars < 5) {
    stars += '<i class="fas fa-star-half-alt text-xs"></i>';
  }
  
  const emptyStars = 5 - Math.ceil(rating);
  for (let i = 0; i < emptyStars; i++) {
    stars += '<i class="far fa-star text-xs"></i>';
  }
  
  return stars;
}

// Load books when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  // Load books API script
  const script = document.createElement('script');
  script.src = 'js/books-api.js';
  script.type = 'module';
  script.onload = () => {
    // Wait a bit for module to load
    setTimeout(() => {
      if (typeof window.BooksAPI !== 'undefined') {
        loadBooksFromAPI();
      }
    }, 100);
  };
  document.head.appendChild(script);
});

// Navbar scroll effect
let lastScroll = 0;
const navbar = document.querySelector('.navbar');
window.addEventListener('scroll', () => {
  const currentScroll = window.pageYOffset;
  
  if (currentScroll > 100) {
    navbar?.classList.add('scrolled');
  } else {
    navbar?.classList.remove('scrolled');
  }
  
  lastScroll = currentScroll;
});

