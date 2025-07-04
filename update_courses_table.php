<?php
require_once 'config.php';

try {
    // Add passing_grade and grade_computation_method columns to courses table
    $pdo->exec("ALTER TABLE courses 
        ADD COLUMN passing_grade DECIMAL(4,2) NOT NULL DEFAULT 75.00,
        ADD COLUMN grade_computation_method ENUM('base_50', 'base_0') NOT NULL DEFAULT 'base_50'");
    
    echo "Courses table updated successfully!";
} catch(PDOException $e) {
    die("Error updating courses table: " . $e->getMessage());
}
?>