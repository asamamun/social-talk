<?php
session_start();
require_once 'db.php'; // contains $pdo = new PDO(...);

// Check user login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// Handle new post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['content'])) {
    $stmt = $pdo->prepare(
        "INSERT INTO posts (user_id, content, created_at) VALUES (?, ?, NOW())"
    );
    $stmt->execute([$user_id, trim($_POST['content'])]);
}

// Fetch only the user's posts
$stmt = $pdo->prepare(
    "SELECT content, created_at FROM posts WHERE user_id = ? ORDER BY created_at DESC"
);
$stmt->execute([$user_id]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head><title>My Posts</title></head>
<body>
<h1>Your Posts</h1>

<form method="post">
    <textarea name="content" rows="3" cols="40" placeholder="Write something..."></textarea><br>
    <button type="submit">Post</button>
</form>

<hr>

<?php if (count($posts) > 0): ?>
    <?php foreach ($posts as $p): ?>
    <div style="border:1px solid #ddd; padding:10px; margin-bottom:10px;">
        <small><?= htmlspecialchars($p['created_at']) ?></small>
        <p><?= nl2br(htmlspecialchars($p['content'])) ?></p>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>You haven’t posted anything yet.</p>
<?php endif; ?>

</body>
</html>
