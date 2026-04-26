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

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $reqId = (int)($_POST['request_id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';
        $validStatuses = ['Pending', 'In Progress', 'Completed', 'Rejected'];

        if ($reqId > 0 && in_array($newStatus, $validStatuses)) {
            try {
                $stmt = $pdo->prepare("UPDATE book_requests SET status = :status WHERE id = :id");
                $stmt->execute([':status' => $newStatus, ':id' => $reqId]);
                $success = 'Request status updated to ' . $newStatus . '.';
            } catch (PDOException $e) {
                error_log("Update Status Error: " . $e->getMessage());
                $errors[] = 'Failed to update status.';
            }
        }
    } elseif ($_POST['action'] === 'delete_request') {
        $reqId = (int)($_POST['request_id'] ?? 0);
        if ($reqId > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM book_requests WHERE id = :id");
                $stmt->execute([':id' => $reqId]);
                $success = 'Request deleted successfully.';
            } catch (PDOException $e) {
                error_log("Delete Request Error: " . $e->getMessage());
                $errors[] = 'Failed to delete request.';
            }
        }
    }
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

$pageTitle = 'Manage Requests';
include __DIR__ . '/../includes/header.php';
?>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="../index.php" class="navbar-brand"><i class="fas fa-book-open"></i> BookHub <span style="font-size:0.7rem; color:var(--secondary); font-weight:400;">SUPER ADMIN</span></a>
        <ul class="navbar-links">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="manage_requests.php" class="active"><i class="fas fa-tasks"></i> Requests</a></li>
            <li><a href="manage_users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="manage_admins.php"><i class="fas fa-user-shield"></i> Admins</a></li>
            <li><a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
</nav>

<div class="main-container">
    <div class="page-header">
        <h1><i class="fas fa-tasks" style="color:var(--primary);"></i> Manage Requests</h1>
        <p>Update status or delete any book request</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i><div><?php echo e($success); ?></div></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i><div><?php echo e(implode('<br>', $errors)); ?></div></div>
    <?php endif; ?>

    <div class="card">
        <?php if (empty($requests)): ?>
            <div class="empty-state"><i class="fas fa-inbox"></i><h3>No requests found</h3></div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>#</th><th>User</th><th>Book</th><th>Category</th><th>Status</th><th>Date</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $i => $req): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><strong><?php echo e($req['username']); ?></strong></td>
                                <td><?php echo e($req['title']); ?></td>
                                <td><?php echo e($req['category']); ?></td>
                                <td>
                                    <?php
                                    $sc = ['Pending'=>'badge-pending','In Progress'=>'badge-inprogress','Completed'=>'badge-completed','Rejected'=>'badge-rejected'];
                                    ?>
                                    <span class="badge <?php echo $sc[$req['status']] ?? 'badge-pending'; ?>"><?php echo e($req['status']); ?></span>
                                </td>
                                <td style="font-size:0.85rem; color:var(--text-muted);"><?php echo date('M d, Y', strtotime($req['created_at'])); ?></td>
                                <td>
                                    <div class="actions-row">
                                        <!-- Status Update Form -->
                                        <form method="POST" action="" style="display:inline-flex; gap:4px; align-items:center;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="request_id" value="<?php echo (int)$req['id']; ?>">
                                            <select name="status" class="form-control" style="padding:5px 8px; font-size:0.8rem; width:auto; min-width:120px;">
                                                <option value="Pending" <?php echo $req['status']==='Pending'?'selected':''; ?>>Pending</option>
                                                <option value="In Progress" <?php echo $req['status']==='In Progress'?'selected':''; ?>>In Progress</option>
                                                <option value="Completed" <?php echo $req['status']==='Completed'?'selected':''; ?>>Completed</option>
                                                <option value="Rejected" <?php echo $req['status']==='Rejected'?'selected':''; ?>>Rejected</option>
                                            </select>
                                            <button type="submit" class="btn btn-info btn-sm" id="btn-update-<?php echo (int)$req['id']; ?>"><i class="fas fa-save"></i></button>
                                        </form>
                                        <!-- Delete Form -->
                                        <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Delete this request?');">
                                            <input type="hidden" name="action" value="delete_request">
                                            <input type="hidden" name="request_id" value="<?php echo (int)$req['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" id="btn-delete-req-<?php echo (int)$req['id']; ?>"><i class="fas fa-trash"></i></button>
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
