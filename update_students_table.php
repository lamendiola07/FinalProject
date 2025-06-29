<?php
require_once 'config.php';

try {
    // Add course_code and section_code columns if they don't exist
    $pdo->exec("ALTER TABLE students 
                ADD COLUMN IF NOT EXISTS course_code VARCHAR(20) NULL,
                ADD COLUMN IF NOT EXISTS section_code VARCHAR(20) NULL");
    
    echo "Students table updated successfully!";
} catch(PDOException $e) {
    die("Error updating students table: " . $e->getMessage());
}
?>