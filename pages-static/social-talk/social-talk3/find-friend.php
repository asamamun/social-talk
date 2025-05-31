<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'includes/header1.php';
include_once 'includes/db.php';

?>

    <!-- Find Friends Section -->
    <div class="container mt-4">
        <!-- Search Bar -->
        <div class="search-card">
            <h4 class="mb-4">Find New Friends</h4>
            <div class="input-group mb-4">
                <input type="text" class="form-control" placeholder="Search for people..." aria-label="Search for people">
                <button class="btn btn-primary" type="button"><i class="fas fa-search me-2"></i>Search</button>
            </div>
        </div>

        <!-- Suggested Friends -->
        <div class="search-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Suggested Friends</h4>
                <a href="user-profile/user-profile.php" class="btn btn-light"><i class="fas fa-arrow-left me-2"></i>Back to Profile</a>
            </div>
            <div class="friend-grid">
                <div class="friend-card position-relative">
                    <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf33584c?w=100&h=100&fit=crop&crop=face" alt="Friend">
                    <div class="online-status"></div>
                    <h6>David Kim</h6>
                    <p class="text-muted small">2 mutual friends</p>
                    <button class="btn btn-primary btn-sm"><i class="fas fa-user-plus me-2"></i>Add Friend</button>
                </div>
                <div class="friend-card position-relative">
                    <img src="https://images.unsplash.com/photo-1522556189639-b1509e7e2f66?w=100&h=100&fit=crop&crop=face" alt="Friend">
                    <h6>Sophia Martinez</h6>
                    <p class="text-muted small">5 mutual friends</p>
                    <button class="btn btn-primary btn-sm"><i class="fas fa-user-plus me-2"></i>Add Friend</button>
                </div>
                <div class="friend-card position-relative">
                    <img src="https://images.unsplash.com/photo-1517841902196-2c6ae9eb9910?w=100&h=100&fit=crop&crop=face" alt="Friend">
                    <div class="online-status"></div>
                    <h6>Chris Evans</h6>
                    <p class="text-muted small">3 mutual friends</p>
                    <button class="btn btn-primary btn-sm"><i class="fas fa-user-plus me-2"></i>Add Friend</button>
                </div>
                <div class="friend-card position-relative">
                    <img src="https://images.unsplash.com/photo-1499996861123-1051b7ed1381?w=100&h=100&fit=crop&crop=face" alt="Friend">
                    <h6>Olivia Brown</h6>
                    <p class="text-muted small">1 mutual friend</p>
                    <button class="btn btn-primary btn-sm"><i class="fas fa-user-plus me-2"></i>Add Friend</button>
                </div>
                <div class="friend-card position-relative">
                    <img src="https://images.unsplash.com/photo-1502685104226-6e7b9f6b3b1e?w=100&h=100&fit=crop&crop=face" alt="Friend">
                    <div class="online-status"></div>
                    <h6>Ethan Davis</h6>
                    <p class="text-muted small">4 mutual friends</p>
                    <button class="btn btn-primary btn-sm"><i class="fas fa-user-plus me-2"></i>Add Friend</button>
                </div>
                <div class="friend-card position-relative">
                    <img src="https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=100&h=100&fit=crop&crop=face" alt="Friend">
                    <h6>Isabella Clark</h6>
                    <p class="text-muted small">2 mutual friends</p>
                    <button class="btn btn-primary btn-sm"><i class="fas fa-user-plus me-2"></i>Add Friend</button>
                </div>
            </div>
        </div>
    </div>



<?php
include_once 'includes/footer1.php';
?>