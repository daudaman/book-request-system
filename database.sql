-- Book Request Management System Database
-- Import this file into MySQL via phpMyAdmin or command line

CREATE DATABASE IF NOT EXISTS book_request_system;
USE book_request_system;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admins table (separate credentials for admin and super admin)
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'superadmin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Books table (populated from Google Books API)
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    author VARCHAR(500) DEFAULT 'Unknown',
    category VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_book (title(255), author(255), category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Book requests table
CREATE TABLE IF NOT EXISTS book_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    category VARCHAR(100) NOT NULL,
    status ENUM('Pending', 'In Progress', 'Completed', 'Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- API rate limiting table
CREATE TABLE IF NOT EXISTS api_rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    request_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default Super Admin (password: superadmin123)
INSERT INTO admins (username, password, role) VALUES 
('superadmin', '$2y$10$EzQZAg1nHo5EzXBrftHeqOUxN9Ll4NyTBukb3663KunrFtnE4Lu96', 'superadmin');

-- Insert default Admin (password: admin123)
INSERT INTO admins (username, password, role) VALUES 
('admin', '$2y$10$d/0lR4a3wfns6ZnHgB70zupFkqPujLfDAyVfeHDb/4.pfklZ0xH7q', 'admin');

-- Insert sample books
INSERT INTO books (title, author, category) VALUES
('Android Programming: The Big Nerd Ranch Guide', 'Bill Phillips', 'Mobile Development'),
('iOS Programming: Diving Deep', 'Aaron Hillegass', 'Mobile Development'),
('Flutter in Action', 'Eric Windmill', 'Mobile Development'),
('Head First Android Development', 'Dawn Griffiths', 'Mobile Development'),
('Artificial Intelligence: A Modern Approach', 'Stuart Russell', 'Artificial Intelligence'),
('Deep Learning', 'Ian Goodfellow', 'Artificial Intelligence'),
('Machine Learning Yearning', 'Andrew Ng', 'Artificial Intelligence'),
('Hands-On Machine Learning', 'Aurelien Geron', 'Artificial Intelligence'),
('Clean Code', 'Robert C. Martin', 'App Development'),
('The Pragmatic Programmer', 'David Thomas', 'App Development'),
('Design Patterns', 'Erich Gamma', 'App Development'),
('Refactoring', 'Martin Fowler', 'App Development');
