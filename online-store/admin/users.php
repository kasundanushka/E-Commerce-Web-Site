<?php
require_once '../inc/header.php';
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}
if(isset($_GET['action'])) {
    if($_GET['action'] == 'delete' && isset($_GET['id']) && $_GET['id'] != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $_SESSION['admin_msg'] = "User deleted";
    } elseif($_GET['action'] == 'toggle_role' && isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $user = $stmt->fetch();
        if($user) {
            $new_role = ($user['role'] == 'admin') ? 'user' : 'admin';
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$new_role, $_GET['id']]);
            $_SESSION['admin_msg'] = "User role updated";
        }
    }
    header("Location: users.php");
    exit;
}
$users = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();
?>
<h1>Manage Users</h1>
<?php if(isset($_SESSION['admin_msg'])): ?>
    <div class="alert alert-info"><?php echo $_SESSION['admin_msg']; unset($_SESSION['admin_msg']); ?></div>
<?php endif; ?>
<table class="table table-bordered">
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Registered</th><th>Actions</th></tr></thead>
    <tbody>
        <?php foreach($users as $user): ?>
        <tr>
            <td><?php echo $user['id']; ?></td>
            <td><?php echo htmlspecialchars($user['name']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><?php echo ucfirst($user['role']); ?></td>
            <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
            <td>
                <a href="users.php?action=toggle_role&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning">Toggle Role</a>
                <?php if($user['id'] != $_SESSION['user_id']): ?>
                    <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<a href="<?php echo htmlspecialchars($base_url); ?>/admin/index.php" class="btn btn-secondary">Back to Dashboard</a>
<?php require_once '../inc/footer.php'; ?>