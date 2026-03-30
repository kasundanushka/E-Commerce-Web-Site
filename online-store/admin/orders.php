<?php
require_once '../inc/header.php';
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $orderId = (int)$_POST['order_id'];
    $status = in_array($_POST['status'], ['pending','processing','shipped','completed','cancelled']) ? $_POST['status'] : 'pending';
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $orderId]);
    $_SESSION['flash_message'] = "Order #$orderId status updated to " . ucfirst($status);
    header('Location: orders.php');
    exit;
}
$orders = $pdo->query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC")->fetchAll();
?>
<h1>All Orders</h1>
<table class="table table-bordered">
    <thead><tr><th>Order ID</th><th>User</th><th>Total</th><th>Date</th><th>Status</th><th>Details</th></tr></thead>
    <tbody>
        <?php foreach($orders as $order): ?>
        <tr>
            <td><?php echo $order['id']; ?></td>
            <td><?php echo htmlspecialchars($order['user_name']); ?></td>
            <td>LKR<?php echo number_format($order['total_amount'],2); ?></td>
            <td><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></td>
            <td>
                <form method="post" style="display:inline-block; min-width:170px;">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php $statuses = ['pending','processing','shipped','completed','cancelled']; ?>
                        <?php foreach($statuses as $status): ?>
                            <option value="<?php echo $status; ?>" <?php echo $order['status'] === $status ? 'selected' : ''; ?>><?php echo ucfirst($status); ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </td>
            <td><a href="<?php echo htmlspecialchars($base_url); ?>/admin/order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">View</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<a href="<?php echo htmlspecialchars($base_url); ?>/admin/index.php" class="btn btn-secondary">Back to Dashboard</a>
<?php require_once '../inc/footer.php'; ?>