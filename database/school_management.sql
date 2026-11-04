-- Create the database (utf8mb4 supports all characters, including accents)
CREATE DATABASE IF NOT EXISTS school_management
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Tell MySQL to use it for the statements below
USE school_management;

-- Users table: one table for all four roles
CREATE TABLE IF NOT EXISTS users (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name  VARCHAR(100) NOT NULL,
    email      VARCHAR(255) NOT NULL,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('student', 'instructor', 'parent', 'admin') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initial administrator (the password column holds a HASH, never the real password)
INSERT INTO users (full_name, email, password, role)
VALUES ('System Administrator', 'admin@crestfield.test', '$2y$12$C.klxb4kGpMtdFaxZpBJWe0ZkUwF.MjHh4Dh9KhilUqi5RHsTGBKW', 'admin');