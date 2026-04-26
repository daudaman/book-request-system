<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireUser();

$pdo = getDBConnection();

// Fetch user's requests with book info
try {
    $stmt = $pdo->prepare("
        SELECT br.id, br.status, br.category, br.created_at, b.title, b.author 
        FROM book_requests br 
        JOIN books b ON br.book_id = b.id 
        WHERE br.user_id = :user_id 
        ORDER BY br.created_at DESC
    ");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $requests = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    $requests = [];
}

// Get stats
try {
    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM book_requests WHERE user_id = :uid");
    $stmtTotal->execute([':uid' => $_SESSION['user_id']]);
    $totalRequests = $stmtTotal->fetchColumn();

    $stmtPending = $pdo->prepare("SELECT COUNT(*) FROM book_requests WHERE user_id = :uid AND status = 'Pending'");
    $stmtPending->execute([':uid' => $_SESSION['user_id']]);
    $pendingRequests = $stmtPending->fetchColumn();

    $stmtCompleted = $pdo->prepare("SELECT COUNT(*) FROM book_requests WHERE user_id = :uid AND status = 'Completed'");
    $stmtCompleted->execute([':uid' => $_SESSION['user_id']]);
    $completedRequests = $stmtCompleted->fetchColumn();
} catch (PDOException $e) {
    error_log("Stats Error: " . $e->getMessage());
    $totalRequests = $pendingRequests = $completedRequests = 0;
}

$pageTitle = 'My Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<!-- Navigation -->
<nav class="navbar">
    <div class="navbar-inner">
        <a href="../index.php" class="navbar-brand">
            <i class="fas fa-book-open"></i> BookHub
        </a>
        <ul class="navbar-links">
            <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="request_book.php"><i class="fas fa-plus-circle"></i> Request Book</a></li>
            <li><a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
</nav>

<div class="main-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1>👋 Welcome, <span class="greeting"><?php echo e($_SESSION['username']); ?></span></h1>
        <p>Manage your book requests and track their progress</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card purple">
            <div class="stat-icon"><i class="fas fa-book"></i></div>
            <div class="stat-value"><?php echo $totalRequests; ?></div>
            <div class="stat-label">Total Requests</div>
        </div>
        <div class="stat-card orange">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-value"><?php echo $pendingRequests; ?></div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-value"><?php echo $completedRequests; ?></div>
            <div class="stat-label">Completed</div>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-list-alt"></i> My Book Requests</h2>
        </div>

        <?php if (empty($requests)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No requests yet</h3>
                <p>Start by requesting a book from our catalog.</p>
                <a href="request_book.php" class="btn btn-primary" style="margin-top:1rem;">
                    <i class="fas fa-plus-circle"></i> Request a Book
                </a>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Requested</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $i => $req): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><strong><?php echo e($req['title']); ?></strong></td>
                                <td><?php echo e($req['author']); ?></td>
                                <td><?php echo e($req['category']); ?></td>
                                <td>
                                    <?php
                                    $statusClass = [
                                        'Pending' => 'badge-pending',
                                        'In Progress' => 'badge-inprogress',
                                        'Completed' => 'badge-completed',
                                        'Rejected' => 'badge-rejected'
                                    ];
                                    $cls = $statusClass[$req['status']] ?? 'badge-pending';
                                    ?>
                                    <span class="badge <?php echo $cls; ?>"><?php echo e($req['status']); ?></span>
                                </td>
                                <td style="color:var(--text-muted); font-size:0.85rem;"><?php echo date('M d, Y', strtotime($req['created_at'])); ?></td>
                                <td>
                                    <?php if ($req['status'] === 'Pending'): ?>
                                        <a href="cancel_request.php?id=<?php echo (int)$req['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Cancel this request?');" id="btn-cancel-<?php echo (int)$req['id']; ?>">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    <?php else: ?>
                                        <span style="color:var(--text-muted); font-size:0.8rem;">—</span>
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
