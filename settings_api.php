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

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get course settings
        $course_id = $_GET['course_id'] ?? null;
        
        if (!$course_id) {
            echo json_encode(['success' => false, 'message' => 'Course ID required']);
            exit;
        }
        
        $stmt = $pdo->prepare("SELECT passing_grade, grade_computation_method FROM courses WHERE id = ?");
        $stmt->execute([$course_id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$course) {
            echo json_encode(['success' => false, 'message' => 'Course not found']);
            exit;
        }
        
        // Default grade scale based on computation method
        $default_grade_scale = [];
        if ($course['grade_computation_method'] === 'base_0') {
            $default_grade_scale = [
                ['min' => 97, 'max' => 100, 'equivalent' => '1.00'],
                ['min' => 94, 'max' => 96, 'equivalent' => '1.25'],
                ['min' => 91, 'max' => 93, 'equivalent' => '1.50'],
                ['min' => 88, 'max' => 90, 'equivalent' => '1.75'],
                ['min' => 85, 'max' => 87, 'equivalent' => '2.00'],
                ['min' => 82, 'max' => 84, 'equivalent' => '2.25'],
                ['min' => 79, 'max' => 81, 'equivalent' => '2.50'],
                ['min' => 76, 'max' => 78, 'equivalent' => '2.75'],
                ['min' => 75, 'max' => 75, 'equivalent' => '3.00'],
                ['min' => 0, 'max' => 74, 'equivalent' => '5.00']
            ];
        } else { // base_50
            $default_grade_scale = [
                ['min' => 98, 'max' => 100, 'equivalent' => '1.00'],
                ['min' => 96, 'max' => 97, 'equivalent' => '1.25'],
                ['min' => 94, 'max' => 95, 'equivalent' => '1.50'],
                ['min' => 92, 'max' => 93, 'equivalent' => '1.75'],
                ['min' => 90, 'max' => 91, 'equivalent' => '2.00'],
                ['min' => 88, 'max' => 89, 'equivalent' => '2.25'],
                ['min' => 86, 'max' => 87, 'equivalent' => '2.50'],
                ['min' => 84, 'max' => 85, 'equivalent' => '2.75'],
                ['min' => 75, 'max' => 83, 'equivalent' => '3.00'],
                ['min' => 50, 'max' => 74, 'equivalent' => '5.00']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'settings' => [
                'passing_grade' => floatval($course['passing_grade']),
                'computation_base' => $course['grade_computation_method'],
                'grade_scale' => $default_grade_scale
            ]
        ]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Update course settings
        $input = json_decode(file_get_contents('php://input'), true);
        
        $course_id = $input['course_id'] ?? null;
        $passing_grade = $input['passing_grade'] ?? null;
        $computation_base = $input['computation_base'] ?? null;
        
        if (!$course_id || !$passing_grade || !$computation_base) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }
        
        // Validate computation base
        if (!in_array($computation_base, ['base_0', 'base_50'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid computation base']);
            exit;
        }
        
        // Validate passing grade
        if ($passing_grade < 0 || $passing_grade > 100) {
            echo json_encode(['success' => false, 'message' => 'Passing grade must be between 0 and 100']);
            exit;
        }
        
        $stmt = $pdo->prepare("UPDATE courses SET passing_grade = ?, grade_computation_method = ? WHERE id = ?");
        $result = $stmt->execute([$passing_grade, $computation_base, $course_id]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Course settings updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update course settings']);
        }
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
// Default grade scale based on computation method
$default_grade_scale = [];
if ($course['grade_computation_method'] === 'base_0') {
    $default_grade_scale = [
        ['min' => 97, 'max' => 100, 'equivalent' => '1.00'],
        ['min' => 94, 'max' => 96, 'equivalent' => '1.25'],
        ['min' => 91, 'max' => 93, 'equivalent' => '1.50'],
        ['min' => 88, 'max' => 90, 'equivalent' => '1.75'],
        ['min' => 85, 'max' => 87, 'equivalent' => '2.00'],
        ['min' => 82, 'max' => 84, 'equivalent' => '2.25'],
        ['min' => 79, 'max' => 81, 'equivalent' => '2.50'],
        ['min' => 76, 'max' => 78, 'equivalent' => '2.75'],
        ['min' => 75, 'max' => 75, 'equivalent' => '3.00'],
        ['min' => 0, 'max' => 74, 'equivalent' => '5.00']
    ];
} else { // base_50
    $default_grade_scale = [
        ['min' => 98, 'max' => 100, 'equivalent' => '1.00'],
        ['min' => 96, 'max' => 97, 'equivalent' => '1.25'],
        ['min' => 94, 'max' => 95, 'equivalent' => '1.50'],
        ['min' => 92, 'max' => 93, 'equivalent' => '1.75'],
        ['min' => 90, 'max' => 91, 'equivalent' => '2.00'],
        ['min' => 88, 'max' => 89, 'equivalent' => '2.25'],
        ['min' => 86, 'max' => 87, 'equivalent' => '2.50'],
        ['min' => 84, 'max' => 85, 'equivalent' => '2.75'],
        ['min' => 75, 'max' => 83, 'equivalent' => '3.00'],
        ['min' => 50, 'max' => 74, 'equivalent' => '5.00']
    ];
}
        echo json_encode([
            'success' => true,
            'settings' => [
                'passing_grade' => floatval($course['passing_grade']),
                'computation_base' => $course['grade_computation_method'],
                'grade_scale' => $default_grade_scale
            ]
        ]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Update course settings
        $input = json_decode(file_get_contents('php://input'), true);
        
        $course_id = $input['course_id'] ?? null;
        $passing_grade = $input['passing_grade'] ?? null;
        $computation_base = $input['computation_base'] ?? null;
        
        if (!$course_id || !$passing_grade || !$computation_base) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }
        
        // Validate computation base
        if (!in_array($computation_base, ['base_0', 'base_50'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid computation base']);
            exit;
        }
        
        // Validate passing grade
        if ($passing_grade < 0 || $passing_grade > 100) {
            echo json_encode(['success' => false, 'message' => 'Passing grade must be between 0 and 100']);
            exit;
        }
        
        $stmt = $pdo->prepare("UPDATE courses SET passing_grade = ?, grade_computation_method = ? WHERE id = ?");
        $result = $stmt->execute([$passing_grade, $computation_base, $course_id]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Course settings updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update course settings']);
        }
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>