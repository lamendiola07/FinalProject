<?php
require_once 'config.php';

try {
    // Add term column to individual_scores table
    $pdo->exec("ALTER TABLE individual_scores ADD COLUMN term ENUM('midterm', 'final') NOT NULL AFTER item_id");
    
    echo "Term column added to individual_scores table.<br>";
    
    // Update existing records with term information from respective item tables
    $componentTypes = ['quiz', 'activity', 'assignment', 'recitation', 'exam'];
    
    foreach ($componentTypes as $componentType) {
        $pdo->exec("UPDATE individual_scores ind_scores
                    JOIN {$componentType}_items i ON ind_scores.item_id = i.id
                    SET ind_scores.term = i.term
                    WHERE ind_scores.item_type = '{$componentType}'");
        
        echo "Updated term for {$componentType} scores.<br>";
    }
    
    // Update the unique key to include the term column
    $pdo->exec("ALTER TABLE individual_scores DROP INDEX unique_score");
    $pdo->exec("ALTER TABLE individual_scores ADD UNIQUE KEY unique_score (course_student_id, item_type, item_id, term)");
    
    echo "Unique key updated to include term column.<br>";
    
    echo "<strong>Term column added and populated successfully!</strong>";
} catch(PDOException $e) {
    echo "Error updating table: " . $e->getMessage();
}
?>