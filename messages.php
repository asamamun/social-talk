<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'includes/header1.php';
include_once 'includes/db.php';

?>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            <!-- Conversations List -->
            <div class="col-lg-4">
                <div class="sidebar">
                    <h4 class="mb-4">Messages</h4>
                    <div class="list-group">
                        <div class="list-group-item list-group-item-action conversation-item" onclick="openChat('Sarah Johnson', this)">
                            <div class="d-flex align-items-center">
                                <div class="position-relative">
                                    <img src="https://images.unsplash.com/photo-1494790108755-2616b612b820?w=50&h=50&fit=crop&crop=face" class="profile-pic me-3" alt="Profile picture of Sarah Johnson">
                                    <div class="online-status"></div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">Sarah Johnson</h6>
                                        <small class="text-muted">2:30 PM</small>
                                    </div>
                                    <p class="mb-1 text-muted">Hey! How was your weekend?</p>
                                </div>
                                <span class="badge bg-primary rounded-pill">2</span>
                            </div>
                        </div>
                        <div class="list-group-item list-group-item-action conversation-item" onclick="openChat('Mike Chen', this)">
                            <div class="d-flex align-items-center">
                                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=50&h=50&fit=crop&crop=face" class="profile-pic me-3" alt="Profile picture of Mike Chen">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">Mike Chen</h6>
                                        <small class="text-muted">Yesterday</small>
                                    </div>
                                    <p class="mb-1 text-muted">Thanks for the advice!</p>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item list-group-item-action conversation-item" onclick="openChat('Emma Wilson', this)">
                            <div class="d-flex align-items-center">
                                <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=50&h=50&fit=crop&crop=face" class="profile-pic me-3" alt="Profile picture of Emma Wilson">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">Emma Wilson</h6>
                                        <small class="text-muted">Yesterday</small>
                                    </div>
                                    <p class="mb-1 text-muted">Let's catch up soon!</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Window -->
            <div class="col-lg-8">
                <div class="sidebar">
                    <div id="chatHeader" class="d-flex align-items-center mb-3 hidden">
                        <img src="" class="profile-pic me-3" id="chatProfilePic" alt="Profile picture">
                        <h5 class="mb-0" id="chatName"></h5>
                    </div>
                    <div class="chat-container" id="chatContainer">
                        <!-- Messages will be dynamically added here -->
                    </div>
                    <div class="input-group mt-3 hidden" id="messageInput">
                        <input type="text" class="form-control" placeholder="Type a message..." aria-label="Type a message">
                        <button class="btn btn-primary" onclick="sendMessage()">Send</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

   

<?php
include_once 'includes/footer1.php';
?>