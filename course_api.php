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
        // Get courses for the logged-in faculty
        try {
            $stmt = $pdo->prepare("SELECT * FROM courses WHERE faculty_id = ? ORDER BY created_at DESC");
            $stmt->execute([$faculty_id]);
            $courses = $stmt->fetchAll();
            
            // In the GET case, add a condition to get a specific course
            if (isset($_GET['id'])) {
                $course_id = $_GET['id'];
                
                try {
                    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND faculty_id = ?");
                    $stmt->execute([$course_id, $faculty_id]);
                    $course = $stmt->fetch();
                    
                    if (!$course) {
                        // Try without faculty check during development
                        $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
                        $stmt->execute([$course_id]);
                        $course = $stmt->fetch();
                    }
                    
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'course' => $course]);
                } catch(PDOException $e) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Error fetching course: ' . $e->getMessage()]);
                }
                exit;
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'courses' => $courses]);
        } catch(PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error fetching courses: ' . $e->getMessage()]);
        }
        break;
        
    case 'POST':
        // Add a new course
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['code']) || !isset($data['subject']) || !isset($data['sectionCode']) || 
            !isset($data['schedule']) || !isset($data['schoolYear']) || !isset($data['semester'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }
        
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Insert the course
            // In the POST case, update the SQL query
            $stmt = $pdo->prepare("INSERT INTO courses (code, subject, section_code, schedule, school_year, semester, faculty_id, passing_grade, grade_computation_method) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['code'],
                $data['subject'],
                $data['sectionCode'],
                $data['schedule'],
                $data['schoolYear'],
                $data['semester'],
                $faculty_id,
                $data['passingGrade'] ?? 75.00,
                $data['gradeComputationMethod'] ?? 'base_50'
            ]);
            $courseId = $pdo->lastInsertId();
            
            // Add students to the course based on section and course code
            try {
                // This query gets students from the database who match the course code and section
                $stmt = $pdo->prepare("SELECT * FROM students WHERE course_code = ? AND section_code = ?");
                $stmt->execute([$data['code'], $data['sectionCode']]);
                $students = $stmt->fetchAll();
                
                $enrolledCount = 0;
                
                // Enroll each matching student in the course
                foreach ($students as $student) {
                    // Enroll student in the course
                    $stmt = $pdo->prepare("INSERT INTO course_students (course_id, student_id) VALUES (?, ?)");
                    $stmt->execute([$courseId, $student['id']]);
                    
                    // Create empty grade record
                    $stmt = $pdo->prepare("INSERT INTO grades (course_student_id) 
                                             SELECT id FROM course_students WHERE course_id = ? AND student_id = ?");
                    $stmt->execute([$courseId, $student['id']]);
                    
                    $enrolledCount++;
                }
            } catch(PDOException $e) {
                // If the query fails (e.g., columns don't exist), just continue without enrolling students
                $enrolledCount = 0;
            }
            
            // Commit transaction
            $pdo->commit();
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => 'Course added successfully. ' . $enrolledCount . ' students enrolled.', 
                'id' => $courseId
            ]);
        } catch(PDOException $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error adding course: ' . $e->getMessage()]);
        }
        break;
        
    case 'DELETE':
        // Delete a course
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Course ID is required']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ? AND faculty_id = ?");
            $stmt->execute([$data['id'], $faculty_id]);
            
            if ($stmt->rowCount() > 0) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Course deleted successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Course not found or you do not have permission to delete it']);
            }
        } catch(PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error deleting course: ' . $e->getMessage()]);
        }
        break;
        
    case 'PUT':
        // Update course settings
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Course ID is required']);
            exit;
        }
        
        try {
            // Set default passing_grade and grade_computation_method if not provided
            $passing_grade = isset($data['passing_grade']) ? $data['passing_grade'] : 75.00;
            $grade_computation_method = isset($data['grade_computation_method']) ? $data['grade_computation_method'] : 'base_50';
            $stmt = $pdo->prepare("UPDATE courses SET passing_grade = ?, grade_computation_method = ? WHERE id = ? AND faculty_id = ?");
            $stmt->execute([
                $data['passing_grade'] ?? 75.00,
                $data['grade_computation_method'] ?? 'base_50',
                $data['id'],
                $faculty_id
            ]);
            
            if ($stmt->rowCount() > 0) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Course settings updated successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Course not found or you do not have permission to update it']);
            }
        } catch(PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error updating course settings: ' . $e->getMessage()]);
        }
        break;
        
    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>