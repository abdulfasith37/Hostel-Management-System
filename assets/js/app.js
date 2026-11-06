// Toggle mobile hamburger menu
function toggleMenu() {
    const nav = document.getElementById('navLinks');
    if (nav) {
        nav.classList.toggle('active');
    }
}

// Confirmation helper for destructive operations
function confirmAction(message) {
    return confirm(message || "Are you sure you want to proceed?");
}

// Client-side quick filter on responsive tables
function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll(`#${tableId} tbody tr`);

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}

// Modal controls
function openTeamModal() {
    const modal = document.getElementById('teamModal');
    if (modal) modal.style.display = 'flex';
}

function closeTeamModal() {
    const modal = document.getElementById('teamModal');
    if (modal) modal.style.display = 'none';
}

function closeOnBackdrop(e) {
    if (e.target.id === 'teamModal') {
        closeTeamModal();
    }
}

// Toggle Admin Mobile Navigation Drawer
function toggleAdminNav() {
    const menu = document.getElementById('adminMenu');
    if (menu) {
        menu.classList.toggle('active');
    }
}

// Mobile dropdown toggle on tap
document.addEventListener('DOMContentLoaded', function () {
    const triggers = document.querySelectorAll('.nav-dropdown .dropdown-trigger');
    triggers.forEach(function (trigger) {
        trigger.addEventListener('click', function (e) {
            if (window.innerWidth <= 900) {
                e.preventDefault();
                const parent = this.closest('.nav-dropdown');
                parent.classList.toggle('open');
            }
        });
    });

    // Auto-bind show passwords checkbox if present
    const cb = document.getElementById('show_passwords');
    if (cb) {
        cb.addEventListener('change', function () {
            toggleAllPasswords(this);
        });
    }
});

// Password visibility helpers
function setEyeIcon(btn, isVisible) {
    const eyeOpenSvg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
    const eyeClosedSvg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
    btn.innerHTML = isVisible ? eyeClosedSvg : eyeOpenSvg;
}

function updateEyeIcons(isVisible) {
    const btns = document.querySelectorAll('.password-toggle-btn');
    btns.forEach(function (btn) {
        setEyeIcon(btn, isVisible);
    });
}

function toggleAllPasswords(trigger) {
    const cb = document.getElementById('show_passwords');
    const isChecked = cb ? cb.checked : (trigger && trigger.checked);
    const targetType = isChecked ? 'text' : 'password';
    const ids = ['current_password', 'new_password', 'confirm_password'];

    ids.forEach(function (id) {
        const el = document.getElementById(id);
        if (el) {
            el.type = targetType;
        }
    });

    updateEyeIcons(isChecked);
}

function toggleSinglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const isNowText = (input.type === 'password');
    input.type = isNowText ? 'text' : 'password';

    if (btn) {
        setEyeIcon(btn, isNowText);
    }

    const allInputs = ['current_password', 'new_password', 'confirm_password'].map(function (id) {
        return document.getElementById(id);
    }).filter(Boolean);

    const allText = allInputs.length > 0 && allInputs.every(function (el) { return el.type === 'text'; });
    const cb = document.getElementById('show_passwords');
    if (cb) {
        cb.checked = allText;
    }
}

// Backward compatibility alias
function togglePasswordVisibility(trigger) {
    toggleAllPasswords(trigger);
}
