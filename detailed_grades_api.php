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
        
        // Get detailed grades
        $stmt = $pdo->prepare("
            SELECT term, component, score 
            FROM detailed_grades 
            WHERE course_student_id = ?
        ");
        $stmt->execute([$courseStudentId]);
        $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
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
        
        $pdo->beginTransaction();
        
        // Save detailed grades
        foreach ($grades as $grade) {
            $stmt = $pdo->prepare("
                INSERT INTO detailed_grades (course_student_id, term, component, score) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE score = VALUES(score), updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([
                $courseStudentId,
                $grade['term'],
                $grade['component'],
                $grade['score']
            ]);
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
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>