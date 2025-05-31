<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/vendor/autoload.php';
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = new MysqliDb();

    if ($db->where('email', $_POST['email'])->getValue('users', 'email')) {
        $message = "Email already exists";
    } else {
        if ($_POST['password'] != $_POST['confirmPassword']) {
            $message = "Passwords do not match";
        } else {
            $data = array(
                'username' => $_POST['username'],
                'email' => $_POST['email'],
                'password_hash' => password_hash($_POST['password'], PASSWORD_DEFAULT)
            );

            if ($db->insert('users', $data)) {
                $_SESSION['message'] = "User registered successfully";
                header("Location: login.php");
                exit;
            } else {
                $message = "Error in registering user";
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
    <div class="signup-container">
        <div class="signup-header">
            <h1>Sign Up</h1>
            <p>Create your account to get started</p>
        </div>

        <!-- show dismissible alert -->
        <?php
        if (isset($message)) {
            //dismissible alert
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
            echo $message;
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
        }
        ?>

        <form id="signupForm" action="<?= $_SERVER['PHP_SELF'] ?>" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
                <div class="error-message" id="usernameError"></div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                <div class="error-message" id="emailError"></div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-input-container">
                    <input type="password" id="signupPassword" name="password" placeholder="Create a password" required>
                    <button type="button" class="toggle-password" id="togglePassword">
                        <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" fill="none" />
                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" fill="none" />
                        </svg>
                        <svg class="eye-off-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                            <path d="m1 1 22 22" stroke="currentColor" stroke-width="2" />
                            <path d="M6.71 6.71C4.68 8.1 3 10.5 3 12s1.68 3.9 3.71 5.29" stroke="currentColor" stroke-width="2" fill="none" />
                            <path d="M17.29 17.29C19.32 15.9 21 13.5 21 12s-1.68-3.9-3.71-5.29" stroke="currentColor" stroke-width="2" fill="none" />
                            <path d="m9.88 9.88a3 3 0 1 0 4.24 4.24" stroke="currentColor" stroke-width="2" fill="none" />
                        </svg>
                    </button>
                </div>
                <div class="error-message" id="passwordError"></div>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <div class="password-input-container">
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
                    <button type="button" class="toggle-password" id="toggleConfirmPassword">
                        <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" fill="none" />
                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" fill="none" />
                        </svg>
                        <svg class="eye-off-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                            <path d="m1 1 22 22" stroke="currentColor" stroke-width="2" />
                            <path d="M6.71 6.71C4.68 8.1 3 10.5 3 12s1.68 3.9 3.71 5.29" stroke="currentColor" stroke-width="2" fill="none" />
                            <path d="M17.29 17.29C19.32 15.9 21 13.5 21 12s-1.68-3.9-3.71-5.29" stroke="currentColor" stroke-width="2" fill="none" />
                            <path d="m9.88 9.88a3 3 0 1 0 4.24 4.24" stroke="currentColor" stroke-width="2" fill="none" />
                        </svg>
                    </button>
                </div>
                <div class="error-message" id="confirmPasswordError"></div>
            </div>

            <button type="submit" class="signup-btn">Create Account</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php" id="loginLink">Sign In</a>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>