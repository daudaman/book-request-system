<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$pdo = getDBConnection();

// Fetch dashboard stats
try {
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalRequests = $pdo->query("SELECT COUNT(*) FROM book_requests")->fetchColumn();
    $inProgress = $pdo->query("SELECT COUNT(*) FROM book_requests WHERE status = 'In Progress'")->fetchColumn();
    $completed = $pdo->query("SELECT COUNT(*) FROM book_requests WHERE status = 'Completed'")->fetchColumn();
    $pending = $pdo->query("SELECT COUNT(*) FROM book_requests WHERE status = 'Pending'")->fetchColumn();
    $rejected = $pdo->query("SELECT COUNT(*) FROM book_requests WHERE status = 'Rejected'")->fetchColumn();
    $totalBooks = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
} catch (PDOException $e) {
    error_log("Admin Dashboard Error: " . $e->getMessage());
    $totalUsers = $totalRequests = $inProgress = $completed = $pending = $rejected = $totalBooks = 0;
}

// Fetch all requests
try {
    $stmt = $pdo->query("
        SELECT br.id, br.status, br.category, br.created_at, b.title, b.author, u.username, u.email
        FROM book_requests br
        JOIN books b ON br.book_id = b.id
        JOIN users u ON br.user_id = u.id
        ORDER BY br.created_at DESC
    ");
    $requests = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Fetch Requests Error: " . $e->getMessage());
    $requests = [];
}

// Fetch all books
try {
    $stmtBooks = $pdo->query("SELECT id, title, author, category, created_at FROM books ORDER BY category ASC, title ASC");
    $books = $stmtBooks->fetchAll();
} catch (PDOException $e) {
    error_log("Fetch Books Error: " . $e->getMessage());
    $books = [];
}

$pageTitle = 'Admin Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="../index.php" class="navbar-brand"><i class="fas fa-book-open"></i> BookHub <span style="font-size:0.7rem; color:var(--accent); font-weight:400;">ADMIN</span></a>
        <ul class="navbar-links">
            <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
</nav>

<div class="main-container">
    <div class="page-header">
        <h1>📊 Admin Dashboard</h1>
        <p>Overview of system statistics (read-only access)</p>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card purple">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-value"><?php echo $totalUsers; ?></div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card blue">
            <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
            <div class="stat-value"><?php echo $totalRequests; ?></div>
            <div class="stat-label">Total Requests</div>
        </div>
        <div class="stat-card orange">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-value"><?php echo $pending; ?></div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card cyan">
            <div class="stat-icon"><i class="fas fa-spinner"></i></div>
            <div class="stat-value"><?php echo $inProgress; ?></div>
            <div class="stat-label">In Progress</div>
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

    <!-- All Requests Table -->
    <div class="card" style="margin-bottom:2rem;">
        <div class="card-header"><h2><i class="fas fa-list-alt"></i> All Book Requests</h2></div>
        <?php if (empty($requests)): ?>
            <div class="empty-state"><i class="fas fa-inbox"></i><h3>No requests yet</h3></div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>#</th><th>User</th><th>Book</th><th>Author</th><th>Category</th><th>Status</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $i => $req): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><strong><?php echo e($req['username']); ?></strong><br><span style="color:var(--text-muted); font-size:0.8rem;"><?php echo e($req['email']); ?></span></td>
                                <td><?php echo e($req['title']); ?></td>
                                <td><?php echo e($req['author']); ?></td>
                                <td><?php echo e($req['category']); ?></td>
                                <td>
                                    <?php
                                    $sc = ['Pending'=>'badge-pending','In Progress'=>'badge-inprogress','Completed'=>'badge-completed','Rejected'=>'badge-rejected'];
                                    ?>
                                    <span class="badge <?php echo $sc[$req['status']] ?? 'badge-pending'; ?>"><?php echo e($req['status']); ?></span>
                                </td>
                                <td style="font-size:0.85rem; color:var(--text-muted);"><?php echo date('M d, Y', strtotime($req['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- All Books Table -->
    <div class="card">
        <div class="card-header"><h2><i class="fas fa-book"></i> All Books (<?php echo $totalBooks; ?>)</h2></div>
        <?php if (empty($books)): ?>
            <div class="empty-state"><i class="fas fa-book-open"></i><h3>No books in database</h3></div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>#</th><th>Title</th><th>Author</th><th>Category</th><th>Added</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $i => $book): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><strong><?php echo e($book['title']); ?></strong></td>
                                <td><?php echo e($book['author']); ?></td>
                                <td><span class="badge badge-user"><?php echo e($book['category']); ?></span></td>
                                <td style="font-size:0.85rem; color:var(--text-muted);"><?php echo date('M d, Y', strtotime($book['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
