// Issue Reporting Portal — main.js

// Auto-dismiss alerts after 4 seconds
document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function () { alert.remove(); }, 500);
        }, 4000);
    });

    // Confirm before deleting
    document.querySelectorAll('.confirm-delete').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to delete this? This cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // Client-side form validation
    const reportForm = document.getElementById('reportIssueForm');
    if (reportForm) {
        reportForm.addEventListener('submit', function (e) {
            const title = document.getElementById('title').value.trim();
            const desc  = document.getElementById('description').value.trim();
            const cat   = document.getElementById('category').value;
            if (!title || !desc || !cat) {
                e.preventDefault();
                showError('Please fill in all required fields.');
                return;
            }
            if (title.length < 5) {
                e.preventDefault();
                showError('Title must be at least 5 characters.');
                return;
            }
            if (desc.length < 10) {
                e.preventDefault();
                showError('Description must be at least 10 characters.');
                return;
            }
        });
    }

    // Register form validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            const pass    = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            if (pass !== confirm) {
                e.preventDefault();
                showError('Passwords do not match.');
                return;
            }
            if (pass.length < 6) {
                e.preventDefault();
                showError('Password must be at least 6 characters.');
                return;
            }
        });
    }
});

function showError(msg) {
    const existing = document.querySelector('.alert-error.js-alert');
    if (existing) existing.remove();
    const div = document.createElement('div');
    div.className = 'alert alert-error js-alert';
    div.textContent = msg;
    const form = document.querySelector('form');
    if (form) form.insertAdjacentElement('beforebegin', div);
}