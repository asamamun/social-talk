<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once '../includes/header3.php';
include_once '../includes/db.php';

?>


    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="post-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=50&h=50&fit=crop&crop=face" class="profile-pic me-3">
                            <div>
                                <h6 class="mb-0">Emma Wilson </h6>
                                <small class="text-muted">1 day ago</small>
                            </div>
                            <div class="ms-auto dropdown">
                                <button class="btn btn-sm" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#">Report</a></li>
                                    <li><a class="dropdown-item" href="#">Unfollow</a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <p>What a productive week! So many exciting projects underway. Loving the energy and collaboration with the team! Who else is feeling productive today? 💪 #worklife #motivation</p>
                        
                        <img src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?w=600&h=400&fit=crop" class="img-fluid rounded mb-3">
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="text-muted">38 likes • 15 comments</span>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <button class="btn btn-light flex-fill me-2 like-btn" onclick="toggleLike(this)">
                                <i class="fas fa-heart me-1"></i>Like
                            </button>
                            <button class="btn btn-light flex-fill me-2" onclick="toggleComments(this)">
                                <i class="fas fa-comment me-1"></i>Comment
                            </button>
                            <button class="btn btn-light flex-fill">
                                <i class="fas fa-share me-1"></i>Share
                            </button>
                        </div>
                        
                        <div class="comment-section"> <div class="mb-3">
                                <div class="d-flex mb-2">
                                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=30&h=30&fit=crop&crop=face" class="profile-pic me-2" style="width: 30px; height: 30px;">
                                    <div class="flex-grow-1">
                                        <strong>John Doe</strong>
                                        <p class="mb-1">That's awesome, Emma! Keep up the great work!</p>
                                        <small class="text-muted">5 hours ago</small>
                                    </div>
                                </div>
                                <div class="d-flex mb-2">
                                    <img src="https://images.unsplash.com/photo-1494790108755-2616b612b820?w=30&h=30&fit=crop&crop=face" class="profile-pic me-2" style="width: 30px; height: 30px;">
                                    <div class="flex-grow-1">
                                        <strong>Sarah Johnson</strong>
                                        <p class="mb-1">Feeling the same! Love a good productive week. 🙌</p>
                                        <small class="text-muted">3 hours ago</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex">
                                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=30&h=30&fit=crop&crop=face" class="profile-pic me-2" style="width: 30px; height: 30px;">
                                <input type="text" class="form-control" placeholder="Write a comment...">
                                <button class="btn btn-primary ms-2">Post</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    


<?php
include_once '../includes/footer3.php';
?>