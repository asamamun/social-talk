<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../vendor/autoload.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit;
}
if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}
$db = new MysqliDb();

// Fetch dashboard stats
$totalUsers = $db->getValue("users", "count(*)");
$activePosts = $db->getValue("posts", "count(*)");
$pendingReports = $db->getValue("reports", "count(*)");
$bannedUsers = $db->where('status', 'banned')->getValue("users", "count(*)");

// Fetch data for charts
$userActivity = $db->rawQuery("SELECT DATE(created_at) as date, COUNT(*) as count FROM users WHERE created_at >= CURDATE() - INTERVAL 7 DAY GROUP BY DATE(created_at)");
$postActivity = $db->rawQuery("SELECT DATE(created_at) as date, COUNT(*) as count FROM posts WHERE created_at >= CURDATE() - INTERVAL 7 DAY GROUP BY DATE(created_at)");

$userDistribution = $db->rawQuery("SELECT status, COUNT(*) as count FROM users GROUP BY status");

// Fetch recent notifications
$recentNotifications = $db->rawQuery("SELECT n.*, u.username, up.profile_picture FROM notifications n JOIN users u ON n.user_id = u.user_id LEFT JOIN user_profile up ON u.user_id = up.user_id ORDER BY n.created_at DESC LIMIT 5");


include('header.php');
?>

<!-- Dashboard Section -->
<div id="dashboard-section">
    <h2 class="mb-4">Dashboard Overview</h2>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted">Total Users</h6>
                            <h3><?php echo $totalUsers; ?></h3>
                        </div>
                        <div class="stat-icon text-primary">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted">Active Posts</h6>
                            <h3><?php echo $activePosts; ?></h3>
                        </div>
                        <div class="stat-icon text-success">
                            <i class="fas fa-newspaper"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted">Pending Reports</h6>
                            <h3><?php echo $pendingReports; ?></h3>
                        </div>
                        <div class="stat-icon text-warning">
                            <i class="fas fa-flag"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted">Banned Users</h6>
                            <h3><?php echo $bannedUsers; ?></h3>
                        </div>
                        <div class="stat-icon text-danger">
                            <i class="fas fa-user-slash"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <span>User Activity (Last 7 Days)</span>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <span>User Distribution</span>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="userDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Recent Activity</span>
                        <a href="all-user.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Activity</th>
                                    <th>Time</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentNotifications as $notification): ?>
                                <tr>
                                    <td>
                                        <img src="../<?php echo $notification['profile_picture'] ?? 'assets/images/default-profile.png'; ?>" class="user-avatar me-2">
                                        <?php echo htmlspecialchars($notification['username']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $notification['type']))); ?></td>
                                    <td><?php echo htmlspecialchars($notification['created_at']); ?></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // User Activity Chart
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    const activityChart = new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($userActivity, 'date')); ?>,
            datasets: [{
                label: 'User Signups',
                data: <?php echo json_encode(array_column($userActivity, 'count')); ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Posts Created',
                data: <?php echo json_encode(array_column($postActivity, 'count')); ?>,
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // User Distribution Chart
    const userDistributionCtx = document.getElementById('userDistributionChart').getContext('2d');
    const userDistributionChart = new Chart(userDistributionCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($userDistribution, 'status')); ?>,
            datasets: [{
                label: 'User Status',
                data: <?php echo json_encode(array_column($userDistribution, 'count')); ?>,
                backgroundColor: [
                    '#0d6efd',
                    '#dc3545',
                    '#ffc107',
                    '#6c757d'
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>
