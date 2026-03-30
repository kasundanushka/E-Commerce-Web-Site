<?php require_once 'inc/header.php'; ?>
<?php
if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Support both hashed passwords (new users) and plain text (legacy/test data)
    $passwordMatches = $user && (password_verify($password, $user['password']) || hash_equals($user['password'], $password));
    if($passwordMatches) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        // Redirect to previously requested page if any
        $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
        unset($_SESSION['redirect_after_login']);
        header("Location: $redirect");
        exit;
    } else {
        $error = "Invalid email or password. Make sure you registered with this email and correct password.";
    }
}
?>
<div class="row justify-content-center page-section">
    <div class="col-md-6 form-container">
        <div class="card admin-panel">
            <div class="card-body">
                <h2 class="page-title">Login</h2>
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="post" autocomplete="off">
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required autocomplete="username" placeholder="user@gmail.com" value="admin@gmail.com">
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required autocomplete="current-password" placeholder="*******" value="">
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                    <a href="register.php" class="btn btn-link">Create an account</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once 'inc/footer.php'; ?>