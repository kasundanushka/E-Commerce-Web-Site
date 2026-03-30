<?php
require_once '../inc/header.php';
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}
if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $_SESSION['product_msg'] = "Product deleted";
    header("Location: products.php");
    exit;
}
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $image = trim($_POST['image']);
    if(isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
        $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, image=? WHERE id=?");
        $stmt->execute([$name, $desc, $price, $image, $_POST['edit_id']]);
        $_SESSION['product_msg'] = "Product updated";
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $desc, $price, $image]);
        $_SESSION['product_msg'] = "Product added";
    }
    header("Location: products.php");
    exit;
}
$edit_product = null;
if(isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_product = $stmt->fetch(PDO::FETCH_ASSOC);
}
$products = $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
?>
<h1>Manage Products</h1>
<?php if(isset($_SESSION['product_msg'])): ?>
    <div class="alert alert-info"><?php echo $_SESSION['product_msg']; unset($_SESSION['product_msg']); ?></div>
<?php endif; ?>
<div class="card mb-4">
    <div class="card-header"><?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?></div>
    <div class="card-body">
        <form method="post">
            <?php if($edit_product): ?>
                <input type="hidden" name="edit_id" value="<?php echo $edit_product['id']; ?>">
            <?php endif; ?>
            <div class="mb-3"><label>Name</label><input type="text" name="name" class="form-control" value="<?php echo $edit_product ? htmlspecialchars($edit_product['name']) : ''; ?>" required></div>
            <div class="mb-3"><label>Description</label><textarea name="description" class="form-control" rows="3"><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea></div>
            <div class="mb-3"><label>Price</label><input type="number" step="0.01" name="price" class="form-control" value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>" required></div>
            <div class="mb-3"><label>Image URL</label><input type="text" name="image" class="form-control" value="<?php echo $edit_product ? htmlspecialchars($edit_product['image']) : ''; ?>" placeholder="https://example.com/image.jpg"></div>
            <button type="submit" class="btn btn-primary"><?php echo $edit_product ? 'Update' : 'Add'; ?> Product</button>
            <?php if($edit_product): ?><a href="products.php" class="btn btn-secondary">Cancel</a><?php endif; ?>
        </form>
    </div>
</div>
<table class="table table-bordered">
    <thead><tr><th>ID</th><th>Name</th><th>Price</th><th>Image</th><th>Actions</th></tr></thead>
    <tbody>
        <?php foreach($products as $p): ?>
        <tr>
            <td><?php echo $p['id']; ?></td>
            <td><?php echo htmlspecialchars($p['name']); ?></td>
            <td>LKR<?php echo number_format($p['price'],2); ?></td>
            <td><img src="<?php echo htmlspecialchars($p['image'] ?: 'https://via.placeholder.com/50'); ?>" width="50"></td>
            <td><a href="products.php?edit=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary">Edit</a> <a href="products.php?action=delete&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<a href="<?php echo htmlspecialchars($base_url); ?>/admin/index.php" class="btn btn-secondary">Back to Dashboard</a>
<?php require_once '../inc/footer.php'; ?>