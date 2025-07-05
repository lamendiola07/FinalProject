<?php
require_once 'config.php';

try {
    // Add grade_weights column to courses table
    $pdo->exec("ALTER TABLE courses 
        ADD COLUMN grade_weights TEXT NULL");
    
    echo "Grade weights column added successfully!";
} catch(PDOException $e) {
    die("Error adding grade_weights column: " . $e->getMessage());
}
?>