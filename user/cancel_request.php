<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireUser();

$requestId = (int)($_GET['id'] ?? 0);

if ($requestId <= 0) {
    header('Location: dashboard.php');
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Only cancel if it belongs to the user and status is Pending
    $stmt = $pdo->prepare("DELETE FROM book_requests WHERE id = :id AND user_id = :uid AND status = 'Pending'");
    $stmt->execute([':id' => $requestId, ':uid' => $_SESSION['user_id']]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['flash_success'] = 'Request cancelled successfully.';
    } else {
        $_SESSION['flash_error'] = 'Cannot cancel this request. It may have already been processed.';
    }
} catch (PDOException $e) {
    error_log("Cancel Request Error: " . $e->getMessage());
    $_SESSION['flash_error'] = 'An error occurred. Please try again.';
}

header('Location: dashboard.php');
exit;
?>
