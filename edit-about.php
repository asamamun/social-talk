<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/vendor/autoload.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

$db = new MysqliDb();

// Get current user data
$db->where("user_id", $_SESSION['user_id']);
$user = $db->getOne("users");

// Get user profile data
$db->where("user_id", $_SESSION['user_id']);
$profile = $db->getOne("user_profile");

// Get education history
$db->where("user_id", $_SESSION['user_id']);
$educations = $db->get("education");

// Get work history
$db->where("user_id", $_SESSION['user_id']);
$works = $db->get("work_history");

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_education'])) {
        // Add new education
        $data = [
            'user_id' => $_SESSION['user_id'],
            'institution_name' => $_POST['institution_name'],
            'degree' => $_POST['degree'],
            'field_of_study' => $_POST['field_of_study'],
            'location' => $_POST['location'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'description' => $_POST['description']
        ];
        $id = $db->insert('education', $data);
        if ($id) {
            $message = "Education added successfully";
            $_SESSION['message'] = $message;
         
            header("Location: edit-about.php");
            exit;
        }
    } elseif (isset($_POST['update_education'])) {
        // Update education
        $data = [
            'institution_name' => $_POST['institution_name'],
            'degree' => $_POST['degree'],
            'field_of_study' => $_POST['field_of_study'],
            'location' => $_POST['location'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'description' => $_POST['description']
        ];
        $db->where('education_id', $_POST['education_id']);
        $db->where('user_id', $_SESSION['user_id']);
        if ($db->update('education', $data)) {
            $message = "Education updated successfully";
            $_SESSION['message'] = $message;
            
            header("Location: edit-about.php");
            exit;
        }
    } elseif (isset($_POST['delete_education'])) {
        // Delete education
        $db->where('education_id', $_POST['education_id']);
        $db->where('user_id', $_SESSION['user_id']);
        if ($db->delete('education')) {
            $message = "Education deleted successfully";
            $_SESSION['message'] = $message;
          
            header("Location: edit-about.php");
            exit;
        }
    } elseif (isset($_POST['add_work'])) {
        // Add new work
        $data = [
            'user_id' => $_SESSION['user_id'],
            'company_name' => $_POST['company_name'],
            'job_title' => $_POST['job_title'],
            'location' => $_POST['location'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'description' => $_POST['description']
        ];
        $id = $db->insert('work_history', $data);
        if ($id) {
            $message = "Work experience added successfully";
            $_SESSION['message'] = $message;
            header("Location: edit-about.php");
            exit;
        }
    } elseif (isset($_POST['update_work'])) {
        // Update work
        $data = [
            'company_name' => $_POST['company_name'],
            'job_title' => $_POST['job_title'],
            'location' => $_POST['location'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'description' => $_POST['description']
        ];
        $db->where('work_id', $_POST['work_id']);
        $db->where('user_id', $_SESSION['user_id']);
        if ($db->update('work_history', $data)) {
            $message = "Work experience updated successfully";
            $_SESSION['message'] = $message;
           
            header("Location: edit-about.php");
            exit;
        }
    } elseif (isset($_POST['delete_work'])) {
        // Delete work
        $db->where('work_id', $_POST['work_id']);
        $db->where('user_id', $_SESSION['user_id']);
        if ($db->delete('work_history')) {
            $message = "Work experience deleted successfully";
            $_SESSION['message'] = $message;
           
            header("Location: edit-about.php");
            exit;
        }
    }
}

include_once 'includes/header1.php';
?>

<div class="container mt-4">
    <!-- Display messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message']['type'] ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['message']['text'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="profile-card mb-4">
                <h4 class="mb-4">Edit About Information</h4>
                
                <!-- Education Section -->
                <div class="mb-5">
                    <h5 class="mb-3">Education</h5>
                    
                    <!-- Education List -->
                    <?php foreach ($educations as $education): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="education_id" value="<?= $education['education_id'] ?>">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Institution Name</label>
                                            <input type="text" class="form-control" name="institution_name" value="<?= htmlspecialchars($education['institution_name']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Degree</label>
                                            <input type="text" class="form-control" name="degree" value="<?= htmlspecialchars($education['degree']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Field of Study</label>
                                            <input type="text" class="form-control" name="field_of_study" value="<?= htmlspecialchars($education['field_of_study']) ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Location</label>
                                            <input type="text" class="form-control" name="location" value="<?= htmlspecialchars($education['location']) ?>">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Start Date</label>
                                            <input type="date" class="form-control" name="start_date" value="<?= $education['start_date'] ?>" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">End Date</label>
                                            <input type="date" class="form-control" name="end_date" value="<?= $education['end_date'] ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($education['description']) ?></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" name="update_education" class="btn btn-primary me-2">Update</button>
                                        <button type="submit" name="delete_education" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this education?')">Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Add New Education Form -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Add New Education</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Institution Name</label>
                                        <input type="text" class="form-control" name="institution_name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Degree</label>
                                        <input type="text" class="form-control" name="degree" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Field of Study</label>
                                        <input type="text" class="form-control" name="field_of_study">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Location</label>
                                        <input type="text" class="form-control" name="location">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" class="form-control" name="start_date" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">End Date</label>
                                        <input type="date" class="form-control" name="end_date">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="3"></textarea>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" name="add_education" class="btn btn-success">Add Education</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Work Experience Section -->
                <div class="mb-5">
                    <h5 class="mb-3">Work Experience</h5>
                    
                    <!-- Work List -->
                    <?php foreach ($works as $work): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="work_id" value="<?= $work['work_id'] ?>">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Company Name</label>
                                            <input type="text" class="form-control" name="company_name" value="<?= htmlspecialchars($work['company_name']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Job Title</label>
                                            <input type="text" class="form-control" name="job_title" value="<?= htmlspecialchars($work['job_title']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Location</label>
                                            <input type="text" class="form-control" name="location" value="<?= htmlspecialchars($work['location']) ?>">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Start Date</label>
                                            <input type="date" class="form-control" name="start_date" value="<?= $work['start_date'] ?>" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">End Date</label>
                                            <input type="date" class="form-control" name="end_date" value="<?= $work['end_date'] ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($work['description']) ?></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" name="update_work" class="btn btn-primary me-2">Update</button>
                                        <button type="submit" name="delete_work" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this work experience?')">Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Add New Work Form -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Add New Work Experience</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Company Name</label>
                                        <input type="text" class="form-control" name="company_name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Job Title</label>
                                        <input type="text" class="form-control" name="job_title" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Location</label>
                                        <input type="text" class="form-control" name="location">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" class="form-control" name="start_date" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">End Date</label>
                                        <input type="date" class="form-control" name="end_date">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="3"></textarea>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" name="add_work" class="btn btn-success">Add Work Experience</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="user-profile-about.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once 'includes/footer1.php';
?>