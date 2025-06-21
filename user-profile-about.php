<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'includes/header1.php';
include_once 'includes/db.php';

?>


<div class="container mt-4">
    <!-- Profile Header -->
    <div class="profile-header">
        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=180&h=180&fit=crop&crop=face" class="profile-pic-xl" alt="John Doe">
        <div class="profile-actions">
            <a href="edit-about.php" class="btn btn-primary">
                <i class="fas fa-pencil-alt me-2"></i>Edit Profile
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-4">
            <!-- About Card -->
            <div class="profile-card">
                <h4 class="mb-4">About</h4>
                <div class="info-item d-flex">
                    <i class="fas fa-briefcase mt-1"></i>
                    <div>
                        <h6 class="mb-0">Works at</h6>
                        <p class="text-muted">Tech Solutions Inc.</p>
                    </div>
                </div>
                <div class="info-item d-flex">
                    <i class="fas fa-graduation-cap mt-1"></i>
                    <div>
                        <h6 class="mb-0">Studied at</h6>
                        <p class="text-muted">Stanford University</p>
                    </div>
                </div>
                <div class="info-item d-flex">
                    <i class="fas fa-home mt-1"></i>
                    <div>
                        <h6 class="mb-0">Lives in</h6>
                        <p class="text-muted">San Francisco, CA</p>
                    </div>
                </div>
                <div class="info-item d-flex">
                    <i class="fas fa-map-marker-alt mt-1"></i>
                    <div>
                        <h6 class="mb-0">From</h6>
                        <p class="text-muted">New York, NY</p>
                    </div>
                </div>
                <div class="info-item d-flex">
                    <i class="fas fa-heart mt-1"></i>
                    <div>
                        <h6 class="mb-0">Relationship</h6>
                        <p class="text-muted">In a relationship</p>
                    </div>
                </div>
                <div class="info-item d-flex">
                    <i class="fas fa-calendar-alt mt-1"></i>
                    <div>
                        <h6 class="mb-0">Joined</h6>
                        <p class="text-muted">January 2018</p>
                    </div>
                </div>
                <a href="edit-about.php" class="btn btn-primary btn-sm px-3 py-2 mt-3" style="font-size: 0.9rem;">
                    <i class="fas fa-edit"></i> Edit About
                </a>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-8">
            <!-- Profile Navigation -->
            <ul class="nav profile-nav mb-4">
                <li class="nav-item">
                    <a class="nav-link" href="user-profile.php" data-section="posts">Posts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="user-profile-about.php" data-section="about">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="friend.php" data-section="friends">Friends</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="photo.php" data-section="photos">Photos</a>
                </li>
                
            </ul>

            <!-- About Section -->
            <div class="profile-card">
                <h4 class="mb-4">About John Doe</h4>
                <p>Hi, I'm John Doe, a software developer at Tech Solutions Inc. I graduated from Stanford University with a degree in Computer Science. Originally from New York, I now live in San Francisco. I love hiking, photography, and building innovative projects in my free time.</p>
                
                <h5 class="mt-5 mb-3">Work and Education</h5>
                <div class="info-item d-flex">
                    <i class="fas fa-briefcase mt-1"></i>
                    <div>
                        <h6 class="mb-0">Works at</h6>
                        <p class="text-muted">Tech Solutions Inc. (2018 - Present)</p>
                        <p class="text-muted">Senior Software Developer</p>
                    </div>
                </div>
                <div class="info-item d-flex">
                    <i class="fas fa-briefcase mt-1"></i>
                    <div>
                        <h6 class="mb-0">Worked at</h6>
                        <p class="text-muted">Digital Innovations Corp. (2015 - 2018)</p>
                        <p class="text-muted">Software Engineer</p>
                    </div>
                </div>
                <div class="info-item d-flex">
                    <i class="fas fa-graduation-cap mt-1"></i>
                    <div>
                        <h6 class="mb-0">Studied at</h6>
                        <p class="text-muted">Stanford University (2011 - 2015)</p>
                        <p class="text-muted">B.S. Computer Science</p>
                    </div>
                </div>

                <h5 class="mt-5 mb-3">Places Lived</h5>
                <div class="info-item d-flex">
                    <i class="fas fa-home mt-1"></i>
                    <div>
                        <h6 class="mb-0">Current City</h6>
                        <p class="text-muted">San Francisco, California</p>
                        <p class="text-muted">2018 - Present</p>
                    </div>
                </div>
                <div class="info-item d-flex">
                    <i class="fas fa-map-marker-alt mt-1"></i>
                    <div>
                        <h6 class="mb-0">Hometown</h6>
                        <p class="text-muted">New York, New York</p>
                    </div>
                </div>

                <h5 class="mt-5 mb-3">Contact Information</h5>
                <div class="info-item d-flex">
                    <i class="fas fa-envelope mt-1"></i>
                    <div>
                        <h6 class="mb-0">Email</h6>
                        <p class="text-muted">john.doe@example.com</p>
                    </div>
                </div>
                <div class="info-item d-flex">
                    <i class="fas fa-phone mt-1"></i>
                    <div>
                        <h6 class="mb-0">Mobile</h6>
                        <p class="text-muted">+1 (555) 123-4567</p>
                    </div>
                </div>

                <h5 class="mt-5 mb-3">Basic Information</h5>
                <div class="info-item d-flex">
                    <i class="fas fa-birthday-cake mt-1"></i>
                    <div>
                        <h6 class="mb-0">Birthday</h6>
                        <p class="text-muted">June 15, 1993</p>
                    </div>
                </div>
                <div class="info-item d-flex">
                    <i class="fas fa-heart mt-1"></i>
                    <div>
                        <h6 class="mb-0">Relationship</h6>
                        <p class="text-muted">In a relationship</p>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="edit-about.php" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit About Info
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>



<?php
include_once 'includes/footer1.php';
?>