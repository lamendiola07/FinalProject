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
            $stmt = $pdo->prepare("INSERT INTO courses (code, subject, section_code, schedule, school_year, semester, faculty_id) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['code'],
                $data['subject'],
                $data['sectionCode'],
                $data['schedule'],
                $data['schoolYear'],
                $data['semester'],
                $faculty_id
            ]);
            
            $courseId = $pdo->lastInsertId();
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Course added successfully', 'id' => $courseId]);
        } catch(PDOException $e) {
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
        
    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>