<?php
require_once '../inc/header.php';
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

$latestProducts = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="page-section admin-panel">
    <h1 class="page-title">Admin Dashboard</h1>
    <div class="row mb-3">
        <div class="col-md-4"><div class="card text-white bg-primary mb-3"><div class="card-header">Users</div><div class="card-body"><a href="<?php echo htmlspecialchars($base_url); ?>/admin/users.php" class="btn btn-light">Manage Users</a></div></div></div>
        <div class="col-md-4"><div class="card text-white bg-success mb-3"><div class="card-header">Products</div><div class="card-body"><a href="<?php echo htmlspecialchars($base_url); ?>/admin/products.php" class="btn btn-light">Manage Products</a></div></div></div>
        <div class="col-md-4"><div class="card text-white bg-info mb-3"><div class="card-header">Orders</div><div class="card-body"><a href="<?php echo htmlspecialchars($base_url); ?>/admin/orders.php" class="btn btn-light">View Orders</a></div></div></div>
    </div>
    <h2 class="page-title">Latest Products</h2>
    <div class="row product-row">
    <?php foreach($latestProducts as $product): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <img src="<?php echo htmlspecialchars($product['image'] ?: 'https://via.placeholder.com/300'); ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                    <p class="card-text"><?php echo htmlspecialchars(substr($product['description'],0,80)); ?>...</p>
                    <p class="card-text"><strong>LKR<?php echo number_format($product['price'],2); ?></strong></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
</div>
<?php require_once '../inc/footer.php'; ?>