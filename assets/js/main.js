/* ============================================================
   H.A COLLECTION — Main Frontend JavaScript
   AJAX Cart | Live Search | Slider | Scroll Animations | Theme
   ============================================================ */

const SITE_URL = document.querySelector('link[rel="stylesheet"]')?.href.split('/assets')[0] || '';

// ---- Theme Toggle ----
const themeToggle = document.getElementById('themeToggle');
const themeIcon = document.getElementById('themeIcon');

function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('ha-theme', theme);
    if (themeIcon) {
        themeIcon.className = theme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
    }
}

// Initialize theme
const savedTheme = localStorage.getItem('ha-theme') || 'dark';
setTheme(savedTheme);

if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-theme');
        setTheme(current === 'dark' ? 'light' : 'dark');
    });
}

// ---- Sticky Header Shadow ----
const header = document.getElementById('mainHeader');
if (header) {
    window.addEventListener('scroll', () => {
        header.classList.toggle('scrolled', window.scrollY > 50);
    });
}

// ---- Mobile Navigation ----
const mobileToggle = document.getElementById('mobileMenuToggle');
const mobileNav = document.getElementById('mobileNav');
const mobileOverlay = document.getElementById('mobileNavOverlay');
const mobileClose = document.getElementById('mobileNavClose');

function openMobileNav() {
    mobileNav?.classList.add('active');
    mobileOverlay?.classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeMobileNav() {
    mobileNav?.classList.remove('active');
    mobileOverlay?.classList.remove('active');
    document.body.style.overflow = '';
}

mobileToggle?.addEventListener('click', openMobileNav);
mobileClose?.addEventListener('click', closeMobileNav);
mobileOverlay?.addEventListener('click', closeMobileNav);

// ---- Hero Slider ----
const sliderWrapper = document.querySelector('.slider-wrapper');
const slides = document.querySelectorAll('.slide');
const dotsContainer = document.getElementById('sliderDots');
const prevBtn = document.getElementById('sliderPrev');
const nextBtn = document.getElementById('sliderNext');
let currentSlide = 0;
let slideInterval;

if (slides.length > 0) {
    // Create dots
    slides.forEach((_, i) => {
        const dot = document.createElement('button');
        dot.className = `slider-dot ${i === 0 ? 'active' : ''}`;
        dot.addEventListener('click', () => goToSlide(i));
        dotsContainer?.appendChild(dot);
    });

    function goToSlide(index) {
        slides[currentSlide].classList.remove('active');
        currentSlide = (index + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
        updateDots();
    }

    function updateDots() {
        dotsContainer?.querySelectorAll('.slider-dot').forEach((dot, i) => {
            dot.classList.toggle('active', i === currentSlide);
        });
    }

    function nextSlide() { goToSlide(currentSlide + 1); }
    function prevSlide() { goToSlide(currentSlide - 1); }

    prevBtn?.addEventListener('click', () => { prevSlide(); resetInterval(); });
    nextBtn?.addEventListener('click', () => { nextSlide(); resetInterval(); });

    function startInterval() { slideInterval = setInterval(nextSlide, 5000); }
    function resetInterval() { clearInterval(slideInterval); startInterval(); }
    startInterval();
}

// ---- Live Search ----
const searchInput = document.getElementById('searchInput');
const searchSuggestions = document.getElementById('searchSuggestions');
const searchCategory = document.getElementById('searchCategory');
const searchBtn = document.getElementById('searchBtn');
let searchTimeout;

if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            searchSuggestions?.classList.remove('active');
            return;
        }

        searchTimeout = setTimeout(() => {
            const category = searchCategory?.value || '';
            fetch(`${SITE_URL}/api/search.php?q=${encodeURIComponent(query)}&category=${category}`)
                .then(r => r.json())
                .then(data => {
                    if (data.results.length > 0) {
                        searchSuggestions.innerHTML = data.results.map(item => `
                            <a href="${item.url}" class="suggestion-item">
                                ${item.image ? `<img src="${item.image}" alt="">` : '<div style="width:40px;height:40px;background:var(--bg-secondary);border-radius:6px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-image" style="color:var(--text-muted);"></i></div>'}
                                <div class="sugg-info">
                                    <div class="sugg-title">${item.title}</div>
                                    <div class="sugg-cat">${item.category}</div>
                                </div>
                                <div class="sugg-price">${item.price}</div>
                            </a>
                        `).join('');
                        searchSuggestions.classList.add('active');
                    } else {
                        searchSuggestions.innerHTML = '<div class="suggestion-item"><div class="sugg-info"><div class="sugg-title">No results found</div></div></div>';
                        searchSuggestions.classList.add('active');
                    }
                })
                .catch(() => searchSuggestions?.classList.remove('active'));
        }, 300);
    });

    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const query = this.value.trim();
            if (query) {
                window.location.href = `${SITE_URL}/pages/shop.php?q=${encodeURIComponent(query)}`;
            }
        }
    });

    // Close suggestions on click outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.search-wrapper')) {
            searchSuggestions?.classList.remove('active');
        }
    });
}

searchBtn?.addEventListener('click', () => {
    const query = searchInput?.value.trim();
    if (query) {
        window.location.href = `${SITE_URL}/pages/shop.php?q=${encodeURIComponent(query)}`;
    }
});

// Mobile search
const mobileSearchInput = document.getElementById('mobileSearchInput');
mobileSearchInput?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        const query = this.value.trim();
        if (query) {
            window.location.href = `${SITE_URL}/pages/shop.php?q=${encodeURIComponent(query)}`;
        }
    }
});

// ---- AJAX Cart ----
function addToCart(productId, quantity = 1, size = '', color = '') {
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    if (size) formData.append('size', size);
    if (color) formData.append('color', color);

    fetch(`${SITE_URL}/api/cart.php`, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cartCount);
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Error adding to cart', 'error');
        }
    })
    .catch(() => showToast('Error adding to cart', 'error'));
}

function updateCartQty(cartId, delta) {
    const input = document.getElementById(`qty-${cartId}`);
    if (!input) return;
    let newQty = parseInt(input.value) + delta;
    if (newQty < 1) newQty = 1;
    input.value = newQty;
    setCartQty(cartId, newQty);
}

function setCartQty(cartId, quantity) {
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('cart_id', cartId);
    formData.append('quantity', quantity);

    fetch(`${SITE_URL}/api/cart.php`, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cartCount);
            location.reload();
        }
    });
}

function removeFromCart(cartId) {
    const formData = new FormData();
    formData.append('action', 'remove');
    formData.append('cart_id', cartId);

    fetch(`${SITE_URL}/api/cart.php`, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cartCount);
            const item = document.getElementById(`cartItem-${cartId}`);
            if (item) {
                item.style.opacity = '0';
                item.style.transform = 'translateX(50px)';
                setTimeout(() => location.reload(), 300);
            }
        }
    });
}

function updateCartCount(count) {
    const el = document.getElementById('cartCount');
    if (el) {
        el.textContent = count || 0;
        el.style.transform = 'scale(1.3)';
        setTimeout(() => el.style.transform = 'scale(1)', 200);
    }
}

// ---- Toast Notification ----
function showToast(message, type = 'success') {
    // Remove existing toasts
    document.querySelectorAll('.toast').forEach(t => t.remove());

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
    document.body.appendChild(toast);

    requestAnimationFrame(() => toast.classList.add('show'));
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 3000);
}

// ---- Scroll Animations ----
function initScrollAnimations() {
    const elements = document.querySelectorAll('.scroll-animate');

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -40px 0px'
        });

        elements.forEach(el => observer.observe(el));
    } else {
        elements.forEach(el => el.classList.add('animated'));
    }
}

document.addEventListener('DOMContentLoaded', initScrollAnimations);

// ---- Shop Sidebar Toggle (Mobile) ----
const filterToggleBtn = document.getElementById('filterToggleBtn');
const shopSidebar = document.getElementById('shopSidebar');
const sidebarCloseBtn = document.getElementById('sidebarClose');

filterToggleBtn?.addEventListener('click', () => {
    shopSidebar?.classList.add('active');
    mobileOverlay?.classList.add('active');
    document.body.style.overflow = 'hidden';
});

sidebarCloseBtn?.addEventListener('click', () => {
    shopSidebar?.classList.remove('active');
    mobileOverlay?.classList.remove('active');
    document.body.style.overflow = '';
});
