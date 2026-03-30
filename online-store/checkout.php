<?php require_once 'inc/header.php'; ?>
<?php
if(!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header("Location: login.php");
    exit;
}
if(empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();
    $total = 0;
    $cart_data = [];
    foreach($products as $p) {
        $qty = $_SESSION['cart'][$p['id']];
        $sub = $p['price'] * $qty;
        $total += $sub;
        $cart_data[] = ['product_id' => $p['id'], 'quantity' => $qty, 'price' => $p['price']];
    }
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
        $stmt->execute([$user_id, $total]);
        $order_id = $pdo->lastInsertId();
        $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach($cart_data as $item) {
            $stmt_item->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
        }
        $pdo->commit();
        unset($_SESSION['cart']);
        $_SESSION['order_success'] = "Order placed successfully! Order ID: $order_id";
        header("Location: orders.php");
        exit;
    } catch(Exception $e) {
        $pdo->rollBack();
        $error = "Order failed: " . $e->getMessage();
    }
}

// Display cart summary for confirmation
$ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$products = $stmt->fetchAll();
$total = 0;
?>
<div class="page-section checkout-summary">
    <h1 class="page-title">Checkout</h1>
    <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <div class="row">
    <div class="col-md-8">
        <h3>Order Summary</h3>
        <table class="table">
            <thead><tr><th>Product</th><th>Quantity</th><th>Price</th><th>Subtotal</th></tr></thead>
            <tbody>
                <?php foreach($products as $p):
                    $qty = $_SESSION['cart'][$p['id']];
                    $sub = $p['price'] * $qty;
                    $total += $sub;
                ?>
                <tr><td><?php echo htmlspecialchars($p['name']); ?></td><td><?php echo $qty; ?></td><td>LKR <?php echo number_format($p['price'],2); ?></td><td>LKR <?php echo number_format($sub,2); ?></td></tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot><tr><th colspan="3" class="text-end">Total:</th><th>LKR <?php echo number_format($total,2); ?></th></tr></tfoot>
        </table>
        <form method="post">
            <button type="submit" class="btn btn-primary">Place Order</button>
            <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
        </form>
    </div>
</div>
<?php require_once 'inc/footer.php'; ?>