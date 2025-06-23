<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/vendor/autoload.php';
if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true){
    header("Location: index.php");
    exit;    
}
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $db = new MysqliDb();
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user = $db->rawQuery("SELECT * FROM users WHERE email = ?", [$email]);
/*     var_dump($user);
    exit; */
    if(!empty($user)){
        if(password_verify($password, $user[0]['password_hash'])){
            $_SESSION['user_id'] = $user[0]['user_id'];
            $_SESSION['username'] = $user[0]['username'];
            $_SESSION['logged_in'] = true;
            $_SESSION['role'] = $user[0]['role'];
            if($user[0]['role'] == 'admin'){
                header("Location: admin/");
                exit;
            }
            else{
                header("Location: index.php");
                exit;
            }
        }
            
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social-Talk - Login</title>
    <link rel="icon" type="image/x-icon" href="assets/logo/logo1.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container d-flex justify-content-center">
    <div class="login-container row g-0">
        <!-- Left Side - Branding -->
        <div class="col-md-6 login-left">
            <div>
                <i class="fas fa-users fa-4x mb-4"></i>
                <h1>Welcome Back!</h1>
                <p>Connect with friends and the world around you on Social-Talk.</p>
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
                <i class="fas fa-users"></i> Social-Talk
            </div>

            <!-- Success/Error Messages -->
            <div id="alertContainer"></div>

            <!-- Login Form -->
            <form id="loginForm" action="<?= $_SERVER['PHP_SELF'] ?>" method="POST">
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                    <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                </div>

                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                    <button type="button" class="password-toggle" id="togglePassword">
                      <!-- Eye Icon (Visible) -->
<svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z" stroke="currentColor" stroke-width="2" fill="none"/>
    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" fill="none"/>
</svg>

<!-- Eye Off Icon (Hidden Initially) -->
<svg class="eye-off-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
    <path d="M1 1l22 22" stroke="currentColor" stroke-width="2"/>
    <path d="M6.71 6.71C4.68 8.1 3 10.5 3 12s1.68 3.9 3.71 5.29" stroke="currentColor" stroke-width="2" fill="none"/>
    <path d="M17.29 17.29C19.32 15.9 21 13.5 21 12s-1.68-3.9-3.71-5.29" stroke="currentColor" stroke-width="2" fill="none"/>
    <path d="M9.88 9.88a3 3 0 0 0 4.24 4.24" stroke="currentColor" stroke-width="2" fill="none"/>
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
                Don't have an account? <a href="register.php">Create Account</a>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>



    <!-- FontAwesome for icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = toggleBtn.querySelector('.eye-icon');
        const eyeOffIcon = toggleBtn.querySelector('.eye-off-icon');

        toggleBtn.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Toggle icons
            eyeIcon.style.display = (type === 'text') ? 'none' : 'inline';
            eyeOffIcon.style.display = (type === 'text') ? 'inline' : 'none';
        });
</script>

</body>
</html>