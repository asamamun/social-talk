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

// ========== LOGIN Script ==========
document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const email = document.getElementById('email').value; // Updated ID
            const password = document.getElementById('password').value; // Updated ID
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.classList.add('loading');

            // Send AJAX request to login.php
            fetch('login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'login': 'true',
                    'email': email,
                    'password': password
                })
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.classList.remove('loading');
                if (data.success) {
                    showAlert('Login successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1500);
                } else {
                    showAlert(data.message || 'Invalid email or password', 'danger');
                }
            })
            .catch(error => {
                submitBtn.classList.remove('loading');
                showAlert('An error occurred. Please try again.', 'danger');
                console.error('Error:', error);
            });
        });
    }

    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const email = document.getElementById('resetEmail').value;
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.classList.add('loading');

            setTimeout(() => {
                submitBtn.classList.remove('loading');
                if (email) {
                    showAlert('Password reset link sent to your email!', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal')).hide();
                    this.reset();
                } else {
                    showAlert('Please enter your email address.', 'danger');
                }
            }, 2000);
        });
    }

    // Forgot password modal show
    window.showForgotPassword = function () {
        const modal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
        modal.show();
    };

    // Social logins
    window.loginWithGoogle = function () {
        showAlert('Google login integration would be implemented here.', 'info');
    };

    window.loginWithFacebook = function () {
        showAlert('Facebook login integration would be implemented here.', 'info');
    };

    // Input focus effects
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('focus', function () {
            this.parentElement.style.transform = 'scale(1.02)';
            this.parentElement.style.transition = 'transform 0.2s ease';
        });
        input.addEventListener('blur', function () {
            this.parentElement.style.transform = 'scale(1)';
        });
    });
});

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

    signupForm.addEventListener('submit', function (e) {
        e.preventDefault();
        if (validateForm()) {
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            btn.textContent = 'Creating Account...';
            btn.disabled = true;

            // Send AJAX request to signup.php
            fetch('signup.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'username': username.value,
                    'email': email.value,
                    'password': password.value,
                    'confirmPassword': confirmPassword.value
                })
            })
            .then(response => response.json())
            .then(data => {
                btn.textContent = originalText;
                btn.disabled = false;
                if (data.success) {
                    showAlert('Account created successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1500);
                } else {
                    showAlert(data.message || 'An error occurred during signup.', 'danger');
                }
            })
            .catch(error => {
                btn.textContent = originalText;
                btn.disabled = false;
                showAlert('An error occurred. Please try again.', 'danger');
                console.error('Error:', error);
            });
        }
    });

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