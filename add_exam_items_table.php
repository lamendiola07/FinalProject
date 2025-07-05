<?php
require_once 'config.php';

try {
    // Create exam_items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS exam_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        term ENUM('midterm', 'final') NOT NULL,
        name VARCHAR(255) NOT NULL,
        max_score DECIMAL(5,2) NOT NULL DEFAULT 100.00,
        date_given DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    )");
    
    // Create recitation_items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS recitation_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        term ENUM('midterm', 'final') NOT NULL,
        name VARCHAR(255) NOT NULL,
        max_score DECIMAL(5,2) NOT NULL DEFAULT 100.00,
        date_given DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    )");
    
    // Modify individual_scores table to include 'exam' and 'recitation' as valid item_types
    $pdo->exec("ALTER TABLE individual_scores MODIFY COLUMN item_type ENUM('quiz', 'activity', 'assignment', 'exam', 'recitation') NOT NULL");
    
    echo "Exam and recitation items tables created and individual_scores table updated successfully!";
} catch(PDOException $e) {
    echo "Error updating tables: " . $e->getMessage();
}
?>