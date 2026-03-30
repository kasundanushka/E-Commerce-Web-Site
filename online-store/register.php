<?php require_once 'inc/header.php'; ?>
<?php
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    
    $errors = [];
    if(empty($name)) $errors[] = "Name is required";
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email";
    if(strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if($password != $confirm) $errors[] = "Passwords do not match";
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if($stmt->fetch()) $errors[] = "Email already registered";
    
    if(empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        if($stmt->execute([$name, $email, $hashed])) {
            $_SESSION['success'] = "Registration successful. Please login.";
            header("Location: login.php");
            exit;
        } else {
            $errors[] = "Registration failed";
        }
    }
}
?>
<div class="row justify-content-center page-section">
    <div class="col-md-6 form-container">
        <div class="card admin-panel">
            <div class="card-body">
                <h2 class="page-title">Register</h2>
                <?php if(!empty($errors)): ?>
                    <div class="alert alert-danger"><?php echo implode('<br>', $errors); ?></div>
                <?php endif; ?>
                <form method="post" autocomplete="off">
            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" class="form-control"  placeholder="Enter your name" value="" equired autocomplete="off">
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" value="" required autocomplete="off">
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" value="" required autocomplete="new-password">
            </div>
            <div class="mb-3">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm your password" value="" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
            <a href="login.php" class="btn btn-link">Already have an account? Login</a>
        </form>
            </div>
        </div>
    </div>
</div>
<?php require_once 'inc/footer.php'; ?>