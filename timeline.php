<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'includes/header1.php';
include_once 'includes/db.php';

?>

<div class="container mt-4">
    <!-- Profile Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="profileModalLabel">My Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <a href="user-profile.php">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=120&h=120&fit=crop&crop=face" class="profile-pic-lg mb-3" alt="John Doe profile">
                        </a>
                        <h4>John Doe</h4>
                        <p class="text-muted">Software Developer</p>
                        <button class="btn btn-primary" aria-label="Edit profile">Edit Profile</button>
                    </div>
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="border-end">
                                <h5>245</h5>
                                <small class="text-muted">Friends</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border-end">
                                <h5>89</h5>
                                <small class="text-muted">Posts</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h5>1.2K</h5>
                            <small class="text-muted">Likes</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Modal -->
    <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="settingsModalLabel">Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Privacy Settings</span>
                            <i class="fas fa-chevron-right"></i>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Notification Settings</span>
                            <i class="fas fa-chevron-right"></i>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Security</span>
                            <i class="fas fa-chevron-right"></i>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Theme Settings</span>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="darkModeSwitch" aria-label="Toggle dark mode">
                                <label class="form-check-label" for="darkModeSwitch">Dark Mode</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid mt-3">
        <div class="row" id="mainContent">
            <!-- Left Sidebar -->
            <div class="col-lg-3">
                <div class="sidebar fade-in">
                    <div class="text-center mb-4">
                        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=120&h=120&fit=crop&crop=face" class="profile-pic-lg mb-3" alt="John Doe profile">
                        <h5>John Doe</h5>
                        <p class="text-muted">Software Developer</p>
                        <div class="d-flex justify-content-around">
                            <div class="text-center">
                                <div class="fw-bold">245</div>
                                <small class="text-muted">Friends</small>
                            </div>
                            <div class="text-center">
                                <div class="fw-bold">89</div>
                                <small class="text-muted">Posts</small>
                            </div>
                        </div>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="timeline.php" class="list-group-item active" onclick="socialNet.showFeed()" aria-current="true">
                            <i class="fas fa-home me-2"></i>News Feed
                        </a>
                        <a href="user-profile/friend.php" class="list-group-item" onclick="socialNet.showFriends()">
                            <i class="fas fa-users me-2"></i>Friends
                        </a>
                        <a href="messages.php" class="list-group-item" onclick="socialNet.showMessages()">
                            <i class="fas fa-envelope me-2"></i>Messages
                            <span class="badge bg-primary text-dark">2</span>
                        </a>
                        <a href="friend-request.php" class="list-group-item" onclick="socialNet.showFriendRequests()">
                            <i class="fas fa-user-plus me-2"></i>Friend Requests
                            <span class="badge bg-success">3</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Feed -->
            <div class="col-lg-6">
                <!-- Create Post -->
                <div class="post-card p-4" id="feedSection">
                    <div class="d-flex align-items-center mb-3">
                        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=50&h=50&fit=crop&crop=face" class="profile-pic me-3">
                        <input type="text" class="form-control" placeholder="What's on your mind, John?" onclick="openCreatePost()">
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-light flex-fill me-2" onclick="openCreatePost()">
                            <i class="fas fa-edit me-2"></i>Post
                        </button>
                        <button class="btn btn-light flex-fill me-2" onclick="openImageUpload()">
                            <i class="fas fa-image me-2"></i>Photo
                        </button>
                        <button class="btn btn-light flex-fill">
                            <i class="fas fa-video me-2"></i>Video
                        </button>
                    </div>
                </div>

                <!-- Posts Feed -->
                <div id="postsContainer">
                    <div class="post-card fade-in position-relative">
                        <!-- Main clickable overlay -->
                        <a href="other-user-profile/other-user-post.php" class="stretched-link" aria-label="View full post"></a>
                        
                        <div class="card-body">
                            <!-- Post Header -->
                            <div class="d-flex align-items-center mb-3 position-relative">
                                <img src="https://images.unsplash.com/photo-1494790108755-2616b612b820?w=50&h=50&fit=crop&crop=face" class="profile-pic me-3" alt="Sarah Johnson profile">
                                <div>
                                    <a href="other-user-profile/other-user-profile.php" class="position-relative" style="z-index: 2;">
                                        <h6 class="mb-0">Sarah Johnson</h6>
                                    </a>
                                    <small class="text-muted">2 hours ago</small>
                                </div>
                                <div class="ms-auto dropdown" style="z-index: 2;">
                                    <button class="btn btn-sm" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-h" aria-hidden="true"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="#">Save Post</a>
                                        <a class="dropdown-item" href="#">Report Post</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Post Content -->
                            <p class="card-text">Just finished my morning hike! The view was absolutely breathtaking. Can't wait to do it again next weekend. 🏞️ #nature #hiking</p>
                            <img src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" class="img-fluid rounded mb-3" alt="Hiking view">

                            <!-- Post Stats -->
                            <div class="d-flex justify-content-between mb-2 position-relative" style="z-index: 1;">
                                <div>
                                    <span class="me-2"><i class="fas fa-thumbs-up me-1"></i> 24</span>
                                    <span><i class="fas fa-comment me-1"></i> 8</span>
                                </div>
                                <div>
                                    <span class="text-muted">12 shares</span>
                                </div>
                            </div>

                            <!-- Post Actions -->
                            <div class="d-flex border-top border-bottom py-2 mb-3 position-relative" style="z-index: 2;">
                                <button class="btn btn-light flex-fill me-2 like-btn" onclick="socialNet.toggleLike(this)" aria-label="Like post">
                                    <i class="fas fa-thumbs-up me-2"></i>Like
                                </button>
                                <button class="btn btn-light flex-fill me-2" onclick="socialNet.focusComment(this)" aria-label="Comment on post">
                                    <i class="fas fa-comment me-2"></i>Comment
                                </button>
                                <button class="btn btn-light flex-fill" onclick="socialNet.sharePost(this)" aria-label="Share post">
                                    <i class="fas fa-share me-2"></i>Share
                                </button>
                            </div>

                            <!-- Comment Section -->
                            <div class="comment-section position-relative" style="z-index: 2;">
                                <div class="d-flex mb-3">
                                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face" class="profile-pic me-2" style="width: 40px; height: 40px;" alt="Profile picture">
                                    <div class="flex-grow-1">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Write a comment..." aria-label="Write a comment">
                                            <button class="btn btn-outline-primary" aria-label="Post comment">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex mb-3">
                                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=40&h=40&fit=crop&crop=face" class="profile-pic me-2" style="width: 40px; height: 40px;" alt="Mike Chen profile">
                                    <div>
                                        <div class="bg-light p-3 rounded">
                                            <strong>Mike Chen</strong>
                                            <p class="mb-1">Looks amazing! Where is this?</p>
                                            <small class="text-muted">1 hour ago</small>
                                        </div>
                                        <div class="d-flex mt-2">
                                            <a href="#" class="text-decoration-none me-3">Like</a>
                                            <a href="#" class="text-decoration-none">Reply</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Second Post -->
                    <div class="post-card fade-in position-relative">
                        <a href="post.php" class="stretched-link" aria-label="View full post"></a>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3 position-relative">
                                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=50&h=50&fit=crop&crop=face" class="profile-pic me-3" alt="Mike Chen profile">
                                <div>
                                    <h6 class="mb-0">Mike Chen</h6>
                                    <small class="text-muted">Yesterday</small>
                                </div>
                                <div class="ms-auto dropdown" style="z-index: 2;">
                                    <button class="btn btn-sm" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-h" aria-hidden="true"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="#">Save Post</a>
                                        <a class="dropdown-item" href="#">Report Post</a>
                                    </div>
                                </div>
                            </div>
                            <p class="card-text">Just launched my new website! Check it out and let me know what you think. Big thanks to everyone who supported me through this journey. 🙏</p>
                            <div class="bg-light p-3 rounded mb-3">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <img src="https://via.placeholder.com/80" class="rounded" width="80" alt="Portfolio preview">
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Mike's Portfolio</h6>
                                        <p class="text-muted small mb-1">mikechen.dev</p>
                                        <p class="small">Personal portfolio showcasing my latest projects and design work.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mb-2 position-relative" style="z-index: 1;">
                                <div>
                                    <span class="me-2"><i class="fas fa-thumbs-up me-1"></i> 42</span>
                                    <span><i class="fas fa-comment me-1"></i> 15</span>
                                </div>
                                <div>
                                    <span class="text-muted">7 shares</span>
                                </div>
                            </div>
                            <div class="d-flex border-top border-bottom py-2 mb-3 position-relative" style="z-index: 2;">
                                <button class="btn btn-light flex-fill me-2 like-btn" onclick="socialNet.toggleLike(this)" aria-label="Like post">
                                    <i class="fas fa-thumbs-up me-2"></i>Like
                                </button>
                                <button class="btn btn-light flex-fill me-2" onclick="socialNet.focusComment(this)" aria-label="Comment on post">
                                    <i class="fas fa-comment me-2"></i>Comment
                                </button>
                                <button class="btn btn-light flex-fill" onclick="socialNet.sharePost(this)" aria-label="Share post">
                                    <i class="fas fa-share me-2"></i>Share
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="col-lg-3">
                <div class="sidebar">
                    <h6 class="mb-3">Friend Suggestions</h6>
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=40&h=40&fit=crop&crop=face" class="profile-pic me-2" style="width: 40px; height: 40px;" alt="Alex Rodriguez profile">
                            <div class="flex-grow-1">
                                <h6 class="mb-0" style="font-size: 0.9em;">Alex Rodriguez</h6>
                                <small class="text-muted">2 mutual friends</small>
                            </div>
                            <button class="btn btn-primary btn-sm" onclick="socialNet.sendFriendRequest(this)" aria-label="Add Alex Rodriguez as friend">
                                <i class="fas fa-user-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <img src="https://images.unsplash.com/photo-1544725176-7c40e5a71c5e?w=40&h=40&fit=crop&crop=face" class="profile-pic me-2" style="width: 40px; height: 40px;" alt="Lisa Park profile">
                            <div class="flex-grow-1">
                                <h6 class="mb-0" style="font-size: 0.9em;">Lisa Park</h6>
                                <small class="text-muted">5 mutual friends</small>
                            </div>
                            <button class="btn btn-primary btn-sm" onclick="socialNet.sendFriendRequest(this)" aria-label="Add Lisa Park as friend">
                                <i class="fas fa-user-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="sidebar fade-in">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Upcoming Events</h5>
                        <a href="#" class="text-decoration-none">See All</a>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded p-2 me-3 text-center" style="width: 40px;">
                                    <div class="fw-bold">15</div>
                                    <small>JUN</small>
                                </div>
                                <div>
                                    <h6 class="mb-0">Tech Conference</h6>
                                    <small class="text-muted">San Francisco</small>
                                </div>
                            </div>
                        </a>
                        <a href="#" class="list-group-item">
                            <div class="d-flex align-items-center">
                                <div class="bg-success text-white rounded p-2 me-3 text-center" style="width: 40px;">
                                    <div class="fw-bold">22</div>
                                    <small>JUN</small>
                                </div>
                                <div>
                                    <h6 class="mb-0">Birthday Party</h6>
                                    <small class="text-muted">Sarah's House</small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="sidebar fade-in">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Suggested Groups</h5>
                        <a href="#" class="text-decoration-none">See All</a>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item">
                            <div class="d-flex align-items-center">
                                <img src="https://via.placeholder.com/40" class="rounded me-3" width="40" alt="Web Developers group">
                                <div>
                                    <h6 class="mb-0">Web Developers</h6>
                                    <small class="text-muted">245K members</small>
                                </div>
                            </div>
                        </a>
                        <a href="#" class="list-group-item">
                            <div class="d-flex align-items-center">
                                <img src="https://via.placeholder.com/40" class="rounded me-3" width="40" alt="Photography Enthusiasts group">
                                <div>
                                    <h6 class="mb-0">Photography Enthusiasts</h6>
                                    <small class="text-muted">189K members</small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>


<?php
include_once 'includes/footer1.php';
?>