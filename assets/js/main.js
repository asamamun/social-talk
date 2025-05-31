// ========== Common Utility Functions ==========

// Toggle password visibility
function togglePasswordVisibility(inputId, buttonId) {
    const input = document.getElementById(inputId);
    const button = document.getElementById(buttonId);
    const eyeIcon = button.querySelector('.eye-icon');
    const eyeOffIcon = button.querySelector('.eye-off-icon');

    if (input.type === 'password') {
        input.type = 'text';
        eyeIcon.style.display = 'none';
        eyeOffIcon.style.display = 'block';
    } else {
        input.type = 'password';
        eyeIcon.style.display = 'block';
        eyeOffIcon.style.display = 'none';
    }
}

// Show alert message
function showAlert(message, type = 'success') {
    const alertContainer = document.getElementById('alertContainer');
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    alertContainer.appendChild(alertDiv);

    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}



// ========== SIGNUP Script ==========
document.addEventListener('DOMContentLoaded', function () {
    const signupForm = document.getElementById('signupForm');
    if (!signupForm) return;

    const username = document.getElementById('username');
    const email = document.getElementById('email'); // Updated ID
    const password = document.getElementById('signupPassword');
    const confirmPassword = document.getElementById('confirmPassword');

    function showError(id, message) {
        const el = document.getElementById(id);
        el.textContent = message;
        el.style.display = 'block';
    }

    function validateForm() {
        let isValid = true;

        document.querySelectorAll('.error-message').forEach(el => {
            el.style.display = 'none';
            el.textContent = '';
        });

        if (username.value.trim().length < 3) {
            showError('usernameError', 'Username must be at least 3 characters long');
            isValid = false;
        }

        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email.value)) {
            showError('emailError', 'Please enter a valid email address');
            isValid = false;
        }

        if (password.value.length < 6) {
            showError('passwordError', 'Password must be at least 6 characters long');
            isValid = false;
        }

        if (password.value !== confirmPassword.value) {
            showError('confirmPasswordError', 'Passwords do not match');
            isValid = false;
        }

        return isValid;
    }


    confirmPassword.addEventListener('input', function () {
        const errorElement = document.getElementById('confirmPasswordError');
        if (password.value !== confirmPassword.value && confirmPassword.value !== '') {
            showError('confirmPasswordError', 'Passwords do not match');
        } else {
            errorElement.style.display = 'none';
        }
    });

    document.getElementById('loginLink')?.addEventListener('click', function (e) {
        e.preventDefault();
        window.location.href = 'login.php';
    });

    // Password toggle buttons
    document.getElementById('togglePassword')?.addEventListener('click', function () {
        togglePasswordVisibility('signupPassword', 'togglePassword');
    });

    document.getElementById('toggleConfirmPassword')?.addEventListener('click', function () {
        togglePasswordVisibility('confirmPassword', 'toggleConfirmPassword');
    });
});