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

// Handle search and filter
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';
$sort = $_GET['sort'] ?? 'newest';

if ($search) {
    $db->where('username', '%' . $search . '%', 'like');
}

if ($filter != 'all') {
    if ($filter == 'admin') {
        $db->where('role', 'admin');
    } else {
        $db->where('status', $filter);
    }
}

// Sorting
if ($sort == 'newest') {
    $db->orderBy('created_at', 'desc');
} elseif ($sort == 'oldest') {
    $db->orderBy('created_at', 'asc');
} elseif ($sort == 'a-z') {
    $db->orderBy('username', 'asc');
} elseif ($sort == 'z-a') {
    $db->orderBy('username', 'desc');
}

// Pagination
$page = $_GET['page'] ?? 1;
$db->pageLimit = 10;
$users = $db->arraybuilder()->paginate("users", $page);
$totalPages = $db->totalPages;

include('header.php');
?>
<!-- User Management Section -->
<div id="users-section">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>User Management</h2>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-plus me-2"></i>Add User
            </button>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <form method="get">
                <div class="row">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex justify-content-end">
                            <div class="dropdown me-2">
                                <select name="filter" class="form-select" onchange="this.form.submit()">
                                    <option value="all" <?php echo ($filter == 'all') ? 'selected' : ''; ?>>All Users</option>
                                    <option value="active" <?php echo ($filter == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="banned" <?php echo ($filter == 'banned') ? 'selected' : ''; ?>>Banned</option>
                                    <option value="admin" <?php echo ($filter == 'admin') ? 'selected' : ''; ?>>Admins</option>
                                </select>
                            </div>
                            <div class="dropdown">
                                <select name="sort" class="form-select" onchange="this.form.submit()">
                                    <option value="newest" <?php echo ($sort == 'newest') ? 'selected' : ''; ?>>Newest First</option>
                                    <option value="oldest" <?php echo ($sort == 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
                                    <option value="a-z" <?php echo ($sort == 'a-z') ? 'selected' : ''; ?>>A-Z</option>
                                    <option value="z-a" <?php echo ($sort == 'z-a') ? 'selected' : ''; ?>>Z-A</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <img src="../<?php echo $user['profile_picture'] ?? 'assets/images/default-profile.png'; ?>" class="user-avatar me-2">
                                <?php echo htmlspecialchars($user['username']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><span class="badge <?php echo ($user['role'] == 'admin') ? 'badge-admin' : 'badge-user'; ?>"><?php echo htmlspecialchars(ucfirst($user['role'])); ?></span></td>
                            <td><span class="badge bg-<?php echo ($user['status'] == 'active') ? 'success' : 'danger'; ?>"><?php echo htmlspecialchars(ucfirst($user['status'])); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1 action-btn" title="Edit" onclick="editUser('<?php echo $user['user_id']; ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($user['status'] == 'active'): ?>
                                <button class="btn btn-sm btn-outline-danger me-1 action-btn" title="Ban" onclick="banUser('<?php echo $user['user_id']; ?>')">
                                    <i class="fas fa-ban"></i>
                                </button>
                                <?php else: ?>
                                <button class="btn btn-sm btn-outline-success me-1 action-btn" title="Unban" onclick="unbanUser('<?php echo $user['user_id']; ?>')">
                                    <i class="fas fa-check"></i>
                                </button>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-outline-secondary action-btn" title="Delete" onclick="deleteUser('<?php echo $user['user_id']; ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&filter=<?php echo $filter; ?>&sort=<?php echo $sort; ?>"><?php echo $i; ?></a></li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="editUserId" name="user_id">
                    <div class="mb-3">
                        <label for="editUsername" class="form-label">Username</label>
                        <input type="text" class="form-control" id="editUsername" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="editRole" class="form-label">Role</label>
                        <select class="form-select" id="editRole" name="role">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editStatus" class="form-label">Status</label>
                        <select class="form-select" id="editStatus" name="status">
                            <option value="active">Active</option>
                            <option value="banned">Banned</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function editUser(userId) {
        fetch('get_user.php?user_id=' + userId)
            .then(response => response.json())
            .then(data => {
                document.getElementById('editUserId').value = data.user_id;
                document.getElementById('editUsername').value = data.username;
                document.getElementById('editEmail').value = data.email;
                document.getElementById('editRole').value = data.role;
                document.getElementById('editStatus').value = data.status;
                var myModal = new bootstrap.Modal(document.getElementById('editUserModal'), {})
                myModal.show();
            });
    }

    document.getElementById('editUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('edit_user.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to update user.');
            }
        });
    });
</script>

<script>
    function deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
            fetch('delete_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'user_id=' + userId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to delete user.');
                }
            });
        }
    }

    function banUser(userId) {
        if (confirm('Are you sure you want to ban this user?')) {
            fetch('ban_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'user_id=' + userId + '&action=ban'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to ban user.');
                }
            });
        }
    }

    function unbanUser(userId) {
        if (confirm('Are you sure you want to unban this user?')) {
            fetch('ban_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'user_id=' + userId + '&action=unban'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to unban user.');
                }
            });
        }
    }
</script>
