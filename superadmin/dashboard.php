<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireSuperAdmin();

$pdo = getDBConnection();

try {
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalAdmins = $pdo->query("SELECT COUNT(*) FROM admins WHERE role = 'admin'")->fetchColumn();
    $totalRequests = $pdo->query("SELECT COUNT(*) FROM book_requests")->fetchColumn();
    $pending = $pdo->query("SELECT COUNT(*) FROM book_requests WHERE status = 'Pending'")->fetchColumn();
    $inProgress = $pdo->query("SELECT COUNT(*) FROM book_requests WHERE status = 'In Progress'")->fetchColumn();
    $completed = $pdo->query("SELECT COUNT(*) FROM book_requests WHERE status = 'Completed'")->fetchColumn();
    $rejected = $pdo->query("SELECT COUNT(*) FROM book_requests WHERE status = 'Rejected'")->fetchColumn();
    $totalBooks = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
} catch (PDOException $e) {
    error_log("SA Dashboard Error: " . $e->getMessage());
    $totalUsers = $totalAdmins = $totalRequests = $pending = $inProgress = $completed = $rejected = $totalBooks = 0;
}

$pageTitle = 'Super Admin Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="../index.php" class="navbar-brand"><i class="fas fa-book-open"></i> BookHub <span style="font-size:0.7rem; color:var(--secondary); font-weight:400;">SUPER ADMIN</span></a>
        <ul class="navbar-links">
            <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="manage_requests.php"><i class="fas fa-tasks"></i> Requests</a></li>
            <li><a href="manage_users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="manage_admins.php"><i class="fas fa-user-shield"></i> Admins</a></li>
            <li><a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
</nav>

<div class="main-container">
    <div class="page-header">
        <h1>👑 Super Admin Dashboard</h1>
        <p>Full system overview and management controls</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card purple">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-value"><?php echo $totalUsers; ?></div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card cyan">
            <div class="stat-icon"><i class="fas fa-user-shield"></i></div>
            <div class="stat-value"><?php echo $totalAdmins; ?></div>
            <div class="stat-label">Admins</div>
        </div>
        <div class="stat-card blue">
            <div class="stat-icon"><i class="fas fa-book"></i></div>
            <div class="stat-value"><?php echo $totalBooks; ?></div>
            <div class="stat-label">Total Books</div>
        </div>
        <div class="stat-card orange">
            <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
            <div class="stat-value"><?php echo $totalRequests; ?></div>
            <div class="stat-label">Total Requests</div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-value"><?php echo $completed; ?></div>
            <div class="stat-label">Completed</div>
        </div>
        <div class="stat-card red">
            <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
            <div class="stat-value"><?php echo $rejected; ?></div>
            <div class="stat-label">Rejected</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:1.5rem;">
        <a href="manage_requests.php" class="card" style="text-decoration:none; text-align:center; padding:2rem;">
            <i class="fas fa-tasks" style="font-size:2rem; color:var(--primary); margin-bottom:1rem; display:block;"></i>
            <h3 style="margin-bottom:0.5rem;">Manage Requests</h3>
            <p style="color:var(--text-muted); font-size:0.85rem;">View, update status, and delete book requests</p>
        </a>
        <a href="manage_users.php" class="card" style="text-decoration:none; text-align:center; padding:2rem;">
            <i class="fas fa-users-cog" style="font-size:2rem; color:var(--success); margin-bottom:1rem; display:block;"></i>
            <h3 style="margin-bottom:0.5rem;">Manage Users</h3>
            <p style="color:var(--text-muted); font-size:0.85rem;">View users, reset passwords, and remove accounts</p>
        </a>
        <a href="manage_admins.php" class="card" style="text-decoration:none; text-align:center; padding:2rem;">
            <i class="fas fa-user-shield" style="font-size:2rem; color:var(--secondary); margin-bottom:1rem; display:block;"></i>
            <h3 style="margin-bottom:0.5rem;">Manage Admins</h3>
            <p style="color:var(--text-muted); font-size:0.85rem;">Add or remove admin accounts</p>
        </a>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
