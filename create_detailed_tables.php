<?php
require_once 'config.php';

try {
    // Create detailed_grades table for storing individual grade components
    $pdo->exec("CREATE TABLE IF NOT EXISTS detailed_grades (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_student_id INT NOT NULL,
        term ENUM('midterm', 'final') NOT NULL,
        component ENUM('quiz', 'activity', 'assignment', 'recitation', 'exam') NOT NULL,
        score DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (course_student_id) REFERENCES course_students(id) ON DELETE CASCADE,
        UNIQUE KEY unique_grade_component (course_student_id, term, component)
    );");
    
    // Create attendance_records table
    $pdo->exec("CREATE TABLE IF NOT EXISTS attendance_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_student_id INT NOT NULL,
        meeting_number INT NOT NULL,
        meeting_date DATE NOT NULL,
        status ENUM('present', 'absent') NOT NULL DEFAULT 'present',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (course_student_id) REFERENCES course_students(id) ON DELETE CASCADE,
        UNIQUE KEY unique_attendance (course_student_id, meeting_number)
    );");
    
    echo "Detailed tables created successfully!";
} catch(PDOException $e) {
    die("Error creating detailed tables: " . $e->getMessage());
}
?>