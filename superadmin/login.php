<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (isSuperAdmin()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $errors[] = 'Please fill in all fields.';
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, username, password, role FROM admins WHERE username = :username AND role = 'superadmin'");
            $stmt->execute([':username' => $username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['username'] = $admin['username'];
                $_SESSION['role'] = 'superadmin';
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = 'Invalid super admin credentials.';
            }
        } catch (PDOException $e) {
            error_log("Super Admin Login Error: " . $e->getMessage());
            $errors[] = 'An error occurred. Please try again.';
        }
    }
}

$pageTitle = 'Super Admin Login';
include __DIR__ . '/../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <i class="fas fa-crown" style="font-size:2.5rem; color:var(--secondary); margin-bottom:0.5rem; display:block;"></i>
            <h1>Super Admin</h1>
            <p>Full system control access</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i><div><?php echo e(implode('<br>', $errors)); ?></div></div>
        <?php endif; ?>

        <form method="POST" action="" id="superadmin-login-form">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Super admin username" value="<?php echo e($username ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Super admin password" required>
            </div>
            <button type="submit" class="btn btn-block btn-lg" id="btn-superadmin-login" style="background:linear-gradient(135deg, var(--secondary), #CC3355); color:#fff;">
                <i class="fas fa-sign-in-alt"></i> Login as Super Admin
            </button>
        </form>

        <div class="auth-footer">
            <a href="../index.php" style="color:var(--text-muted);"><i class="fas fa-arrow-left"></i> Back to Home</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
