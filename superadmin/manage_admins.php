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
    if ($_POST['action'] === 'add_admin') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $errors[] = 'Username and password are required.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        } else {
            try {
                // Check if username already exists
                $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = :username");
                $stmt->execute([':username' => $username]);
                if ($stmt->fetch()) {
                    $errors[] = 'Admin username already exists.';
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO admins (username, password, role) VALUES (:username, :password, 'admin')");
                    $stmt->execute([':username' => $username, ':password' => $hashedPassword]);
                    $success = 'Admin "' . $username . '" added successfully.';
                }
            } catch (PDOException $e) {
                error_log("Add Admin Error: " . $e->getMessage());
                $errors[] = 'Failed to add admin.';
            }
        }
    } elseif ($_POST['action'] === 'delete_admin') {
        $adminId = (int)($_POST['admin_id'] ?? 0);
        if ($adminId > 0) {
            try {
                // Cannot delete super admin
                $stmt = $pdo->prepare("SELECT role FROM admins WHERE id = :id");
                $stmt->execute([':id' => $adminId]);
                $admin = $stmt->fetch();

                if ($admin && $admin['role'] === 'superadmin') {
                    $errors[] = 'Cannot delete the super admin account.';
                } else {
                    $stmt = $pdo->prepare("DELETE FROM admins WHERE id = :id AND role = 'admin'");
                    $stmt->execute([':id' => $adminId]);
                    if ($stmt->rowCount() > 0) {
                        $success = 'Admin deleted successfully.';
                    } else {
                        $errors[] = 'Admin not found or cannot be deleted.';
                    }
                }
            } catch (PDOException $e) {
                error_log("Delete Admin Error: " . $e->getMessage());
                $errors[] = 'Failed to delete admin.';
            }
        }
    }
}

// Fetch all admins
try {
    $stmt = $pdo->query("SELECT id, username, role, created_at FROM admins ORDER BY role DESC, created_at ASC");
    $admins = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Fetch Admins Error: " . $e->getMessage());
    $admins = [];
}

$pageTitle = 'Manage Admins';
include __DIR__ . '/../includes/header.php';
?>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="../index.php" class="navbar-brand"><i class="fas fa-book-open"></i> BookHub <span style="font-size:0.7rem; color:var(--secondary); font-weight:400;">SUPER ADMIN</span></a>
        <ul class="navbar-links">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="manage_requests.php"><i class="fas fa-tasks"></i> Requests</a></li>
            <li><a href="manage_users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="manage_admins.php" class="active"><i class="fas fa-user-shield"></i> Admins</a></li>
            <li><a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
</nav>

<div class="main-container">
    <div class="page-header">
        <h1><i class="fas fa-user-shield" style="color:var(--secondary);"></i> Manage Admins</h1>
        <p>Add new admins or remove existing ones (super admin cannot be deleted)</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i><div><?php echo e($success); ?></div></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i><div><?php echo e(implode('<br>', $errors)); ?></div></div>
    <?php endif; ?>

    <!-- Add Admin Form -->
    <div class="card" style="margin-bottom:2rem;">
        <div class="card-header"><h2><i class="fas fa-user-plus"></i> Add New Admin</h2></div>
        <form method="POST" action="" id="add-admin-form">
            <input type="hidden" name="action" value="add_admin">
            <div style="display:grid; grid-template-columns:1fr 1fr auto; gap:1rem; align-items:end;">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="admin-username">Username</label>
                    <input type="text" class="form-control" id="admin-username" name="username" placeholder="Admin username" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="admin-password">Password</label>
                    <input type="password" class="form-control" id="admin-password" name="password" placeholder="Admin password" required>
                </div>
                <button type="submit" class="btn btn-success btn-lg" id="btn-add-admin" style="height:48px;">
                    <i class="fas fa-plus"></i> Add Admin
                </button>
            </div>
        </form>
    </div>

    <!-- Admins List -->
    <div class="card">
        <div class="card-header"><h2><i class="fas fa-user-shield"></i> All Admins (<?php echo count($admins); ?>)</h2></div>
        <?php if (empty($admins)): ?>
            <div class="empty-state"><i class="fas fa-user-shield"></i><h3>No admins found</h3></div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>#</th><th>Username</th><th>Role</th><th>Created</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $i => $admin): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><strong><?php echo e($admin['username']); ?></strong></td>
                                <td>
                                    <?php if ($admin['role'] === 'superadmin'): ?>
                                        <span class="badge badge-superadmin"><i class="fas fa-crown"></i> Super Admin</span>
                                    <?php else: ?>
                                        <span class="badge badge-admin"><i class="fas fa-user-shield"></i> Admin</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:0.85rem; color:var(--text-muted);"><?php echo date('M d, Y', strtotime($admin['created_at'])); ?></td>
                                <td>
                                    <?php if ($admin['role'] === 'superadmin'): ?>
                                        <span style="color:var(--text-muted); font-size:0.8rem;"><i class="fas fa-lock"></i> Protected</span>
                                    <?php else: ?>
                                        <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Delete this admin?');">
                                            <input type="hidden" name="action" value="delete_admin">
                                            <input type="hidden" name="admin_id" value="<?php echo (int)$admin['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" id="btn-delete-admin-<?php echo (int)$admin['id']; ?>"><i class="fas fa-trash"></i> Delete</button>
                                        </form>
                                    <?php endif; ?>
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
