CREATE DATABASE IF NOT EXISTS library_db CHARACTER SET utf8mb4;
USE library_db;

CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_no VARCHAR(30) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  course VARCHAR(100),
  email VARCHAR(100),
  password VARCHAR(255) NOT NULL
);

CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

CREATE TABLE books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  isbn VARCHAR(30) NOT NULL UNIQUE,
  title VARCHAR(200) NOT NULL,
  author VARCHAR(100) NOT NULL,
  copies INT NOT NULL DEFAULT 1,
  available INT NOT NULL DEFAULT 1
);

CREATE TABLE loans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  book_id INT NOT NULL,
  issued_on DATE NOT NULL,
  due_on DATE NOT NULL,
  returned_on DATE NULL,
  FOREIGN KEY (student_id) REFERENCES students(id),
  FOREIGN KEY (book_id) REFERENCES books(id)
);

INSERT INTO books (isbn, title, author, copies, available) VALUES
('9780132350884','Clean Code','Robert C. Martin',3,3),
('9780596517748','JavaScript: The Good Parts','Douglas Crockford',2,2);
