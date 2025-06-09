<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$faculty_id = $_SESSION['faculty_id'];

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle different request methods
switch ($method) {
    case 'GET':
        // Get students and grades for a specific course
        if (!isset($_GET['course_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Course ID is required']);
            exit;
        }
        
        $course_id = $_GET['course_id'];
        
        try {
            // First verify the faculty has access to this course
            $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND faculty_id = ?");
            $stmt->execute([$course_id, $faculty_id]);
            $course = $stmt->fetch();
            
            if (!$course) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Course not found or you do not have permission to access it']);
                exit;
            }
            
            // Get students and their grades for this course
            $stmt = $pdo->prepare("SELECT s.student_number, s.full_name, g.first_grade, g.second_grade, g.computed_grade, g.final_grade 
                                 FROM students s 
                                 JOIN course_students cs ON s.id = cs.student_id 
                                 LEFT JOIN grades g ON cs.id = g.course_student_id 
                                 WHERE cs.course_id = ? 
                                 ORDER BY s.full_name");
            $stmt->execute([$course_id]);
            $students = $stmt->fetchAll();
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'students' => $students, 'course' => $course]);
        } catch(PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error fetching students: ' . $e->getMessage()]);
        }
        break;
        
    case 'POST':
        // Add or update a grade
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['course_id']) || !isset($data['student_number']) || 
            (!isset($data['first_grade']) && !isset($data['second_grade']))) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }
        
        try {
            // First verify the faculty has access to this course
            $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND faculty_id = ?");
            $stmt->execute([$data['course_id'], $faculty_id]);
            $course = $stmt->fetch();
            
            if (!$course) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Course not found or you do not have permission to access it']);
                exit;
            }
            
            // Get or create student
            $stmt = $pdo->prepare("SELECT id FROM students WHERE student_number = ?");
            $stmt->execute([$data['student_number']]);
            $student = $stmt->fetch();
            
            if (!$student) {
                // Student doesn't exist, create them
                if (!isset($data['full_name'])) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Student name is required for new students']);
                    exit;
                }
                
                $stmt = $pdo->prepare("INSERT INTO students (student_number, full_name) VALUES (?, ?)");
                $stmt->execute([$data['student_number'], $data['full_name']]);
                $student_id = $pdo->lastInsertId();
            } else {
                $student_id = $student['id'];
            }
            
            // Get or create course_student relationship
            $stmt = $pdo->prepare("SELECT id FROM course_students WHERE course_id = ? AND student_id = ?");
            $stmt->execute([$data['course_id'], $student_id]);
            $course_student = $stmt->fetch();
            
            if (!$course_student) {
                // Enroll student in course
                $stmt = $pdo->prepare("INSERT INTO course_students (course_id, student_id) VALUES (?, ?)");
                $stmt->execute([$data['course_id'], $student_id]);
                $course_student_id = $pdo->lastInsertId();
            } else {
                $course_student_id = $course_student['id'];
            }
            
            // Get existing grade record if any
            $stmt = $pdo->prepare("SELECT * FROM grades WHERE course_student_id = ?");
            $stmt->execute([$course_student_id]);
            $grade = $stmt->fetch();
            
            // Calculate computed grade if both first and second grades are available
            $first_grade = isset($data['first_grade']) ? $data['first_grade'] : ($grade ? $grade['first_grade'] : null);
            $second_grade = isset($data['second_grade']) ? $data['second_grade'] : ($grade ? $grade['second_grade'] : null);
            $computed_grade = null;
            
            if ($first_grade !== null && $second_grade !== null) {
                // Simple average calculation - you can modify this formula as needed
                $computed_grade = ($first_grade + $second_grade) / 2;
                $computed_grade = round($computed_grade, 2);
            }
            
            if (!$grade) {
                // Insert new grade record
                $stmt = $pdo->prepare("INSERT INTO grades (course_student_id, first_grade, second_grade, computed_grade) 
                                     VALUES (?, ?, ?, ?)");
                $stmt->execute([$course_student_id, $first_grade, $second_grade, $computed_grade]);
            } else {
                // Update existing grade record
                $stmt = $pdo->prepare("UPDATE grades SET first_grade = ?, second_grade = ?, computed_grade = ? 
                                     WHERE course_student_id = ?");
                $stmt->execute([$first_grade, $second_grade, $computed_grade, $course_student_id]);
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => 'Grade updated successfully',
                'computed_grade' => $computed_grade
            ]);
        } catch(PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error updating grade: ' . $e->getMessage()]);
        }
        break;
        
    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>