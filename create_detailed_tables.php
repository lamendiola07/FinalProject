<?php
require_once 'config.php';

try {
    // First, check if detailed_grades table exists and has data that needs to be migrated
    $tableExists = $pdo->query("SHOW TABLES LIKE 'detailed_grades'")->rowCount() > 0;
    
    if ($tableExists) {
        // Check if there's data to migrate
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM detailed_grades");
        $count = $stmt->fetch();
        
        if ($count['count'] > 0) {
            // Migrate data from detailed_grades to individual_scores
            // For each component type in detailed_grades, we need to create corresponding items
            // and then create scores for those items
            
            $pdo->beginTransaction();
            
            // Get all unique course_student_id, term, component combinations
            $stmt = $pdo->query("SELECT DISTINCT course_student_id, term, component FROM detailed_grades");
            $components = $stmt->fetchAll();
            
            foreach ($components as $component) {
                $courseStudentId = $component['course_student_id'];
                $term = $component['term'];
                $componentType = $component['component'];
                
                // Skip components that aren't supported in individual_scores
                if (!in_array($componentType, ['quiz', 'activity', 'assignment'])) {
                    continue;
                }
                
                // Get course_id for this course_student
                $stmt = $pdo->prepare("SELECT course_id FROM course_students WHERE id = ?");
                $stmt->execute([$courseStudentId]);
                $courseId = $stmt->fetchColumn();
                
                if (!$courseId) continue;
                
                // Create a generic item for this component if it doesn't exist
                $itemName = ucfirst($term) . ' ' . ucfirst($componentType) . ' Average';
                
                $stmt = $pdo->prepare("SELECT id FROM {$componentType}_items 
                                       WHERE course_id = ? AND term = ? AND name = ?");
                $stmt->execute([$courseId, $term, $itemName]);
                $itemId = $stmt->fetchColumn();
                
                if (!$itemId) {
                    // Create the item
                    $stmt = $pdo->prepare("INSERT INTO {$componentType}_items 
                                         (course_id, term, name, max_score) VALUES (?, ?, ?, 100)");
                    $stmt->execute([$courseId, $term, $itemName]);
                    $itemId = $pdo->lastInsertId();
                }
                
                // Get the score from detailed_grades
                $stmt = $pdo->prepare("SELECT score FROM detailed_grades 
                                     WHERE course_student_id = ? AND term = ? AND component = ?");
                $stmt->execute([$courseStudentId, $term, $componentType]);
                $score = $stmt->fetchColumn();
                
                // Insert or update the score in individual_scores
                $stmt = $pdo->prepare("INSERT INTO individual_scores 
                                     (course_student_id, item_type, item_id, score) 
                                     VALUES (?, ?, ?, ?) 
                                     ON DUPLICATE KEY UPDATE score = VALUES(score)");
                $stmt->execute([$courseStudentId, $componentType, $itemId, $score]);
            }
            
            $pdo->commit();
            echo "Data migrated from detailed_grades to individual_scores successfully!<br>";
        }
        
        // Drop the detailed_grades table
        $pdo->exec("DROP TABLE IF EXISTS detailed_grades");
        echo "detailed_grades table removed successfully!<br>";
    }
    
    // Create attendance_records table (keep this part)
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
    
    echo "Attendance records table created/verified successfully!";
} catch(PDOException $e) {
    die("Error updating tables: " . $e->getMessage());
}
?>