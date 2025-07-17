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

include('header.php');
?>
<div class="d-flex justify-content-end">
    <a href="add-user.php" class="btn btn-primary mb-3">Add User</a>
</div>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $db->where("user_id != " . $_SESSION['user_id']);
        $users = $db->get("users");
        foreach ($users as $user) {
        ?>
            <tr>
                <td><?php echo $user['user_id']; ?></td>
                <td><?php echo $user['username']; ?></td>
                <td><?php echo $user['email']; ?></td>
                <td><?php echo $user['role']; ?></td>
                <td><?php echo $user['status']; ?></td>
                <td>
                    <a href="edit-user.php?user_id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                    <a href="delete-user.php?user_id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>
<?php include 'footer.php'; ?>