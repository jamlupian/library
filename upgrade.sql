-- Run this ONLY if you already imported the old database.sql
USE library_db;

ALTER TABLE students ADD COLUMN password VARCHAR(255) NULL;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);
-- Old sample students have no password; delete them (or re-add them from the admin panel).
-- The admin account (admin / adminlibrary) is created automatically on first admin login.
