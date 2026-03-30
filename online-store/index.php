<?php
require_once 'inc/header.php';

$search = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? 'all');
$price = trim($_GET['price'] ?? 'all');
$page = max(1, (int)($_GET['page'] ?? 1));

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $productId = (int)($_POST['product_id'] ?? 0);
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    if($productId > 0) {
        if(!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        if(isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }
        $_SESSION['cart_success'] = 'Product added to cart!';
        header('Location: cart.php');
        exit;
    }
}

$limit = 6;
$offset = ($page - 1) * $limit;
$params = [];
$whereClauses = [];

if($search !== '') {
    $whereClauses[] = "(name LIKE ? OR description LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
}

if($category !== 'all') {
    if($category === 'smartphones') {
        $whereClauses[] = "(name LIKE ? OR description LIKE ? OR name LIKE ? OR description LIKE ?)";
        $params[] = '%Smartphone%';
        $params[] = '%smartphone%';
        $params[] = '%iPhone%';
        $params[] = '%Android%';
    } elseif($category === 'laptops') {
        $whereClauses[] = "(name LIKE ? OR description LIKE ? OR name LIKE ? OR description LIKE ?)";
        $params[] = '%Laptop%';
        $params[] = '%laptop%';
        $params[] = '%Notebook%';
        $params[] = '%notebook%';
    } elseif($category === 'audio') {
        $whereClauses[] = "(name LIKE ? OR description LIKE ? OR name LIKE ? OR description LIKE ? OR name LIKE ? OR description LIKE ?)";
        $params[] = '%Headphone%';
        $params[] = '%headphone%';
        $params[] = '%Audio%';
        $params[] = '%audio%';
        $params[] = '%Speaker%';
        $params[] = '%speaker%';
    }
}

if($price !== 'all') {
    if($price === 'under-50000') {
        $whereClauses[] = "price < 50000";
    } elseif($price === '50000-100000') {
        $whereClauses[] = "price BETWEEN 50000 AND 100000";
    } elseif($price === '100000-200000') {
        $whereClauses[] = "price BETWEEN 100000 AND 200000";
    } elseif($price === 'over-200000') {
        $whereClauses[] = "price > 200000";
    }
}

$where = '';
if(!empty($whereClauses)) {
    $where = 'WHERE ' . implode(' AND ', $whereClauses);
}

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM products $where");
$totalStmt->execute($params);
$totalRecords = (int)$totalStmt->fetchColumn();
$totalPages = max(1, ceil($totalRecords / $limit));

// Some MariaDB versions do not support placeholders for LIMIT/OFFSET in non-emulated prepares.
// Use integer-casted values directly to avoid syntax issues.
$limit = (int)$limit;
$offset = (int)$offset;
$sql = "SELECT * FROM products $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
?>
<div class="hero-banner">
    <div class="container">
        <h1>Welcome to Tech Store</h1>
        <p>Discover the latest tech at great prices - crafted for a beautiful experience.</p>
        <a class="btn btn-light text-primary" href="#product-list">Shop Now</a>
    </div>
</div>
<div class="page-section">
    <div class="container">
        <div class="row align-items-center mb-4">
            <div class="col-md-4">
                <h1 class="page-title">Products</h1>
            </div>
            <div class="col-md-8">
                <form class="row g-2" method="get" action="index.php">
                    <div class="col-md-5">
                        <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" class="form-control" placeholder="Search products...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="category">
                            <option value="all" <?php echo $category === 'all' ? 'selected' : ''; ?>>All Categories</option>
                            <option value="smartphones" <?php echo $category === 'smartphones' ? 'selected' : ''; ?>>Smartphones</option>
                            <option value="laptops" <?php echo $category === 'laptops' ? 'selected' : ''; ?>>Laptops</option>
                            <option value="audio" <?php echo $category === 'audio' ? 'selected' : ''; ?>>Audio</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="price">
                            <option value="all" <?php echo $price === 'all' ? 'selected' : ''; ?>>All prices</option>
                            <option value="under-50000" <?php echo $price === 'under-50000' ? 'selected' : ''; ?>>Under LKR 50,000</option>
                            <option value="50000-100000" <?php echo $price === '50000-100000' ? 'selected' : ''; ?>>LKR 50,000 - 100,000</option>
                            <option value="100000-200000" <?php echo $price === '100000-200000' ? 'selected' : ''; ?>>LKR 100,000 - 200,000</option>
                            <option value="over-200000" <?php echo $price === 'over-200000' ? 'selected' : ''; ?>>Over LKR 200,000</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">Go</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="product-list" class="row product-row">

    <?php while($product = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <img src="<?php echo htmlspecialchars($product['image'] ?: 'https://via.placeholder.com/300'); ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                <p class="text-muted">Category: <?php
                    $cat = 'Other';
                    $text = $product['name'] . ' ' . $product['description'];
                    if(preg_match('/laptop|notebook/i', $text)) {
                        $cat = 'Laptops';
                    } elseif(preg_match('/audio|headphone|speaker/i', $text)) {
                        $cat = 'Audio';
                    } elseif(preg_match('/iphone|android|smartphone/i', $text)) {
                        $cat = 'Smartphones';
                    }
                    echo $cat;
                ?></p>
                <p class="card-text"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                <p class="card-text"><strong>LKR <?php echo number_format($product['price'], 2); ?></strong></p>
                <div class="d-grid gap-2">
                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
                    <form method="post" class="d-flex gap-2 align-items-center">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="number" name="quantity" value="1" min="1" class="form-control" style="max-width: 80px;" aria-label="Quantity">
                        <button type="submit" name="add_to_cart" class="btn btn-outline-primary">Add</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
        </div>
        <?php if($totalPages > 1): ?>
            <nav aria-label="Product pagination">
                <ul class="pagination justify-content-center mt-4">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?q=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&price=<?php echo urlencode($price); ?>&page=<?php echo $page-1; ?>">Previous</a>
                    </li>
                    <?php for($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?php echo $p == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?q=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&price=<?php echo urlencode($price); ?>&page=<?php echo $p; ?>"><?php echo $p; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?q=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&price=<?php echo urlencode($price); ?>&page=<?php echo $page+1; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>
<?php require_once 'inc/footer.php'; ?>