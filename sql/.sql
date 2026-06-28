-- Create Database
CREATE DATABASE issue_reporting_portal;

-- Use Database
USE issue_reporting_portal;

-- =========================
-- USERS TABLE
-- =========================
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'employee', 'student') DEFAULT 'employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- DEPARTMENTS TABLE
-- =========================
CREATE TABLE departments (
    department_id INT PRIMARY KEY AUTO_INCREMENT,
    department_name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- ISSUE CATEGORY TABLE
-- =========================
CREATE TABLE issue_categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- ISSUES TABLE
-- =========================
CREATE TABLE issues (
    issue_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    
    reported_by INT NOT NULL,
    assigned_to INT NULL,
    
    department_id INT,
    category_id INT,

    priority ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
    
    status ENUM('Open', 'In Progress', 'Resolved', 'Closed') DEFAULT 'Open',

    attachment VARCHAR(255),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
    ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (reported_by) REFERENCES users(user_id)
        ON DELETE CASCADE,

    FOREIGN KEY (assigned_to) REFERENCES users(user_id)
        ON DELETE SET NULL,

    FOREIGN KEY (department_id) REFERENCES departments(department_id)
        ON DELETE SET NULL,

    FOREIGN KEY (category_id) REFERENCES issue_categories(category_id)
        ON DELETE SET NULL
);

-- =========================
-- ISSUE COMMENTS TABLE
-- =========================
CREATE TABLE issue_comments (
    comment_id INT PRIMARY KEY AUTO_INCREMENT,
    issue_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    commented_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (issue_id) REFERENCES issues(issue_id)
        ON DELETE CASCADE,

    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
);

-- =========================
-- ISSUE HISTORY TABLE
-- =========================
CREATE TABLE issue_history (
    history_id INT PRIMARY KEY AUTO_INCREMENT,
    issue_id INT NOT NULL,
    changed_by INT NOT NULL,

    old_status ENUM('Open', 'In Progress', 'Resolved', 'Closed'),
    new_status ENUM('Open', 'In Progress', 'Resolved', 'Closed'),

    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (issue_id) REFERENCES issues(issue_id)
        ON DELETE CASCADE,

    FOREIGN KEY (changed_by) REFERENCES users(user_id)
        ON DELETE CASCADE
);

-- =========================
-- NOTIFICATIONS TABLE
-- =========================
CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
);

-- =========================
-- INDEXES FOR PERFORMANCE
-- =========================
CREATE INDEX idx_issue_status ON issues(status);
CREATE INDEX idx_issue_priority ON issues(priority);
CREATE INDEX idx_issue_reported_by ON issues(reported_by);
CREATE INDEX idx_issue_assigned_to ON issues(assigned_to);