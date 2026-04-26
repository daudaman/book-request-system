<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireSuperAdmin();

$pdo = getDBConnection();
$success = '';
$errors = [];

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'reset_password') {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId > 0) {
            try {
                // Generate a new random password
                $newPassword = bin2hex(random_bytes(4)); // 8 chars
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = :pass WHERE id = :id");
                $stmt->execute([':pass' => $hashedPassword, ':id' => $userId]);
                $success = 'Password reset successfully. New password: ' . $newPassword;
            } catch (PDOException $e) {
                error_log("Reset Password Error: " . $e->getMessage());
                $errors[] = 'Failed to reset password.';
            }
        }
    } elseif ($_POST['action'] === 'delete_user') {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
                $stmt->execute([':id' => $userId]);
                $success = 'User deleted successfully.';
            } catch (PDOException $e) {
                error_log("Delete User Error: " . $e->getMessage());
                $errors[] = 'Failed to delete user.';
            }
        }
    }
}

// Fetch all users
try {
    $stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Fetch Users Error: " . $e->getMessage());
    $users = [];
}

$pageTitle = 'Manage Users';
include __DIR__ . '/../includes/header.php';
?>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="../index.php" class="navbar-brand"><i class="fas fa-book-open"></i> BookHub <span style="font-size:0.7rem; color:var(--secondary); font-weight:400;">SUPER ADMIN</span></a>
        <ul class="navbar-links">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="manage_requests.php"><i class="fas fa-tasks"></i> Requests</a></li>
            <li><a href="manage_users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="manage_admins.php"><i class="fas fa-user-shield"></i> Admins</a></li>
            <li><a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
</nav>

<div class="main-container">
    <div class="page-header">
        <h1><i class="fas fa-users" style="color:var(--success);"></i> Manage Users</h1>
        <p>View all users, reset passwords, and delete accounts</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i><div><?php echo e($success); ?></div></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i><div><?php echo e(implode('<br>', $errors)); ?></div></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header"><h2><i class="fas fa-users"></i> All Users (<?php echo count($users); ?>)</h2></div>
        <?php if (empty($users)): ?>
            <div class="empty-state"><i class="fas fa-users"></i><h3>No registered users</h3></div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>#</th><th>Username</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $i => $user): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><strong><?php echo e($user['username']); ?></strong></td>
                                <td><?php echo e($user['email']); ?></td>
                                <td><span class="badge badge-user"><?php echo e($user['role']); ?></span></td>
                                <td style="font-size:0.85rem; color:var(--text-muted);"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="actions-row">
                                        <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Reset password for this user?');">
                                            <input type="hidden" name="action" value="reset_password">
                                            <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                            <button type="submit" class="btn btn-warning btn-sm" id="btn-reset-<?php echo (int)$user['id']; ?>"><i class="fas fa-key"></i> Reset</button>
                                        </form>
                                        <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Delete this user? All their requests will also be deleted.');">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" id="btn-delete-user-<?php echo (int)$user['id']; ?>"><i class="fas fa-trash"></i> Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
