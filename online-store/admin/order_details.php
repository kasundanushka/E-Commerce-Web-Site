<?php
require_once '../inc/header.php';
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}
if(!isset($_GET['id'])) {
    header("Location: orders.php");
    exit;
}
$order_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();
if(!$order) {
    header("Location: orders.php");
    exit;
}
$items = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$items->execute([$order_id]);
$items = $items->fetchAll();
?>
<h1>Order Details #<?php echo $order['id']; ?></h1>
<p><strong>User:</strong> <?php echo htmlspecialchars($order['user_name']); ?></p>
<p><strong>Date:</strong> <?php echo date('F j, Y g:i a', strtotime($order['order_date'])); ?></p>
<p><strong>Total:</strong> LKR<?php echo number_format($order['total_amount'],2); ?></p>
<p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
<h3>Items</h3>
<table class="table table-sm">
    <thead><tr><th>Product</th><th>Quantity</th><th>Price</th><th>Subtotal</th></tr></thead>
    <tbody>
        <?php foreach($items as $item): ?>
        <tr>
            <td><?php echo htmlspecialchars($item['name']); ?></td>
            <td><?php echo $item['quantity']; ?></td>
            <td>LKR<?php echo number_format($item['price'],2); ?></td>
                    <td>LKR<?php echo number_format($item['price'] * $item['quantity'],2); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<a href="<?php echo htmlspecialchars($base_url); ?>/admin/orders.php" class="btn btn-secondary">Back to Orders</a>
<?php require_once '../inc/footer.php'; ?>