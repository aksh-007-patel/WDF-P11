-- Practical 11: Student Management CRUD with Search and Filter
-- Import: phpMyAdmin > Import > setup.sql

CREATE DATABASE IF NOT EXISTS student_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE student_db;

DROP TABLE IF EXISTS students;

CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  phone VARCHAR(15) NOT NULL,
  course VARCHAR(50) NOT NULL,
  age INT NOT NULL,
  gender ENUM('Male','Female','Other') NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT chk_age CHECK (age > 0 AND age < 120)
) ENGINE=InnoDB;

-- 22 fresh seeded records
INSERT INTO students (name, email, phone, course, age, gender, status) VALUES
('Yash Thakur', 'yash.thakur@example.com', '9898100101', 'BCA', 19, 'Male', 'active'),
('Hetvi Shah', 'hetvi.shah@example.com', '9898100102', 'BBA', 20, 'Female', 'active'),
('Parth Solanki', 'parth.solanki@example.com', '9898100103', 'B.Tech', 21, 'Male', 'active'),
('Riddhi Trivedi', 'riddhi.trivedi@example.com', '9898100104', 'MCA', 22, 'Female', 'active'),
('Dhruv Gandhi', 'dhruv.gandhi@example.com', '9898100105', 'B.Sc', 20, 'Male', 'active'),
('Foram Joshi', 'foram.joshi@example.com', '9898100106', 'M.Tech', 24, 'Female', 'active'),
('Kunj Parekh', 'kunj.parekh@example.com', '9898100107', 'BCA', 19, 'Male', 'active'),
('Misha Agarwal', 'misha.agarwal@example.com', '9898100108', 'MBA', 23, 'Female', 'active'),
('Tanay Gupta', 'tanay.gupta@example.com', '9898100109', 'B.Tech', 22, 'Male', 'active'),
('Avni Saxena', 'avni.saxena@example.com', '9898100110', 'BBA', 19, 'Female', 'active'),
('Rehan Shaikh', 'rehan.shaikh@example.com', '9898100111', 'MCA', 21, 'Male', 'active'),
('Jiya Chauhan', 'jiya.chauhan@example.com', '9898100112', 'BCA', 18, 'Female', 'active'),
('Vivaan Mishra', 'vivaan.mishra@example.com', '9898100113', 'M.Tech', 25, 'Male', 'active'),
('Aanya Bhatt', 'aanya.bhatt@example.com', '9898100114', 'B.Sc', 20, 'Female', 'active'),
('Daksh Jain', 'daksh.jain@example.com', '9898100115', 'BBA', 19, 'Male', 'active'),
('Navya Singhania', 'navya.singhania@example.com', '9898100116', 'MBA', 22, 'Female', 'active'),
('Kabir Malhotra', 'kabir.malhotra@example.com', '9898100117', 'B.Tech', 21, 'Male', 'active'),
('Sara Fernandes', 'sara.fernandes@example.com', '9898100118', 'MCA', 23, 'Female', 'active'),
('Ishaan Kulkarni', 'ishaan.kulkarni@example.com', '9898100119', 'BCA', 20, 'Male', 'active'),
('Meera Pillai', 'meera.pillai@example.com', '9898100120', 'BBA', 19, 'Female', 'active'),
('Devang Rathod', 'devang.rathod@example.com', '9898100121', 'B.Tech', 22, 'Male', 'active'),
('Krisha Desai', 'krisha.desai@example.com', '9898100122', 'MCA', 21, 'Female', 'active');
