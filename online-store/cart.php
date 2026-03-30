<?php require_once 'inc/header.php'; ?>
<?php
if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['update_cart'])) {
        foreach($_POST['quantity'] as $id => $qty) {
            $qty = (int)$qty;
            if($qty <= 0) unset($_SESSION['cart'][$id]);
            else $_SESSION['cart'][$id] = $qty;
        }
        $_SESSION['flash_message'] = "Cart updated";
        header("Location: cart.php");
        exit;
    } elseif(isset($_POST['remove_item'])) {
        $removeId = (int)$_POST['remove_item'];
        if(isset($_SESSION['cart'][$removeId])) {
            unset($_SESSION['cart'][$removeId]);
            $_SESSION['flash_message'] = "Item removed from cart";
        }
        header("Location: cart.php");
        exit;
    }
}

$cart_items = [];
$total = 0;
if(!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();
    foreach($products as $product) {
        $qty = $_SESSION['cart'][$product['id']];
        $sub = $product['price'] * $qty;
        $total += $sub;
        $cart_items[] = ['product' => $product, 'quantity' => $qty, 'subtotal' => $sub];
    }
}
?>
<h1>Shopping Cart</h1>
<?php if(isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['flash_message']); unset($_SESSION['flash_message']); ?></div>
<?php endif; ?>
<?php if(empty($cart_items)): ?>
    <div class="page-section">
        <div class="alert alert-info">Your cart is empty. <a href="index.php">Continue shopping</a></div>
    </div>
<?php else: ?>
    <div class="page-section cart-summary">
        <form method="post">
            <table class="table">
            <thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach($cart_items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product']['name']); ?></td>
                    <td>LKR <?php echo number_format($item['product']['price'],2); ?></td>
                    <td><input type="number" name="quantity[<?php echo $item['product']['id']; ?>]" value="<?php echo $item['quantity']; ?>" min="0" class="form-control w-50"></td>
                    <td>LKR <?php echo number_format($item['subtotal'],2); ?></td>
                    <td><button type="submit" name="remove_item" value="<?php echo $item['product']['id']; ?>" class="btn btn-danger btn-sm">Remove</button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot><tr><th colspan="3" class="text-end">Total:</th><th>LKR <?php echo number_format($total,2); ?></th><td></td></tr></tfoot>
        </table>
        <button type="submit" name="update_cart" class="btn btn-secondary">Update Cart</button>
        <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
        <a href="index.php" class="btn btn-link">Continue Shopping</a>
    </form>
</div>
<?php endif; ?>
<?php require_once 'inc/footer.php'; ?>