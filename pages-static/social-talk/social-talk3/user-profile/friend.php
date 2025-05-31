<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once '../includes/header2.php';
include_once '../includes/db.php';

?>



    <!-- Friends Section -->
    <div class="container mt-4">
        <div class="friend-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">John Doe's Friends (245)</h4>
                <div>
                   <a href="../find-friend.php"class="btn btn-primary me-2"><i class="fas fa-user-plus me-2"></i>Find Friends</a>
                    <a href="user-profile.php" class="btn btn-light"><i class="fas fa-arrow-left me-2"></i>Back to Profile</a>
                </div>
            </div>
            <div class="friend-grid">
                <div class="friend-card position-relative">
                    <img src="https://images.unsplash.com/photo-1494790108755-2616b612b820?w=100&h=100&fit=crop&crop=face" alt="Friend">
                    <div class="online-status"></div>
                    <h6>Sarah Johnson</h6>
                    <button class="btn btn-success btn-sm"><i class="fas fa-user-friends me-2"></i>Friends</button>
                </div>
                <div class="friend-card position-relative">
                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=face" alt="Friend">
                    <h6>Mike Chen</h6>
                    <button class="btn btn-success btn-sm"><i class="fas fa-user-friends me-2"></i>Friends</button>
                </div>
                <div class="friend-card position-relative">
                    <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop&crop=face" alt="Friend">
                    <div class="online-status"></div>
                    <h6>Emma Wilson</h6>
                    <button class="btn btn-success btn-sm"><i class="fas fa-user-friends me-2"></i>Friends</button>
                </div>
                <div class="friend-card position-relative">
                    <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop&crop=face" alt="Friend">
                    <h6>Alex Rodriguez</h6>
                    <button class="btn btn-success btn-sm"><i class="fas fa-user-friends me-2"></i>Friends</button>
                </div>
                <div class="friend-card position-relative">
                    <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&h=100&fit=crop&crop=face" alt="Friend">
                    <div class="online-status"></div>
                    <h6>Laura Smith</h6>
                    <button class="btn btn-success btn-sm"><i class="fas fa-user-friends me-2"></i>Friends</button>
                </div>
                <div class="friend-card position-relative">
                    <img src="https://images.unsplash.com/photo-1506794778202-d4c18e0b24c4?w=100&h=100&fit=crop&crop=face" alt="Friend">
                    <h6>James Lee</h6>
                    <button class="btn btn-success btn-sm"><i class="fas fa-user-friends me-2"></i>Friends</button>
                </div>
            </div>
        </div>
    </div>

    


<?php
include_once '../includes/footer2.php';
?>