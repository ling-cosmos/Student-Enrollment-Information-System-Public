-- ============================================================
--  SEIS - Student Enrollment Information System
--  Full Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS seis_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE seis_db;

-- Academic years (e.g. "2024-2025")
CREATE TABLE IF NOT EXISTS academic_years (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    year_label  VARCHAR(20)  NOT NULL UNIQUE,
    is_active   TINYINT(1)   NOT NULL DEFAULT 0,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Semesters tied to an academic year
CREATE TABLE IF NOT EXISTS semesters (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    academic_year_id INT UNSIGNED NOT NULL,
    name             VARCHAR(50)  NOT NULL,
    start_date       DATE         NOT NULL,
    end_date         DATE         NOT NULL,
    is_active        TINYINT(1)   NOT NULL DEFAULT 0,
    created_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Courses/subjects
CREATE TABLE IF NOT EXISTS courses (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_code  VARCHAR(20)   NOT NULL UNIQUE,
    course_name  VARCHAR(100)  NOT NULL,
    units        TINYINT       NOT NULL DEFAULT 3,
    capacity     INT           NOT NULL DEFAULT 40,
    description  TEXT,
    is_active    TINYINT(1)    NOT NULL DEFAULT 1,
    created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Students master table
CREATE TABLE IF NOT EXISTS students (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_no     VARCHAR(20)   NOT NULL UNIQUE,
    first_name     VARCHAR(50)   NOT NULL,
    middle_name    VARCHAR(50),
    last_name      VARCHAR(50)   NOT NULL,
    email          VARCHAR(100)  NOT NULL UNIQUE,
    phone          VARCHAR(20),
    birthdate      DATE,
    gender         ENUM('Male','Female','Other'),
    address        TEXT,
    photo          VARCHAR(255)  DEFAULT 'default.png',
    status         ENUM('Active','Inactive','Graduated','Dropped') NOT NULL DEFAULT 'Active',
    created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Enrollments (student + course + semester)
CREATE TABLE IF NOT EXISTS enrollments (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id   INT UNSIGNED NOT NULL,
    course_id    INT UNSIGNED NOT NULL,
    semester_id  INT UNSIGNED NOT NULL,
    status       ENUM('Enrolled','Dropped','Completed') NOT NULL DEFAULT 'Enrolled',
    enrolled_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    dropped_at   TIMESTAMP    NULL,
    UNIQUE KEY uq_enrollment (student_id, course_id, semester_id),
    FOREIGN KEY (student_id)  REFERENCES students(id)  ON DELETE CASCADE,
    FOREIGN KEY (course_id)   REFERENCES courses(id)   ON DELETE CASCADE,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- System users
CREATE TABLE IF NOT EXISTS users (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username     VARCHAR(50)   NOT NULL UNIQUE,
    password     VARCHAR(255)  NOT NULL,
    full_name    VARCHAR(100)  NOT NULL,
    email        VARCHAR(100)  NOT NULL UNIQUE,
    role         ENUM('admin','registrar','viewer') NOT NULL DEFAULT 'registrar',
    is_active    TINYINT(1)    NOT NULL DEFAULT 1,
    last_login   TIMESTAMP     NULL,
    created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---- SEED DATA ----
INSERT INTO academic_years (year_label, is_active) VALUES ('2024-2025', 1);

INSERT INTO semesters (academic_year_id, name, start_date, end_date, is_active) VALUES
(1, '1st Semester', '2024-08-01', '2024-12-31', 0),
(1, '2nd Semester', '2025-01-01', '2025-05-31', 1);

INSERT INTO courses (course_code, course_name, units, capacity) VALUES
('CS101',   'Introduction to Computing',         3, 40),
('CS102',   'Computer Programming 1',            3, 35),
('MATH101', 'Mathematics in the Modern World',   3, 45),
('ENG101',  'Purposive Communication',           3, 40),
('PE101',   'Physical Education 1',              2, 50);

-- No administrator credentials are committed to this public repository.
-- Create the first administrator locally with a unique password hash before use.
