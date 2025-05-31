<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once '../includes/header3.php';
include_once '../includes/db.php';

?>

    <div class="container mt-4">
        <!-- Profile Header -->
        <div class="profile-header">
            <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=180&h=180&fit=crop&crop=face" class="profile-pic-xl" alt="Jane Smith">
           <div class="profile-actions">
  <button class="btn btn-primary me-2"><i class="fas fa-user-plus me-2"></i>Add Friend</button>
  <button class="btn btn-outline-primary me-2"><i class="fas fa-user-check me-2"></i>Follow</button>
  <button class="btn btn-success"><i class="fas fa-envelope me-2"></i>Message</button>
</div>

        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-4">
                <!-- About Card -->
                <div class="profile-card">
                    <h4>About</h4>
                    <p><i class="fas fa-briefcase me-2"></i>UX Designer at Creative Studio</p>
                    <p><i class="fas fa-graduation-cap me-2"></i>Graduated from UCLA</p>
                    <p><i class="fas fa-home me-2"></i>Lives in Los Angeles, CA</p>
                    <p><i class="fas fa-map-marker-alt me-2"></i>From Seattle, WA</p>
                    <p><i class="fas fa-heart me-2"></i>Single</p>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-8">
               <ul class="nav profile-nav mb-4">
  <li class="nav-item"><a class="nav-link" href="other-user-profile.php">Posts</a></li>
    <li class="nav-item"><a class="nav-link active" href="other-user-about.php">About</a></li>
    <li class="nav-item"><a class="nav-link" href="other-user-photo.php">Photos</a></li>
</ul>

                

                <!-- Sample Post -->
                <div class="post-card">
                    <div class="d-flex align-items-center mb-3">
                        <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=50&h=50&fit=crop&crop=face" class="profile-pic me-3">
                        <div>
                            <h6 class="mb-0">Jane Smith</h6>
                            <small class="text-muted">1 hour ago</small>
                        </div>
                    </div>
                    <p>Just wrapped up an incredible design sprint! Excited to share what we built soon 🚀</p>
                    <img src="https://images.unsplash.com/photo-1581090700227-1e8dced06f3d?w=600&h=400&fit=crop" class="img-fluid rounded mb-3">
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-light flex-fill me-2"><i class="fas fa-heart me-1"></i>Like</button>
                        <button class="btn btn-light flex-fill me-2"><i class="fas fa-comment me-1"></i>Comment</button>
                        <button class="btn btn-light flex-fill"><i class="fas fa-share me-1"></i>Share</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

<?php
include_once '../includes/footer3.php';
?>