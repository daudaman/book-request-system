#  Daud Aman
#  22P-9189
#  BCS-8A
#  ASSIGNMENT # 3

#  Book Request Management System

A secure, multi-role web application built with **pure PHP** and **MySQL** that integrates with the **Google Books API**. Three user roles (User, Admin, Super Admin) provide role-based access control for managing book requests.

---

##  Features

###  Authentication System
- Secure registration and login with `password_hash()` / `password_verify()`
- PHP session-based authentication
- Role-based access control on every page
- Complete session destruction on logout

###  User Panel
- Register and login
- Browse books fetched from **Google Books API**
- Submit book requests (auto-filled username & email)
- View personal dashboard with request statistics
- Cancel pending requests
- Rate-limited API access (max 5 calls per 24 hours)

###  Admin Panel (Read-Only)
- Separate admin login
- Dashboard with statistics (total users, requests, in-progress, completed)
- View all book requests
- View all books in the database

###  Super Admin Panel (Full Control)
- Manage all book requests (update status, delete)
- Manage users (view, reset passwords, delete)
- Manage admins (add new, delete — cannot delete super admin)

###  Google Books API Integration
- Fetches books by category (App Development, Mobile Development, Artificial Intelligence)
- Silent insertion into database (no duplicate entries)
- Rate limiting: max 5 API calls per user per 24 hours
- Graceful fallback to database if API fails

---

##  Setup Instructions

### Prerequisites
- **XAMPP** or **WAMP** installed on your machine
- **PHP 7.4+** with PDO MySQL extension
- **MySQL 5.7+**
- **Internet connection** (for Google Books API)

### Step 1: Clone or Copy Files
Copy the `book-request-system` folder to your XAMPP/WAMP web root:
```
XAMPP: C:\xampp\htdocs\book-request-system\
WAMP:  C:\wamp64\www\book-request-system\
```

### Step 2: Start Services
1. Open **XAMPP Control Panel**
2. Start **Apache** and **MySQL**

### Step 3: Import Database
1. Open **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Click **"Import"** tab
3. Choose `database.sql` from the project root
4. Click **"Go"** to execute

Or via command line:
```bash
mysql -u root < database.sql
```

### Step 4: Configure Database
Edit `config/db.php` if your MySQL credentials differ:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'book_request_system');
define('DB_USER', 'root');
define('DB_PASS', '');  // Set your MySQL password if any
```

### Step 5: Run the Project
Open your browser and navigate to:
```
http://localhost/book-request-system/
```

---

## 🔑 Default Credentials

| Role | Username | Password |
|------|----------|----------|
| Super Admin | `superadmin` | `superadmin123` |
| Admin | `admin` | `admin123` |
| User | Register a new account | — |

---

##  Project Structure

```
book-request-system/
│
├── config/
│   └── db.php                  # Database connection (PDO)
│
├── includes/
│   ├── header.php              # HTML header + CSS
│   ├── footer.php              # HTML footer
│   └── auth.php                # Authentication & authorization
│
├── user/
│   ├── register.php            # User registration
│   ├── login.php               # User login
│   ├── dashboard.php           # User dashboard
│   ├── request_book.php        # Book request form
│   └── cancel_request.php      # Cancel pending request
│
├── admin/
│   ├── login.php               # Admin login
│   └── dashboard.php           # Admin dashboard (read-only)
│
├── superadmin/
│   ├── login.php               # Super admin login
│   ├── dashboard.php           # Super admin dashboard
│   ├── manage_requests.php     # CRUD for requests
│   ├── manage_users.php        # User management
│   └── manage_admins.php       # Admin management
│
├── api/
│   └── fetch_books.php         # Google Books API handler
│
├── index.php                   # Landing page
├── logout.php                  # Session destroyer
├── database.sql                # Database schema + seed data
└── README.md                   # This file
```

---

##  Security Features

- **PDO Prepared Statements** — prevents SQL injection
- **`password_hash()` / `password_verify()`** — secure password storage
- **`htmlspecialchars()`** — prevents XSS attacks
- **Role-based access control** — enforced on every page
- **Session validation** — prevents unauthorized URL access
- **Rate limiting** — prevents API abuse

---

##  Technologies Used

- PHP 7.4+ (pure PHP, no frameworks)
- MySQL with PDO
- Google Books API
- HTML5, CSS3, JavaScript (Vanilla)
- Font Awesome 6 Icons
- Google Fonts (Inter)

---

##  Database Tables

| Table | Purpose |
|-------|---------|
| `users` | Registered users (id, username, email, password, role) |
| `admins` | Admin/Super Admin accounts (id, username, password, role) |
| `books` | Books fetched from Google API (id, title, author, category) |
| `book_requests` | User book requests (id, user_id, book_id, category, status) |
| `sapi_rate_limits` | Tracks API call limits per user |

---

