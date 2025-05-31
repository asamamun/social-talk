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
    </div>

           <div class="col-lg-8">
               <ul class="nav profile-nav mb-4">
<li class="nav-item"><a class="nav-link" href="other-user-profile.php">Posts</a></li>
    <li class="nav-item"><a class="nav-link active" href="other-user-about.php">About</a></li>
    <li class="nav-item"><a class="nav-link" href="other-user-photo.php">Photos</a></li>
</ul>

    <!-- About Info Section -->
    <div class="row">
      <div class="col-lg-8 offset-lg-2">
        <div class="profile-card">
          <h4>About Jane Smith</h4>

          <div class="info-item d-flex">
            <i class="fas fa-briefcase mt-1"></i>
            <div class="ms-3">
              <h6 class="mb-0">Works at</h6>
              <p class="text-muted mb-0">Creative Studio</p>
            </div>
          </div>

          <div class="info-item d-flex">
            <i class="fas fa-graduation-cap mt-1"></i>
            <div class="ms-3">
              <h6 class="mb-0">Studied at</h6>
              <p class="text-muted mb-0">UCLA</p>
            </div>
          </div>

          <div class="info-item d-flex">
            <i class="fas fa-home mt-1"></i>
            <div class="ms-3">
              <h6 class="mb-0">Lives in</h6>
              <p class="text-muted mb-0">Los Angeles, CA</p>
            </div>
          </div>

          <div class="info-item d-flex">
            <i class="fas fa-map-marker-alt mt-1"></i>
            <div class="ms-3">
              <h6 class="mb-0">From</h6>
              <p class="text-muted mb-0">Seattle, WA</p>
            </div>
          </div>

          <div class="info-item d-flex">
            <i class="fas fa-heart mt-1"></i>
            <div class="ms-3">
              <h6 class="mb-0">Relationship</h6>
              <p class="text-muted mb-0">Single</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>



<?php
include_once '../includes/footer3.php';
?>