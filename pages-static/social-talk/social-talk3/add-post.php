<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'includes/header1.php';
include_once 'includes/db.php';

?>
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

  

<?php
include_once 'includes/footer1.php';
?>