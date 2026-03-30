<?php require_once 'inc/header.php'; ?>
<?php
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>
<div class="page-section orders-list">
    <h1 class="page-title">My Orders</h1>
    <?php if(isset($_SESSION['order_success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['order_success']; unset($_SESSION['order_success']); ?></div>
    <?php endif; ?>
    <?php if(empty($orders)): ?>
    <p>You have no orders yet. <a href="index.php">Start shopping</a></p>
<?php else: ?>
    <?php foreach($orders as $order): ?>
        <div class="card mb-3">
            <div class="card-header">Order #<?php echo $order['id']; ?> - <?php echo date('F j, Y', strtotime($order['order_date'])); ?> - Total: LKR <?php echo number_format($order['total_amount'],2); ?> - Status: <?php echo ucfirst($order['status']); ?></div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead><tr><th>Product</th><th>Quantity</th><th>Price</th><th>Subtotal</th></tr></thead>
                    <tbody>
                    <?php
                    $stmt_items = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                    $stmt_items->execute([$order['id']]);
                    $items = $stmt_items->fetchAll();
                    foreach($items as $item):
                        $sub = $item['price'] * $item['quantity'];
                    ?>
                    <tr><td><?php echo htmlspecialchars($item['name']); ?></td><td><?php echo $item['quantity']; ?></td><td>LKR <?php echo number_format($item['price'],2); ?></td><td>LKR <?php echo number_format($sub,2); ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php require_once 'inc/footer.php'; ?>