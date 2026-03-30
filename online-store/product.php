<?php require_once 'inc/header.php'; ?>
<?php
if(!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$product) {
    header("Location: index.php");
    exit;
}
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = (int)$_POST['quantity'];
    if($quantity > 0) {
        if(!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        if(isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id] += $quantity;
        } else {
            $_SESSION['cart'][$id] = $quantity;
        }
        $_SESSION['cart_success'] = "Product added to cart!";
        header("Location: cart.php");
        exit;
    }
}
?>
<div class="row">
    <div class="col-md-6">
        <img src="<?php echo htmlspecialchars($product['image'] ?: 'https://via.placeholder.com/500'); ?>" class="img-fluid">
    </div>
    <div class="col-md-6">
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        <h3>LKR <?php echo number_format($product['price'], 2); ?></h3>
        <form method="post">
            <div class="mb-3">
                <label>Quantity</label>
                <input type="number" name="quantity" value="1" min="1" class="form-control w-25">
            </div>
            <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
            <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
        </form>
    </div>
</div>
<?php require_once 'inc/footer.php'; ?>