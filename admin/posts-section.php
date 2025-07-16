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

// Handle search, filter, and sort
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';
$sort = $_GET['sort'] ?? 'newest';

$db->join("users u", "p.user_id=u.user_id", "LEFT");
if ($search) {
    $db->where("p.content", '%' . $search . '%', 'like');
}

if ($filter != 'all') {
    // This part needs to be adapted based on how post status is actually stored.
    // Assuming visibility for now.
    $db->where('p.visibility', $filter);
}

// Sorting
if ($sort == 'newest') {
    $db->orderBy('p.created_at', 'desc');
} elseif ($sort == 'oldest') {
    $db->orderBy('p.created_at', 'asc');
}
// More complex sorts like most liked/commented would require subqueries.

// Pagination
$page = $_GET['page'] ?? 1;
$db->pageLimit = 10;
$posts = $db->arraybuilder()->paginate("posts p", $page, "p.*, u.username");
$totalPages = $db->totalPages;

include('header.php');
?>
<div id="posts-section">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Post Management</h2>
    </div>

    <div class="card">
        <div class="card-header">
            <form method="get">
                <div class="row">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search posts..." value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex justify-content-end">
                            <select name="filter" class="form-select me-2" onchange="this.form.submit()">
                                <option value="all" <?php echo ($filter == 'all') ? 'selected' : ''; ?>>All Posts</option>
                                <option value="public" <?php echo ($filter == 'public') ? 'selected' : ''; ?>>Public</option>
                                <option value="friends" <?php echo ($filter == 'friends') ? 'selected' : ''; ?>>Friends</option>
                                <option value="private" <?php echo ($filter == 'private') ? 'selected' : ''; ?>>Private</option>
                                <option value="restricted" <?php echo ($filter == 'restricted') ? 'selected' : ''; ?>>Restricted</option>
                            </select>
                            <select name="sort" class="form-select" onchange="this.form.submit()">
                                <option value="newest" <?php echo ($sort == 'newest') ? 'selected' : ''; ?>>Newest First</option>
                                <option value="oldest" <?php echo ($sort == 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
                            </select>
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
                            <th>Post</th>
                            <th>Author</th>
                            <th>Likes</th>
                            <th>Comments</th>
                            <th>Visibility</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars(substr($post['content'], 0, 50)); ?>...</td>
                                <td><?php echo htmlspecialchars($post['username']); ?></td>
                                <td><?php echo $db->where('post_id', $post['post_id'])->getValue('likes', 'count(*)'); ?></td>
                                <td><?php echo $db->where('post_id', $post['post_id'])->getValue('comments', 'count(*)'); ?></td>
                                <td><span class="badge bg-info"><?php echo ucfirst($post['visibility']); ?></span></td>
                                <td><?php echo date('d M Y, H:i', strtotime($post['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary me-1 action-btn" title="View" onclick="viewPost('<?php echo $post['post_id']; ?>')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger action-btn" title="Delete" onclick="deletePost('<?php echo $post['post_id']; ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning action-btn" title="Restrict" onclick="restrictPost('<?php echo $post['post_id']; ?>')">
                                        <i class="fas fa-ban"></i>
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
<script>
    function viewPost(postId) {
        window.open('../user-profile.php?post_id=' + postId, '_blank');
    }

    function deletePost(postId) {
        if (confirm('Are you sure you want to delete this post?')) {
            fetch('delete_post.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'post_id=' + postId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to delete post.');
                }
            });
        }
    }

    function restrictPost(postId) {
        if (confirm('Are you sure you want to restrict this post?')) {
            fetch('restrict_post.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'post_id=' + postId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to restrict post.');
                }
            });
        }
    }
</script>
