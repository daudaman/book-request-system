<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireUser();

$pdo = getDBConnection();
$errors = [];
$success = '';
$books = [];
$selectedCategory = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $bookId = (int)$_POST['book_id'];
    $category = trim($_POST['category'] ?? '');
    if ($bookId <= 0 || empty($category)) {
        $errors[] = 'Please select a valid book and category.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM book_requests WHERE user_id = :uid AND book_id = :bid AND status IN ('Pending', 'In Progress')");
            $stmt->execute([':uid' => $_SESSION['user_id'], ':bid' => $bookId]);
            if ($stmt->fetch()) {
                $errors[] = 'You already have an active request for this book.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO book_requests (user_id, book_id, category, status) VALUES (:uid, :bid, :cat, 'Pending')");
                $stmt->execute([':uid' => $_SESSION['user_id'], ':bid' => $bookId, ':cat' => $category]);
                $success = 'Book request submitted successfully!';
            }
        } catch (PDOException $e) {
            error_log("Request Error: " . $e->getMessage());
            $errors[] = 'Failed to submit request. Please try again.';
        }
    }
}

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $selectedCategory = trim($_GET['category']);
    try {
        $stmt = $pdo->prepare("SELECT id, title, author, category FROM books WHERE category = :cat ORDER BY title ASC");
        $stmt->execute([':cat' => $selectedCategory]);
        $books = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Fetch Books Error: " . $e->getMessage());
    }
}

$pageTitle = 'Request a Book';
include __DIR__ . '/../includes/header.php';
?>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="../index.php" class="navbar-brand"><i class="fas fa-book-open"></i> BookHub</a>
        <ul class="navbar-links">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="request_book.php" class="active"><i class="fas fa-plus-circle"></i> Request Book</a></li>
            <li><a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
</nav>

<div class="main-container">
    <div class="page-header">
        <h1><i class="fas fa-search" style="color:var(--primary);"></i> Request a Book</h1>
        <p>Select a category, browse books from Google Books API, and submit your request</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i><div><?php echo e(implode('<br>', $errors)); ?></div></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i><div><?php echo e($success); ?></div></div>
    <?php endif; ?>

    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h2><i class="fas fa-user-circle"></i> Your Information</h2></div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group" style="margin-bottom:0;">
                <label>Username</label>
                <input type="text" class="form-control" value="<?php echo e($_SESSION['username']); ?>" readonly style="opacity:0.7;">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label>Email</label>
                <input type="text" class="form-control" value="<?php echo e($_SESSION['email']); ?>" readonly style="opacity:0.7;">
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h2><i class="fas fa-layer-group"></i> Select Category</h2></div>
        <div class="form-group">
            <label for="category-select">Book Category</label>
            <select class="form-control" id="category-select">
                <option value="">-- Choose a Category --</option>
                <option value="App Development" <?php echo $selectedCategory === 'App Development' ? 'selected' : ''; ?>>App Development</option>
                <option value="Mobile Development" <?php echo $selectedCategory === 'Mobile Development' ? 'selected' : ''; ?>>Mobile Development</option>
                <option value="Artificial Intelligence" <?php echo $selectedCategory === 'Artificial Intelligence' ? 'selected' : ''; ?>>Artificial Intelligence</option>
            </select>
        </div>
        <button type="button" class="btn btn-primary" id="btn-fetch-books" onclick="fetchBooks()">
            <i class="fas fa-cloud-download-alt"></i> Fetch Books from Google
        </button>
        <span id="fetch-status" style="margin-left:1rem; color:var(--text-muted); font-size:0.85rem;"></span>
    </div>

    <div class="card" id="books-section" style="display:<?php echo !empty($books) ? 'block' : 'none'; ?>;">
        <div class="card-header"><h2><i class="fas fa-book"></i> Available Books — <span id="category-label"><?php echo e($selectedCategory); ?></span></h2></div>
        <div id="books-container">
            <?php if (!empty($books)): ?>
                <form method="POST" action="" id="request-form">
                    <input type="hidden" name="category" value="<?php echo e($selectedCategory); ?>">
                    <div class="book-grid" id="book-grid">
                        <?php foreach ($books as $book): ?>
                            <label class="book-item" data-book-id="<?php echo (int)$book['id']; ?>">
                                <input type="radio" name="book_id" value="<?php echo (int)$book['id']; ?>" style="display:none;" required>
                                <div class="book-title"><?php echo e($book['title']); ?></div>
                                <div class="book-author"><i class="fas fa-pen-nib"></i> <?php echo e($book['author']); ?></div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div style="margin-top:1.5rem; text-align:center;">
                        <button type="submit" class="btn btn-success btn-lg" id="btn-submit-request"><i class="fas fa-paper-plane"></i> Submit Request</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        <div id="loading-books" class="loading-spinner" style="display:none;"><div class="spinner"></div></div>
    </div>
</div>

<script>
document.addEventListener('click', function(e) {
    var item = e.target.closest('.book-item');
    if (item) {
        document.querySelectorAll('.book-item').forEach(function(el) { el.classList.remove('selected'); });
        item.classList.add('selected');
        var radio = item.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
    }
});

function fetchBooks() {
    var category = document.getElementById('category-select').value;
    if (!category) { alert('Please select a category first.'); return; }
    var section = document.getElementById('books-section');
    var container = document.getElementById('books-container');
    var loading = document.getElementById('loading-books');
    var status = document.getElementById('fetch-status');
    var label = document.getElementById('category-label');
    section.style.display = 'block';
    container.innerHTML = '';
    loading.style.display = 'flex';
    status.textContent = 'Fetching books from Google Books API...';
    label.textContent = category;
    var formData = new FormData();
    formData.append('category', category);
    fetch('../api/fetch_books.php', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        loading.style.display = 'none';
        if (data.error) { status.textContent = data.error; container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>' + data.error + '</h3></div>'; return; }
        if (data.books && data.books.length > 0) {
            status.textContent = data.books.length + ' books loaded.';
            var html = '<form method="POST" action="" id="request-form"><input type="hidden" name="category" value="' + esc(category) + '"><div class="book-grid" id="book-grid">';
            data.books.forEach(function(book) {
                html += '<label class="book-item" data-book-id="' + book.id + '"><input type="radio" name="book_id" value="' + book.id + '" style="display:none;" required><div class="book-title">' + esc(book.title) + '</div><div class="book-author"><i class="fas fa-pen-nib"></i> ' + esc(book.author) + '</div></label>';
            });
            html += '</div><div style="margin-top:1.5rem; text-align:center;"><button type="submit" class="btn btn-success btn-lg" id="btn-submit-request"><i class="fas fa-paper-plane"></i> Submit Request</button></div></form>';
            container.innerHTML = html;
        } else { status.textContent = 'No books found.'; container.innerHTML = '<div class="empty-state"><i class="fas fa-search"></i><h3>No books found</h3></div>'; }
    })
    .catch(function(err) { loading.style.display = 'none'; status.textContent = 'Failed to fetch books.'; console.error(err); });
}
function esc(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
