<?php
require_once 'config.php';

try {
    // Create quiz_items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS quiz_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        term ENUM('midterm', 'final') NOT NULL,
        name VARCHAR(255) NOT NULL,
        max_score DECIMAL(5,2) NOT NULL DEFAULT 100.00,
        date_given DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    )");
    
    // Create activity_items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        term ENUM('midterm', 'final') NOT NULL,
        name VARCHAR(255) NOT NULL,
        max_score DECIMAL(5,2) NOT NULL DEFAULT 100.00,
        date_given DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    )");
    
    // Create assignment_items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS assignment_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        term ENUM('midterm', 'final') NOT NULL,
        name VARCHAR(255) NOT NULL,
        max_score DECIMAL(5,2) NOT NULL DEFAULT 100.00,
        date_given DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    )");
    
    // Create individual_scores table for storing student scores on individual items
    $pdo->exec("CREATE TABLE IF NOT EXISTS individual_scores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_student_id INT NOT NULL,
        item_type ENUM('quiz', 'activity', 'assignment') NOT NULL,
        item_id INT NOT NULL,
        score DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (course_student_id) REFERENCES course_students(id) ON DELETE CASCADE,
        UNIQUE KEY unique_score (course_student_id, item_type, item_id)
    )");
    
    echo "Individual items tables created successfully!";
} catch(PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?>