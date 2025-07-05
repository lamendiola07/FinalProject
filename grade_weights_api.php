<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$faculty_id = $_SESSION['faculty_id'];

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle different request methods
switch ($method) {
    case 'GET':
        // Get grade weights for a course
        if (!isset($_GET['course_id'])) {
            echo json_encode(['success' => false, 'message' => 'Course ID required']);
            exit;
        }
        
        $course_id = $_GET['course_id'];
        
        try {
            $stmt = $pdo->prepare("SELECT grade_weights FROM courses WHERE id = ?");
            $stmt->execute([$course_id]);
            $course = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$course) {
                echo json_encode(['success' => false, 'message' => 'Course not found']);
                exit;
            }
            
            // Default weights if none are set
            $default_weights = json_encode([
                'attendance' => 0.10,
                'quiz' => 0.15,
                'activity' => 0.15,
                'assignment' => 0.10,
                'recitation' => 0.10,
                'exam' => 0.40
            ]);
            
            $weights = $course['grade_weights'] ?? $default_weights;
            
            echo json_encode([
                'success' => true, 
                'grade_weights' => json_decode($weights)
            ]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error fetching grade weights: ' . $e->getMessage()]);
        }
        break;
        
    case 'POST':
        // Update grade weights for a course
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['course_id']) || !isset($data['grade_weights'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }
        
        try {
            // First, update the grade weights
            $stmt = $pdo->prepare("UPDATE courses SET grade_weights = ? WHERE id = ?");
            $stmt->execute([json_encode($data['grade_weights']), $data['course_id']]);
            
            // Then, update passing_grade and grade_computation_method if provided
            if (isset($data['passing_grade']) && isset($data['grade_computation_method'])) {
                $stmt = $pdo->prepare("UPDATE courses SET passing_grade = ?, grade_computation_method = ? WHERE id = ?");
                $stmt->execute([
                    $data['passing_grade'],
                    $data['grade_computation_method'],
                    $data['course_id']
                ]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Grade weights updated successfully']);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error updating grade weights: ' . $e->getMessage()]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>