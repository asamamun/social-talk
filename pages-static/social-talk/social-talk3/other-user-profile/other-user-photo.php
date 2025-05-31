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

    <!-- Navigation Tabs -->
            <div class="col-lg-8">
               <ul class="nav profile-nav mb-4">
    <li class="nav-item"><a class="nav-link" href="other-user-profile.php">Posts</a></li>
    <li class="nav-item"><a class="nav-link active" href="other-user-about.php">About</a></li>
    <li class="nav-item"><a class="nav-link" href="other-user-photo.php">Photos</a></li>
</ul>

    <!-- Photo Grid -->
    <div class="profile-card">
      <h4 class="mb-4">Jane's Photos</h4>
      <div class="photo-grid">
        <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=300&h=300&fit=crop" alt="Photo">
        <img src="https://images.unsplash.com/photo-1452421822248-d4c2b47f0c81?w=300&h=300&fit=crop" alt="Photo">
        <img src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=300&h=300&fit=crop" alt="Photo">
        <img src="https://images.unsplash.com/photo-1470114716159-e389f8712fda?w=300&h=300&fit=crop" alt="Photo">
        <img src="https://images.unsplash.com/photo-1472214103451-9374bd1c798e?w=300&h=300&fit=crop" alt="Photo">
        <img src="https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=300&h=300&fit=crop" alt="Photo">
      </div>
    </div>
  </div>

 

<?php
include_once '../includes/footer3.php';
?>