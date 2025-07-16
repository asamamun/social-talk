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

// Handle filter
$filter = $_GET['filter'] ?? 'all';

if ($filter != 'all') {
    $db->where('status', $filter);
}

// Pagination
$page = $_GET['page'] ?? 1;
$db->pageLimit = 10;
$reports = $db->arraybuilder()->paginate("reports", $page);
$totalPages = $db->totalPages;

include('header.php');
?>


<!-- Reports Section -->
<div id="reports-section">
    <h2 class="mb-4">Reports Management</h2>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="btn-group" role="group">
                    <a href="?filter=all" class="btn btn-outline-primary <?php echo ($filter == 'all') ? 'active' : ''; ?>">All</a>
                    <a href="?filter=pending" class="btn btn-outline-primary <?php echo ($filter == 'pending') ? 'active' : ''; ?>">Pending</a>
                    <a href="?filter=resolved" class="btn btn-outline-primary <?php echo ($filter == 'resolved') ? 'active' : ''; ?>">Resolved</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Report ID</th>
                            <th>Reported Content</th>
                            <th>Reported By</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Reported At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                        <tr>
                            <td>#REP-<?php echo $report['report_id']; ?></td>
                            <td>
                                <?php if ($report['reported_post_id']): ?>
                                    Post #<?php echo $report['reported_post_id']; ?>
                                <?php elseif ($report['reported_user_id']): ?>
                                    User #<?php echo $report['reported_user_id']; ?>
                                <?php endif; ?>
                            </td>
                            <td>User #<?php echo $report['reporter_id']; ?></td>
                            <td><?php echo htmlspecialchars($report['reason']); ?></td>
                            <td><span class="badge bg-<?php echo ($report['status'] == 'pending') ? 'warning' : 'success'; ?>"><?php echo ucfirst($report['status']); ?></span></td>
                            <td><?php echo date('d M Y, H:i', strtotime($report['created_at'])); ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1 action-btn review-btn" title="Review" data-report-id="<?php echo $report['report_id']; ?>">
                                    <i class="fas fa-search"></i>
                                </button>
                                <?php if ($report['status'] == 'pending'): ?>
                                <button class="btn btn-sm btn-outline-success me-1 action-btn approve-btn" title="Approve" data-report-id="<?php echo $report['report_id']; ?>">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger action-btn reject-btn" title="Reject" data-report-id="<?php echo $report['report_id']; ?>">
                                    <i class="fas fa-times"></i>
                                </button>
                                <?php else: ?>
                                <button class="btn btn-sm btn-outline-secondary action-btn reopen-btn" title="Reopen" data-report-id="<?php echo $report['report_id']; ?>">
                                    <i class="fas fa-undo"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>"><?php echo $i; ?></a></li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>
<!-- Report Details Modal -->
<div class="modal fade" id="reportDetailsModal" tabindex="-1" aria-labelledby="reportDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportDetailsModalLabel">Report Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Report ID:</strong> <span id="modalReportId"></span></p>
                <p><strong>Reported Content:</strong> <span id="modalReportedContent"></span></p>
                <p><strong>Reported By:</strong> <span id="modalReportedBy"></span></p>
                <p><strong>Reason:</strong> <span id="modalReason"></span></p>
                <p><strong>Status:</strong> <span id="modalStatus"></span></p>
                <p><strong>Reported At:</strong> <span id="modalReportedAt"></span></p>
                <hr>
                <h6>Additional Details:</h6>
                <div id="modalAdditionalDetails">
                    <!-- Content from reported post/user will go here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="modalApproveBtn">Approve</button>
                <button type="button" class="btn btn-danger" id="modalRejectBtn">Reject</button>
                <button type="button" class="btn btn-info" id="modalReopenBtn">Reopen</button>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>