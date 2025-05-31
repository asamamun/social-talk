<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once '../includes/header2.php';
include_once '../includes/db.php';

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
   
        <a href="edit-about.php" class="btn btn-primary btn-sm px-3 py-2" style="font-size: 0.9rem;">
            <i class="fas fa-edit"></i> Edit About
        </a>
    </h4>
                </div>

                <!-- Photos Card -->
                <div class="profile-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">Photos</h4>
                        <a href="photo.php" class="text-primary" data-section="photos">See All</a>
                    </div>
                    <div class="photo-grid">
                        <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=300&h=300&fit=crop" alt="Photo 1">
                        <img src="https://images.unsplash.com/photo-1452421822248-d4c2b47f0c81?w=300&h=300&fit=crop" alt="Photo 2">
                        <img src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=300&h=300&fit=crop" alt="Photo 3">
                        <img src="https://images.unsplash.com/photo-1470114716159-e389f8712fda?w=300&h=300&fit=crop" alt="Photo 4">
                        <img src="https://images.unsplash.com/photo-1472214103451-9374bd1c798e?w=300&h=300&fit=crop" alt="Photo 5">
                        <img src="https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=300&h=300&fit=crop" alt="Photo 6">
                    </div>
                </div>

                <!-- Friends Card -->
                <div class="profile-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">Friends</h4>
                        <a href="friend.php" class="text-primary" data-section="friends">See All</a>
                    </div>
                    <p class="text-muted mb-4">245 friends</p>
                    <div class="row">
                        <div class="col-6 friend-card">
                            <img src="https://images.unsplash.com/photo-1494790108755-2616b612b820?w=100&h=100&fit=crop&crop=face" alt="Sarah Johnson">
                            <h6>Sarah Johnson</h6>
                            <!-- <div class="online-status"></div> -->
                        </div>
                        <div class="col-6 friend-card">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=face" alt="Mike Chen">
                            <h6>Mike Chen</h6>
                        </div>
                        <div class="col-6 friend-card">
                            <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop&crop=face" alt="Emma Wilson">
                            <h6>Emma Wilson</h6>
                            <!-- <div class="online-status"></div> -->
                        </div>
                        <div class="col-6 friend-card">
                            <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop&crop=face" alt="Alex Rodriguez">
                            <h6>Alex Rodriguez</h6>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-8">
                <!-- Profile Navigation -->
                <ul class="nav profile-nav mb-4">
                    <li class="nav-item">
                        <a class="nav-link active" href="#posts" data-section="posts">Posts</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user-profile-about.php" data-section="about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="friend.php" data-section="friends">Friends</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="photo.php" data-section="photos">Photos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="video.php" data-section="videos">Videos</a>
                    </li>
                </ul>

                <!-- Dynamic Content Area -->
                <div id="content-area">
                    <!-- Create Post -->
                    <div class="post-card p-4 mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=50&h=50&fit=crop&crop=face" class="profile-pic me-3" alt="John Doe">
                            <input type="text" class="form-control" id="post-input" placeholder="What's on your mind, John?" aria-label="Create a post">
                        </div>
                        <div class="d-flex justify-content-between">
                            <button class="btn btn-light flex-fill me-2" onclick="createPost('text')">
                                <i class="fas fa-edit me-2"></i>Post
                            </button>
                            <button class="btn btn-light flex-fill me-2" onclick="createPost('photo')">
                                <i class="fas fa-image me-2"></i>Photo
                            </button>
                            <button class="btn btn-light flex-fill" onclick="createPost('video')">
                                <i class="fas fa-video me-2"></i>Video
                            </button>
                        </div>
                    </div>
                       <!-- Create Post Modal -->
        <div class="modal fade" id="createPostModal" tabindex="-1" aria-labelledby="createPostModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createPostModalLabel">Create Post</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=50&h=50&fit=crop&crop=face" class="profile-pic me-3" alt="John Doe profile">
                            <div>
                                <h6 class="mb-0">John Doe</h6>
                            </div>
                        </div>
                        <textarea class="form-control mb-3" rows="4" placeholder="What's on your mind?" id="postContent" aria-label="Post content"></textarea>
                        <div class="d-flex justify-content-between">
                            <div>
                                <button class="btn btn-light me-2" onclick="socialNet.openImageUpload()" aria-label="Upload photo">
                                    <i class="fas fa-image me-1"></i>Photo
                                </button>
                                <button class="btn btn-light" aria-label="Upload video">
                                    <i class="fas fa-video me-1"></i>Video
                                </button>
                            </div>
                            <button class="btn btn-primary" onclick="socialNet.createPost()">Post</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

                    <!-- Posts Section -->
                    <div id="posts-section">
                        <div class="post-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=50&h=50&fit=crop&crop=face" class="profile-pic me-3" alt="John Doe">
                                    <div>
                                        <h6 class="mb-0">John Doe</h6>
                                        <small class="text-muted">Yesterday at 5:30 PM</small>
                                    </div>
                                    <div class="ms-auto dropdown">
                                        <button class="btn btn-sm" data-bs-toggle="dropdown" aria-label="Post options">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="editPost(this)">Edit</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="deletePost(this)">Delete</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <p>Just launched a new project I've been working on for months! So excited to share it with everyone. Check it out and let me know what you think!</p>
                                <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=600&h=400&fit=crop" class="img-fluid rounded mb-3" alt="Project launch">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <span class="text-muted">42 likes • 15 comments</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <button class="btn btn-light flex-fill me-2" onclick="toggleLike(this)">
                                        <i class="fas fa-heart me-1"></i>Like
                                    </button>
                                    <button class="btn btn-light flex-fill me-2" onclick="addComment(this)">
                                        <i class="fas fa-comment me-1"></i>Comment
                                    </button>
                                    <button class="btn btn-light flex-fill" onclick="sharePost(this)">
                                        <i class="fas fa-share me-1"></i>Share
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="post-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=50&h=50&fit=crop&crop=face" class="profile-pic me-3" alt="John Doe">
                                    <div>
                                        <h6 class="mb-0">John Doe</h6>
                                        <small class="text-muted">3 days ago</small>
                                    </div>
                                    <div class="ms-auto dropdown">
                                        <button class="btn btn-sm" data-bs-toggle="dropdown" aria-label="Post options">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="editPost(this)">Edit</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="deletePost(this)">Delete</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <p>Happy birthday to my amazing partner! So grateful to have you in my life. Here's to many more years together! ❤️</p>
                                <img src="https://images.unsplash.com/photo-1516589178581-6cd7833ae3b2?w=600&h=400&fit=crop" class="img-fluid rounded mb-3" alt="Birthday celebration">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <span class="text-muted">128 likes • 24 comments</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <button class="btn btn-light flex-fill me-2" onclick="toggleLike(this)">
                                        <i class="fas fa-heart me-1"></i>Like
                                    </button>
                                    <button class="btn btn-light flex-fill me-2" onclick="addComment(this)">
                                        <i class="fas fa-comment me-1"></i>Comment
                                    </button>
                                    <button class="btn btn-light flex-fill" onclick="sharePost(this)">
                                        <i class="fas fa-share me-1"></i>Share
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- About Section (Hidden by Default) -->
                    <div id="about-section" class="d-none">
                        <div class="profile-card">
                            <h4 class="mb-4">About John Doe</h4>
                            <p>Hi, I'm John Doe, a software developer at Tech Solutions Inc. I graduated from Stanford University with a degree in Computer Science. Originally from New York, I now live in San Francisco. I love hiking, photography, and building innovative projects in my free time.</p>
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
                        </div>
                    </div>

                    <!-- Friends Section (Hidden by Default) -->
                    <div id="friends-section" class="d-none">
                        <div class="profile-card">
                            <h4 class="mb-4">Friends (245)</h4>
                            <div class="row">
                                <div class="col-6 friend-card">
                                    <img src="https://images.unsplash.com/photo-1494790108755-2616b612b820?w=100&h=100&fit=crop&crop=face" alt="Sarah Johnson">
                                    <h6>Sarah Johnson</h6>
                                    <div class="online-status"></div>
                                </div>
                                <div class="col-6 friend-card">
                                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=face" alt="Mike Chen">
                                    <h6>Mike Chen</h6>
                                </div>
                                <div class="col-6 friend-card">
                                    <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop&crop=face" alt="Emma Wilson">
                                    <h6>Emma Wilson</h6>
                                    <div class="online-status"></div>
                                </div>
                                <div class="col-6 friend-card">
                                    <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop&crop=face" alt="Alex Rodriguez">
                                    <h6>Alex Rodriguez</h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Photos Section (Hidden by Default) -->
                    <div id="photos-section" class="d-none">
                        <div class="profile-card">
                            <h4 class="mb-4">Photos</h4>
                            <div class="photo-grid">
                                <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=300&h=300&fit=crop" alt="Photo 1">
                                <img src="https://images.unsplash.com/photo-1452421822248-d4c2b47f0c81?w=300&h=300&fit=crop" alt="Photo 2">
                                <img src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=300&h=300&fit=crop" alt="Photo 3">
                                <img src="https://images.unsplash.com/photo-1470114716159-e389f8712fda?w=300&h=300&fit=crop" alt="Photo 4">
                                <img src="https://images.unsplash.com/photo-1472214103451-9374bd1c798e?w=300&h=300&fit=crop" alt="Photo 5">
                                <img src="https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=300&h=300&fit=crop" alt="Photo 6">
                            </div>
                        </div>
                    </div>

                    <!-- Videos Section (Hidden by Default) -->
                    <div id="videos-section" class="d-none">
                        <div class="profile-card">
                            <h4 class="mb-4">Videos</h4>
                            <p class="text-muted">No videos available yet.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

<?php
include_once '../includes/footer2.php';
?>