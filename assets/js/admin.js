/* ============================================================
   H.A COLLECTION — Admin Panel JavaScript
   ============================================================ */

// Theme Toggle
const adminThemeToggle = document.getElementById('adminThemeToggle');
const adminThemeIcon = document.getElementById('adminThemeIcon');

function setAdminTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('ha-theme', theme);
    if (adminThemeIcon) {
        adminThemeIcon.className = theme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
    }
}

const savedTheme = localStorage.getItem('ha-theme') || 'dark';
setAdminTheme(savedTheme);

adminThemeToggle?.addEventListener('click', () => {
    const current = document.documentElement.getAttribute('data-theme');
    setAdminTheme(current === 'dark' ? 'light' : 'dark');
});

// Sidebar Toggle
const sidebarToggle = document.getElementById('sidebarToggle');
const adminSidebar = document.getElementById('adminSidebar');

sidebarToggle?.addEventListener('click', () => {
    adminSidebar?.classList.toggle('active');
});

// Close sidebar on click outside (mobile)
document.addEventListener('click', (e) => {
    if (window.innerWidth <= 768) {
        if (!e.target.closest('.admin-sidebar') && !e.target.closest('.sidebar-toggle')) {
            adminSidebar?.classList.remove('active');
        }
    }
});

// Auto-hide alerts after 5 seconds
document.querySelectorAll('.alert-success').forEach(alert => {
    setTimeout(() => {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        setTimeout(() => alert.remove(), 300);
    }, 5000);
});
