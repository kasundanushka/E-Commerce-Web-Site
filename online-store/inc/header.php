<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once dirname(__DIR__) . '/config/db.php';

$pathSegments = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'));
$projectIndex = array_search('online-store', $pathSegments, true);
if ($projectIndex !== false) {
    $base_url = '/' . implode('/', array_slice($pathSegments, 0, $projectIndex + 1));
} else {
    // Fallback to parent directory when project folder name is not found.
    $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
}

$current = basename($_SERVER['SCRIPT_NAME']);
$currentDir = dirname($_SERVER['SCRIPT_NAME']);
function navActive($name, $current, $currentDir = '') {
    // On admin pages, only the admin tab should be active (not Home)
    if (strpos($currentDir, '/admin') === 0) {
        return $name === 'admin' ? 'active' : '';
    }

    return $name === $current ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tech Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo htmlspecialchars($base_url); ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <h1 style="font-size: 50px;"><a  class="navbar-brand" href="<?php echo $base_url; ?>/index.php"><b style="font-size: 45px;">Tech Store</b></a></h1>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link <?php echo navActive('index.php', $current); ?>" href="<?php echo $base_url; ?>/index.php">Home</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link <?php echo navActive('cart.php', $current); ?>" href="<?php echo $base_url; ?>/cart.php">Cart</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo navActive('orders.php', $current); ?>" href="<?php echo $base_url; ?>/orders.php">Orders</a></li>
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item"><a class="nav-link <?php echo navActive('admin', $current, $currentDir); ?>" href="<?php echo $base_url; ?>/admin/">Admin Panel</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link <?php echo navActive('login.php', $current); ?>" href="<?php echo $base_url; ?>/login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo navActive('register.php', $current); ?>" href="<?php echo $base_url; ?>/register.php">Register</a></li>
                <?php endif; ?>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="btn btn-danger btn-sm text-white" href="<?php echo $base_url; ?>/logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container mt-4">
<?php
$flashAlerts = [
    'success' => 'alert-success',
    'error' => 'alert-danger',
    'info' => 'alert-info',
    'flash_message' => 'alert-info',
];
foreach ($flashAlerts as $key => $class) {
    if (!empty($_SESSION[$key])) {
        echo '<div class="alert ' . $class . '" role="alert">' . htmlspecialchars($_SESSION[$key]) . '</div>';
        unset($_SESSION[$key]);
    }
}
?>