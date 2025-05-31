<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'includes/header1.php';
include_once 'includes/db.php';

?>


    <!-- Photos Section -->
    <div class="container mt-4">
        <div class="photo-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">John Doe's videos</h4>
                <div>
                    <button class="btn btn-primary me-2"><i class="fas fa-plus me-2"></i>Upload video</button>
                    <a href="user-profile.php" class="btn btn-light"><i class="fas fa-arrow-left me-2"></i>Back to Profile</a>
                </div>
            </div>
            <div class="photo-grid">
                <video src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=300&h=300&fit=crop" alt="video">
                <video src="https://images.unsplash.com/photo-1452421822248-d4c2b47f0c81?w=300&h=300&fit=crop" alt="video">
                <video src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=300&h=300&fit=crop" alt="video">
                <video src="https://images.unsplash.com/photo-1470114716159-e389f8712fda?w=300&h=300&fit=crop" alt="video">
                <video src="https://images.unsplash.com/photo-1472214103451-9374bd1c798e?w=300&h=300&fit=crop" alt="video">
                <video src="https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=300&h=300&fit=crop" alt="video">
                <video src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=300&h=300&fit=crop" alt="video">
                <video src="https://images.unsplash.com/photo-1519681393784-d120267933ba?w=300&h=300&fit=crop" alt="video">
                <video src="https://images.unsplash.com/photo-1490730141103-6cac27aaab94?w=300&h=300&fit=crop" alt="video">
                </div>
        </div>
    </div>



<?php
include_once 'includes/footer1.php';
?>