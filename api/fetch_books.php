<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

session_start();

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    echo json_encode(['error' => 'Unauthorized. Please login.']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

$category = trim($_POST['category'] ?? '');
$validCategories = ['App Development', 'Mobile Development', 'Artificial Intelligence'];

if (empty($category) || !in_array($category, $validCategories)) {
    echo json_encode(['error' => 'Invalid category selected.']);
    exit;
}

$pdo = getDBConnection();

// Check rate limit: max 5 API calls per user per 24 hours
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM api_rate_limits WHERE user_id = :uid AND request_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $stmt->execute([':uid' => $_SESSION['user_id']]);
    $callCount = $stmt->fetchColumn();

    if ($callCount >= 5) {
        echo json_encode(['error' => 'API rate limit reached. Max 5 calls per 24 hours. Please try again later.']);
        exit;
    }
} catch (PDOException $e) {
    error_log("Rate Limit Check Error: " . $e->getMessage());
}

// Call Google Books API
$query = urlencode($category);
$apiUrl = "https://www.googleapis.com/books/v1/volumes?q={$query}&maxResults=20";

$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'ignore_errors' => true
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
]);

$response = @file_get_contents($apiUrl, false, $context);

if ($response === false) {
    // Fallback: return books already in DB for this category
    try {
        $stmt = $pdo->prepare("SELECT id, title, author, category FROM books WHERE category = :cat ORDER BY title ASC");
        $stmt->execute([':cat' => $category]);
        $existingBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['books' => $existingBooks, 'source' => 'database']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Failed to fetch books. Please try again later.']);
    }
    exit;
}

$data = json_decode($response, true);

if (!isset($data['items']) || empty($data['items'])) {
    // Return existing DB books as fallback
    try {
        $stmt = $pdo->prepare("SELECT id, title, author, category FROM books WHERE category = :cat ORDER BY title ASC");
        $stmt->execute([':cat' => $category]);
        $existingBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['books' => $existingBooks, 'source' => 'database']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'No books found for this category.']);
    }
    exit;
}

// Record API call for rate limiting
try {
    $stmt = $pdo->prepare("INSERT INTO api_rate_limits (user_id) VALUES (:uid)");
    $stmt->execute([':uid' => $_SESSION['user_id']]);
} catch (PDOException $e) {
    error_log("Rate Limit Insert Error: " . $e->getMessage());
}

// Extract and insert books silently (avoid duplicates)
$insertedBooks = [];
foreach ($data['items'] as $item) {
    $volumeInfo = $item['volumeInfo'] ?? [];
    $title = trim($volumeInfo['title'] ?? '');
    $authors = $volumeInfo['authors'] ?? ['Unknown'];
    $author = implode(', ', $authors);

    if (empty($title)) continue;

    try {
        // Insert or ignore duplicate
        $stmt = $pdo->prepare("INSERT IGNORE INTO books (title, author, category) VALUES (:title, :author, :cat)");
        $stmt->execute([':title' => $title, ':author' => $author, ':cat' => $category]);
    } catch (PDOException $e) {
        error_log("Book Insert Error: " . $e->getMessage());
    }
}

// Fetch all books in this category from DB
try {
    $stmt = $pdo->prepare("SELECT id, title, author, category FROM books WHERE category = :cat ORDER BY title ASC");
    $stmt->execute([':cat' => $category]);
    $allBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['books' => $allBooks, 'source' => 'google_api']);
} catch (PDOException $e) {
    error_log("Fetch All Books Error: " . $e->getMessage());
    echo json_encode(['error' => 'Failed to retrieve books.']);
}
?>
