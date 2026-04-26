<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();

// Redirect logged-in users to their dashboards
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'user':
            header('Location: user/dashboard.php');
            exit;
        case 'admin':
            header('Location: admin/dashboard.php');
            exit;
        case 'superadmin':
            header('Location: superadmin/dashboard.php');
            exit;
    }
}

$pageTitle = 'Welcome';
include 'includes/header.php';
?>

<div class="auth-wrapper" style="flex-direction: column; gap: 3rem;">
    <!-- Hero Section -->
    <div style="text-align:center; max-width:700px; animation: fadeUp 0.6s ease;">
        <div style="margin-bottom:1.5rem;">
            <i class="fas fa-book-open" style="font-size:3.5rem; background:linear-gradient(135deg, var(--primary), var(--accent)); -webkit-background-clip:text; -webkit-text-fill-color:transparent;"></i>
        </div>
        <h1 style="font-size:3rem; font-weight:800; margin-bottom:1rem; line-height:1.2;">
            <span style="background:linear-gradient(135deg, var(--primary), var(--accent)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">BookHub</span>
        </h1>
        <p style="font-size:1.2rem; color:var(--text-secondary); margin-bottom:0.5rem;">
            Book Request Management System
        </p>
        <p style="font-size:0.95rem; color:var(--text-muted); max-width:500px; margin:0 auto;">
            Search books from Google Books API, submit requests, and manage your reading journey with role-based access control.
        </p>
    </div>

    <!-- Role Cards -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:1.5rem; max-width:900px; width:100%; animation: fadeUp 0.8s ease;">
        
        <!-- User Card -->
        <div class="card" style="text-align:center; padding:2.5rem 2rem;">
            <div style="width:70px; height:70px; border-radius:18px; background:rgba(108,99,255,0.12); display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem;">
                <i class="fas fa-user" style="font-size:1.8rem; color:var(--primary);"></i>
            </div>
            <h3 style="margin-bottom:0.5rem; font-size:1.2rem;">User Portal</h3>
            <p style="color:var(--text-muted); font-size:0.85rem; margin-bottom:1.5rem;">Browse books, submit requests, and track your reading progress.</p>
            <a href="user/login.php" class="btn btn-primary btn-block" id="btn-user-login">
                <i class="fas fa-sign-in-alt"></i> User Login
            </a>
            <div style="margin-top:0.8rem;">
                <a href="user/register.php" style="color:var(--primary); text-decoration:none; font-size:0.85rem; font-weight:500;">
                    Create Account <i class="fas fa-arrow-right" style="font-size:0.75rem;"></i>
                </a>
            </div>
        </div>

        <!-- Admin Card -->
        <div class="card" style="text-align:center; padding:2.5rem 2rem;">
            <div style="width:70px; height:70px; border-radius:18px; background:rgba(0,210,255,0.12); display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem;">
                <i class="fas fa-user-shield" style="font-size:1.8rem; color:var(--accent);"></i>
            </div>
            <h3 style="margin-bottom:0.5rem; font-size:1.2rem;">Admin Panel</h3>
            <p style="color:var(--text-muted); font-size:0.85rem; margin-bottom:1.5rem;">View statistics, monitor requests, and manage book inventory.</p>
            <a href="admin/login.php" class="btn btn-block" id="btn-admin-login" style="background:linear-gradient(135deg, var(--accent), #0099CC); color:#fff; box-shadow:0 4px 15px rgba(0,210,255,0.3);">
                <i class="fas fa-sign-in-alt"></i> Admin Login
            </a>
        </div>

        <!-- Super Admin Card -->
        <div class="card" style="text-align:center; padding:2.5rem 2rem;">
            <div style="width:70px; height:70px; border-radius:18px; background:rgba(255,101,132,0.12); display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem;">
                <i class="fas fa-crown" style="font-size:1.8rem; color:var(--secondary);"></i>
            </div>
            <h3 style="margin-bottom:0.5rem; font-size:1.2rem;">Super Admin</h3>
            <p style="color:var(--text-muted); font-size:0.85rem; margin-bottom:1.5rem;">Full system control — manage users, admins, and all requests.</p>
            <a href="superadmin/login.php" class="btn btn-block" id="btn-superadmin-login" style="background:linear-gradient(135deg, var(--secondary), #CC3355); color:#fff; box-shadow:0 4px 15px rgba(255,101,132,0.3);">
                <i class="fas fa-sign-in-alt"></i> Super Admin Login
            </a>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
