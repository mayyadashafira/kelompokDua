// Inisialisasi aplikasi
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    setupNavigation();
    setupModal();
    setDefaultDate();
}

// Navigation
function setupNavigation() {
    const menuItems = document.querySelectorAll('.menu-item');
    
    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all items
            menuItems.forEach(i => i.classList.remove('active'));
            
            // Add active class to clicked item
            this.classList.add('active');
            
            // Hide all sections
            const sections = document.querySelectorAll('.content-section');
            sections.forEach(section => section.classList.remove('active'));
            
            // Show target section
            const target = this.getAttribute('data-target');
            const targetSection = document.getElementById(target);
            if (targetSection) {
                targetSection.classList.add('active');
            }
        });
    });
}

// Modal Functions
function setupModal() {
    const modal = document.getElementById('edit-modal');
    const closeBtn = document.querySelector('.close');
    
    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }
    
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });
}

function openEditModal(taskId, title, description, deadline, priority, course, status) {
    const modal = document.getElementById('edit-modal');
    if (!modal) return;
    
    document.getElementById('edit-task-id').value = taskId;
    document.getElementById('edit-task-title').value = title;
    document.getElementById('edit-task-description').value = description || '';
    document.getElementById('edit-task-deadline').value = deadline;
    document.getElementById('edit-task-priority').value = priority;
    document.getElementById('edit-task-course').value = course;
    document.getElementById('edit-task-status').value = status;
    
    modal.style.display = 'block';
}

function closeModal() {
    const modal = document.getElementById('edit-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Utility Functions
function setDefaultDate() {
    const deadlineInput = document.getElementById('task-deadline');
    if (deadlineInput) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        deadlineInput.value = tomorrow.toISOString().split('T')[0];
        deadlineInput.min = new Date().toISOString().split('T')[0];
    }
    
    const editDeadlineInput = document.getElementById('edit-task-deadline');
    if (editDeadlineInput) {
        editDeadlineInput.min = new Date().toISOString().split('T')[0];
    }
}

// Switch to add task function
function switchToAddTask() {
    document.querySelectorAll('.menu-item').forEach(item => item.classList.remove('active'));
    document.querySelectorAll('.content-section').forEach(section => section.classList.remove('active'));
    
    document.querySelector('[data-target="add-task"]').classList.add('active');
    document.getElementById('add-task').classList.add('active');
}

// Export functions untuk global access
window.openEditModal = openEditModal;
window.closeModal = closeModal;
window.switchToAddTask = switchToAddTask;

// Toast Notification System
function showToast(message, type = 'success', duration = 3000) {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast');
    existingToasts.forEach(toast => toast.remove());
    
    const toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container';
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icons = {
        success: 'fas fa-check',
        error: 'fas fa-exclamation-triangle',
        warning: 'fas fa-exclamation-circle',
        info: 'fas fa-info-circle'
    };
    
    const titles = {
        success: 'Sukses',
        error: 'Error',
        warning: 'Peringatan',
        info: 'Info'
    };
    
    toast.innerHTML = `
        <div class="toast-icon">
            <i class="${icons[type] || icons.success}"></i>
        </div>
        <div class="toast-content">
            <div class="toast-title">${titles[type] || titles.success}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    toastContainer.appendChild(toast);
    document.body.appendChild(toastContainer);
    
    // Auto remove after duration
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
        if (toastContainer.children.length === 0) {
            toastContainer.remove();
        }
    }, duration);
}

// Check for URL parameters and show toast
function checkForNotifications() {
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    const type = urlParams.get('type') || 'success';
    
    if (message) {
        showToast(decodeURIComponent(message), type);
        
        // Clean URL (remove parameters without reloading)
        const cleanUrl = window.location.pathname;
        window.history.replaceState({}, document.title, cleanUrl);
    }
}

// Update initializeApp function
function initializeApp() {
    setupNavigation();
    setupModal();
    setDefaultDate();
    checkForNotifications(); // Add this line
}