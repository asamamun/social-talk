<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once '../includes/header.php';
include_once '../includes/db.php';

?>

<div class="container d-flex justify-content-center">
    <div class="login-container row g-0">
        <!-- Left Side - Branding -->
        <div class="col-md-6 login-left">
            <div>
                <i class="fas fa-users fa-4x mb-4"></i>
                <h1>Welcome Back!</h1>
                <p>Connect with friends and the world around you on SocialNet.</p>
                <div class="mt-4">
                    <i class="fas fa-heart me-2"></i>
                    <i class="fas fa-share me-2"></i>
                    <i class="fas fa-comment me-2"></i>
                    <i class="fas fa-thumbs-up"></i>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="col-md-6 login-right">
            <div class="brand-title d-md-none">
                <i class="fas fa-users"></i> SocialNet
            </div>

            <!-- Success/Error Messages -->
            <div id="alertContainer"></div>

            <!-- Login Form -->
            <form id="loginForm" action="login.php" method="POST">
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                    <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                </div>

                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                    <button type="button" class="password-toggle" id="togglePassword">
                        <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" fill="none"/>
                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" fill="none"/>
                        </svg>
                        <svg class="eye-off-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                            <path d="m1 1 22 22" stroke="currentColor" stroke-width="2"/>
                            <path d="M6.71 6.71C4.68 8.1 3 10.5 3 12s1.68 3.9 3.71 5.29" stroke="currentColor" stroke-width="2" fill="none"/>
                            <path d="M17.29 17.29C19.32 15.9 21 13.5 21 12s-1.68-3.9-3.71-5.29" stroke="currentColor" stroke-width="2" fill="none"/>
                            <path d="m9.88 9.88a3 3 0 1 0 4.24 4.24" stroke="currentColor" stroke-width="2" fill="none"/>
                        </svg>
                    </button>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">
                            Remember me
                        </label>
                    </div>
                    <a href="#" class="forgot-password" onclick="showForgotPassword()">
                        Forgot Password?
                    </a>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <span class="btn-text">Sign In</span>
                </button>
            </form>

            <div class="signup-link">
                Don't have an account? <a href="signup.php">Create Account</a>
            </div>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <p class="text-muted mb-4">Enter your email address and we'll send you a link to reset your password.</p>
                
                <form id="forgotPasswordForm">
                    <div class="form-floating mb-4">
                        <input type="email" class="form-control" id="resetEmail" placeholder="Email" required>
                        <label for="resetEmail"><i class="fas fa-envelope me-2"></i>Email Address</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <span class="btn-text">Send Reset Link</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<?php
include_once '../includes/footer.php';
?>