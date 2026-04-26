<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = isset($pageTitle) ? $pageTitle : 'Book Request System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Book Request Management System - Search, request, and manage books with ease">
    <title><?php echo htmlspecialchars($pageTitle); ?> | BookHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* ========== CSS RESET & VARIABLES ========== */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #6C63FF;
            --primary-dark: #5A52D5;
            --primary-light: #8B85FF;
            --secondary: #FF6584;
            --accent: #00D2FF;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --info: #3B82F6;

            --bg-dark: #0F0E17;
            --bg-card: #1A1A2E;
            --bg-card-hover: #1F1F35;
            --bg-input: #16213E;
            --bg-glass: rgba(26, 26, 46, 0.8);

            --text-primary: #FFFFFE;
            --text-secondary: #A7A9BE;
            --text-muted: #6B6D7B;

            --border-color: rgba(108, 99, 255, 0.2);
            --border-radius: 12px;
            --border-radius-lg: 20px;
            --border-radius-sm: 8px;

            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.2);
            --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.3);
            --shadow-lg: 0 8px 40px rgba(0, 0, 0, 0.4);
            --shadow-glow: 0 0 30px rgba(108, 99, 255, 0.3);

            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100vh;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Animated background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(ellipse at 20% 50%, rgba(108, 99, 255, 0.08) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(0, 210, 255, 0.06) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 80%, rgba(255, 101, 132, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        /* ========== NAVIGATION ========== */
        .navbar {
            background: var(--bg-glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-inner {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .navbar-brand i {
            font-size: 1.6rem;
            -webkit-text-fill-color: var(--primary);
        }

        .navbar-links {
            display: flex;
            align-items: center;
            gap: 8px;
            list-style: none;
        }

        .navbar-links a {
            color: var(--text-secondary);
            text-decoration: none;
            padding: 8px 16px;
            border-radius: var(--border-radius-sm);
            font-size: 0.9rem;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .navbar-links a:hover,
        .navbar-links a.active {
            color: var(--text-primary);
            background: rgba(108, 99, 255, 0.15);
        }

        .navbar-links .btn-logout {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .navbar-links .btn-logout:hover {
            background: var(--danger);
            color: #fff;
        }

        /* ========== MAIN CONTAINER ========== */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        /* ========== CARDS ========== */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            transition: var(--transition);
        }

        .card:hover {
            border-color: rgba(108, 99, 255, 0.4);
            box-shadow: var(--shadow-glow);
        }

        .card-header {
            margin-bottom: 1.5rem;
        }

        .card-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-header h2 i {
            color: var(--primary);
        }

        /* ========== STATS GRID ========== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-lg);
            padding: 1.8rem;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            border-radius: 4px 0 0 4px;
        }

        .stat-card.purple::before { background: var(--primary); }
        .stat-card.blue::before { background: var(--info); }
        .stat-card.green::before { background: var(--success); }
        .stat-card.orange::before { background: var(--warning); }
        .stat-card.red::before { background: var(--danger); }
        .stat-card.cyan::before { background: var(--accent); }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 1rem;
        }

        .stat-card.purple .stat-icon { background: rgba(108, 99, 255, 0.15); color: var(--primary); }
        .stat-card.blue .stat-icon { background: rgba(59, 130, 246, 0.15); color: var(--info); }
        .stat-card.green .stat-icon { background: rgba(16, 185, 129, 0.15); color: var(--success); }
        .stat-card.orange .stat-icon { background: rgba(245, 158, 11, 0.15); color: var(--warning); }
        .stat-card.red .stat-icon { background: rgba(239, 68, 68, 0.15); color: var(--danger); }
        .stat-card.cyan .stat-icon { background: rgba(0, 210, 255, 0.15); color: var(--accent); }

        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.3rem;
        }

        .stat-card .stat-label {
            color: var(--text-secondary);
            font-size: 0.85rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ========== TABLES ========== */
        .table-wrapper {
            overflow-x: auto;
            border-radius: var(--border-radius);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background: rgba(108, 99, 255, 0.1);
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid rgba(108, 99, 255, 0.08);
            font-size: 0.9rem;
            color: var(--text-primary);
        }

        tbody tr {
            transition: var(--transition);
        }

        tbody tr:hover {
            background: rgba(108, 99, 255, 0.05);
        }

        /* ========== STATUS BADGES ========== */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-pending {
            background: rgba(245, 158, 11, 0.15);
            color: var(--warning);
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .badge-inprogress {
            background: rgba(59, 130, 246, 0.15);
            color: var(--info);
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .badge-completed {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge-rejected {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .badge-user { background: rgba(108, 99, 255, 0.15); color: var(--primary); border: 1px solid rgba(108, 99, 255, 0.3); }
        .badge-admin { background: rgba(0, 210, 255, 0.15); color: var(--accent); border: 1px solid rgba(0, 210, 255, 0.3); }
        .badge-superadmin { background: rgba(255, 101, 132, 0.15); color: var(--secondary); border: 1px solid rgba(255, 101, 132, 0.3); }

        /* ========== FORMS ========== */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-sm);
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.95rem;
            transition: var(--transition);
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.15);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        select.form-control {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23A7A9BE' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            padding-right: 40px;
        }

        select.form-control option {
            background: var(--bg-card);
            color: var(--text-primary);
        }

        /* ========== BUTTONS ========== */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border: none;
            border-radius: var(--border-radius-sm);
            font-family: inherit;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            box-shadow: 0 4px 15px rgba(108, 99, 255, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(108, 99, 255, 0.5);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success), #059669);
            color: #fff;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(16, 185, 129, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #DC2626);
            color: #fff;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(239, 68, 68, 0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning), #D97706);
            color: #fff;
        }

        .btn-info {
            background: linear-gradient(135deg, var(--info), #2563EB);
            color: #fff;
        }

        .btn-outline {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(108, 99, 255, 0.08);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }

        .btn-lg {
            padding: 14px 28px;
            font-size: 1rem;
        }

        .btn-block {
            width: 100%;
            justify-content: center;
        }

        /* ========== ALERTS / MESSAGES ========== */
        .alert {
            padding: 14px 20px;
            border-radius: var(--border-radius-sm);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: var(--success);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--danger);
        }

        .alert-info {
            background: rgba(59, 130, 246, 0.12);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: var(--info);
        }

        .alert-warning {
            background: rgba(245, 158, 11, 0.12);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: var(--warning);
        }

        /* ========== AUTH PAGES ========== */
        .auth-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }

        .auth-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-lg);
            padding: 2.5rem;
            width: 100%;
            max-width: 440px;
            box-shadow: var(--shadow-lg);
            animation: fadeUp 0.5s ease;
        }

        .auth-card .auth-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-card .auth-logo h1 {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .auth-card .auth-logo p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .auth-card .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .auth-card .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .auth-card .auth-footer a:hover {
            text-decoration: underline;
        }

        /* ========== PAGE TITLE ========== */
        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: var(--text-secondary);
            font-size: 1rem;
        }

        .page-header .greeting {
            color: var(--primary-light);
            font-weight: 600;
        }

        /* ========== EMPTY STATE ========== */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--border-color);
        }

        .empty-state h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
        }

        /* ========== BOOK GRID ========== */
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.2rem;
        }

        .book-item {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            transition: var(--transition);
            cursor: pointer;
            position: relative;
        }

        .book-item:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-glow);
        }

        .book-item.selected {
            border-color: var(--primary);
            background: rgba(108, 99, 255, 0.08);
        }

        .book-item.selected::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 12px;
            right: 12px;
            width: 28px;
            height: 28px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            color: #fff;
        }

        .book-item .book-title {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 0.4rem;
            line-height: 1.4;
        }

        .book-item .book-author {
            color: var(--text-secondary);
            font-size: 0.8rem;
        }

        /* ========== LOADING ========== */
        .loading-spinner {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--border-color);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        /* ========== ANIMATIONS ========== */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ========== ACTION BUTTONS ROW ========== */
        .actions-row {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 768px) {
            .navbar-inner {
                flex-direction: column;
                height: auto;
                padding: 1rem 0;
                gap: 10px;
            }

            .navbar-links {
                flex-wrap: wrap;
                justify-content: center;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }

            .main-container {
                padding: 1rem;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
