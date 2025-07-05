<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';

// Get course_student_id from course_id and student_number
function getCourseStudentId($pdo, $courseId, $studentNumber) {
    $stmt = $pdo->prepare("
        SELECT cs.id 
        FROM course_students cs 
        JOIN students s ON cs.student_id = s.id 
        WHERE cs.course_id = ? AND s.student_number = ?
    ");
    $stmt->execute([$courseId, $studentNumber]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['id'] : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $courseId = $_GET['course_id'] ?? null;
        $studentNumber = $_GET['student_number'] ?? null;
        
        if (!$courseId || !$studentNumber) {
            echo json_encode(['success' => false, 'message' => 'Course ID and student number are required']);
            exit;
        }
        
        $courseStudentId = getCourseStudentId($pdo, $courseId, $studentNumber);
        if (!$courseStudentId) {
            echo json_encode(['success' => false, 'message' => 'Student not found in course']);
            exit;
        }
        
        // Get course_id for this course_student
        $stmt = $pdo->prepare("SELECT course_id FROM course_students WHERE id = ?");
        $stmt->execute([$courseStudentId]);
        $courseId = $stmt->fetchColumn();
        
        // Get all component types and their average scores
        $componentTypes = ['quiz', 'activity', 'assignment', 'recitation', 'exam'];
        $grades = [];
        
        foreach (['midterm', 'final'] as $term) {
            foreach ($componentTypes as $componentType) {
                // Get all items of this type for this course and term
                $stmt = $pdo->prepare("
                    SELECT i.id, i.name, i.max_score 
                    FROM {$componentType}_items i 
                    WHERE i.course_id = ? AND i.term = ?
                ");
                $stmt->execute([$courseId, $term]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($items) > 0) {
                    // Get scores for these items
                    $totalScore = 0;
                    $totalMaxScore = 0;
                    $scoreCount = 0;
                    
                    foreach ($items as $item) {
                        // Around line 60, update the query that gets scores:
                                        $stmt = $pdo->prepare("
                                            SELECT score 
                                            FROM individual_scores 
                                            WHERE course_student_id = ? AND item_type = ? AND item_id = ? AND term = ?
                                        ");
                                        $stmt->execute([$courseStudentId, $componentType, $item['id'], $term]);
                        $score = $stmt->fetchColumn();
                        
                        if ($score !== false) {
                            $totalScore += $score;
                            $totalMaxScore += $item['max_score'];
                            $scoreCount++;
                        }
                    }
                    
                    // Calculate average score (0-100 scale)
                    $averageScore = $scoreCount > 0 ? ($totalScore / $totalMaxScore * 100) : 0;
                    
                    // Add to grades array in the format expected by the frontend
                    $grades[] = [
                        'term' => $term,
                        'component' => $componentType,
                        'score' => $averageScore
                    ];
                } else {
                    // No items found, add a zero score
                    $grades[] = [
                        'term' => $term,
                        'component' => $componentType,
                        'score' => 0
                    ];
                }
            }
        }
        
        // Get attendance records
        $stmt = $pdo->prepare("
            SELECT meeting_number, meeting_date, status 
            FROM attendance_records 
            WHERE course_student_id = ? 
            ORDER BY meeting_number
        ");
        $stmt->execute([$courseStudentId]);
        $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'grades' => $grades,
            'attendance' => $attendance
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $courseId = $input['course_id'] ?? null;
        $studentNumber = $input['student_number'] ?? null;
        $grades = $input['grades'] ?? [];
        $attendance = $input['attendance'] ?? [];
        
        if (!$courseId || !$studentNumber) {
            echo json_encode(['success' => false, 'message' => 'Course ID and student number are required']);
            exit;
        }
        
        $courseStudentId = getCourseStudentId($pdo, $courseId, $studentNumber);
        if (!$courseStudentId) {
            echo json_encode(['success' => false, 'message' => 'Student not found in course']);
            exit;
        }
        
        // Get course_id for this course_student
        $stmt = $pdo->prepare("SELECT course_id FROM course_students WHERE id = ?");
        $stmt->execute([$courseStudentId]);
        $courseId = $stmt->fetchColumn();
        
        $pdo->beginTransaction();
        
        // Process grades from the frontend
        foreach ($grades as $grade) {
            $term = $grade['term'];
            $component = $grade['component'];
            $score = $grade['score'];
            
            // Skip components that aren't supported in individual_scores
            if (!in_array($component, ['quiz', 'activity', 'assignment', 'recitation', 'exam'])) {
                continue;
            }
            
            // Find or create an item for this component
            $itemName = ucfirst($term) . ' ' . ucfirst($component) . ' Average';
            
            $stmt = $pdo->prepare("SELECT id FROM {$component}_items 
                                   WHERE course_id = ? AND term = ? AND name = ?");
            $stmt->execute([$courseId, $term, $itemName]);
            $itemId = $stmt->fetchColumn();
            
            if (!$itemId) {
                // Create the item
                $stmt = $pdo->prepare("INSERT INTO {$component}_items 
                                     (course_id, term, name, max_score) VALUES (?, ?, ?, 100)");
                $stmt->execute([$courseId, $term, $itemName]);
                $itemId = $pdo->lastInsertId();
            }
            
            // Save the score
            $stmt = $pdo->prepare("
                INSERT INTO individual_scores (course_student_id, item_type, item_id, term, score) 
                VALUES (?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE score = VALUES(score), updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$courseStudentId, $component, $itemId, $term, $score]);
        }
        
        // Clear existing attendance records for this student first
        $stmt = $pdo->prepare("DELETE FROM attendance_records WHERE course_student_id = ?");
        $stmt->execute([$courseStudentId]);
        
        // Save attendance records (insert all fresh)
        foreach ($attendance as $record) {
            $stmt = $pdo->prepare("
                INSERT INTO attendance_records (course_student_id, meeting_number, meeting_date, status) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $courseStudentId,
                $record['meeting_number'],
                $record['meeting_date'],
                $record['status']
            ]);
        }
        
        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => 'Data saved successfully']);
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>