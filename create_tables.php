<?php
require_once 'config.php';

try {
    // Create courses table
    $pdo->exec("CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(20) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        section_code VARCHAR(20) NOT NULL,
        schedule VARCHAR(50) NOT NULL,
        school_year VARCHAR(20) NOT NULL,
        semester VARCHAR(20) NOT NULL,
        faculty_id VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_course (code, section_code, school_year, semester, faculty_id)
    );");
    
    // Create students table
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_number VARCHAR(50) NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        UNIQUE KEY unique_student (student_number)
    );");
    
    // Create course_students table (for enrollment)
    $pdo->exec("CREATE TABLE IF NOT EXISTS course_students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        student_id INT NOT NULL,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        UNIQUE KEY unique_enrollment (course_id, student_id)
    );");
    
    // Create grades table
    $pdo->exec("CREATE TABLE IF NOT EXISTS grades (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_student_id INT NOT NULL,
        first_grade DECIMAL(4,2) NULL,
        second_grade DECIMAL(4,2) NULL,
        computed_grade DECIMAL(4,2) NULL,
        final_grade DECIMAL(4,2) NULL,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (course_student_id) REFERENCES course_students(id) ON DELETE CASCADE
    );");
    
    echo "Tables created successfully!";
} catch(PDOException $e) {
    die("Error creating tables: " . $e->getMessage());
}
?>