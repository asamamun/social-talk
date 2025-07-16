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

// Fetch data for charts
$engagement = $db->rawQuery("SELECT DATE(created_at) as date, COUNT(*) as count FROM likes GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 7");
$comments = $db->rawQuery("SELECT DATE(created_at) as date, COUNT(*) as count FROM comments GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 7");

$contentTypes = $db->rawQuery("SELECT (CASE WHEN images IS NOT NULL AND images != '' THEN 'image' ELSE 'text' END) as type, COUNT(*) as count FROM posts GROUP BY type");

include('header.php');
?>


<!-- Analytics Section -->
<div id="analytics-section">
    <h2 class="mb-4">Analytics</h2>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <span>Engagement Metrics (Last 7 Days)</span>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="engagementChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <span>Content Types</span>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="contentTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Engagement Chart
    const engagementCtx = document.getElementById('engagementChart').getContext('2d');
    const engagementChart = new Chart(engagementCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($engagement, 'date')); ?>,
            datasets: [{
                label: 'Likes',
                data: <?php echo json_encode(array_column($engagement, 'count')); ?>,
                backgroundColor: 'rgba(13, 110, 253, 0.5)',
                borderColor: '#0d6efd',
                borderWidth: 1
            }, {
                label: 'Comments',
                data: <?php echo json_encode(array_column($comments, 'count')); ?>,
                backgroundColor: 'rgba(25, 135, 84, 0.5)',
                borderColor: '#198754',
                borderWidth: 1
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

    // Content Type Chart
    const contentTypeCtx = document.getElementById('contentTypeChart').getContext('2d');
    const contentTypeChart = new Chart(contentTypeCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_column($contentTypes, 'type')); ?>,
            datasets: [{
                label: 'Content Types',
                data: <?php echo json_encode(array_column($contentTypes, 'count')); ?>,
                backgroundColor: [
                    '#0d6efd',
                    '#198754'
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
