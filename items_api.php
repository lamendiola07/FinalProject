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

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGetRequest();
        break;
    case 'POST':
        handlePostRequest();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

function handleGetRequest() {
    global $pdo;
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_items':
            getItems();
            break;
        case 'get_scores':
            getScores();
            break;
        case 'get_all_data':
            getAllData();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
}

function handlePostRequest() {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'add_item':
            addItem($input);
            break;
        case 'update_item':
            updateItem($input);
            break;
        case 'delete_item':
            deleteItem($input);
            break;
        case 'save_scores':
            saveScores($input);
            break;
        case 'update_score':
            updateScore($input);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
}

function getItems() {
    global $pdo;
    
    $courseId = $_GET['course_id'] ?? '';
    $term = $_GET['term'] ?? '';
    $type = $_GET['type'] ?? '';
    
    if (!$courseId || !$term || !$type) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        return;
    }
    
    try {
        $tableName = $type . '_items';
        $stmt = $pdo->prepare("SELECT * FROM {$tableName} WHERE course_id = ? AND term = ? ORDER BY date_given ASC, created_at ASC");
        $stmt->execute([$courseId, $term]);
        $items = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'items' => $items]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function getScores() {
    global $pdo;
    
    $courseId = $_GET['course_id'] ?? '';
    $term = $_GET['term'] ?? '';
    $type = $_GET['type'] ?? '';
    
    if (!$courseId || !$term || !$type) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        return;
    }
    
    try {
        // Get all students in the course
        $stmt = $pdo->prepare("
            SELECT cs.id as course_student_id, s.student_number, s.full_name
            FROM course_students cs
            JOIN students s ON cs.student_id = s.id
            WHERE cs.course_id = ?
            ORDER BY s.full_name
        ");
        $stmt->execute([$courseId]);
        $students = $stmt->fetchAll();
        
        // Get all items for this type and term
        $tableName = $type . '_items';
        $stmt = $pdo->prepare("SELECT id FROM {$tableName} WHERE course_id = ? AND term = ?");
        $stmt->execute([$courseId, $term]);
        $items = $stmt->fetchAll();
        
        // Get all scores
        $scores = [];
        foreach ($students as $student) {
            $scores[$student['student_number']] = [];
            
            foreach ($items as $item) {
                $stmt = $pdo->prepare("
                    SELECT score FROM individual_scores 
                    WHERE course_student_id = ? AND item_type = ? AND item_id = ?
                ");
                $stmt->execute([$student['course_student_id'], $type, $item['id']]);
                $score = $stmt->fetchColumn();
                
                $scores[$student['student_number']][$item['id']] = $score !== false ? floatval($score) : 0;
            }
        }
        
        echo json_encode(['success' => true, 'scores' => $scores, 'students' => $students]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function getAllData() {
    global $pdo;
    
    $courseId = $_GET['course_id'] ?? '';
    $term = $_GET['term'] ?? '';
    $type = $_GET['type'] ?? '';
    
    if (!$courseId || !$term || !$type) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        return;
    }
    
    try {
        // Get items
        $tableName = $type . '_items';
        $stmt = $pdo->prepare("SELECT * FROM {$tableName} WHERE course_id = ? AND term = ? ORDER BY date_given ASC, created_at ASC");
        $stmt->execute([$courseId, $term]);
        $items = $stmt->fetchAll();
        
        // Get students
        $stmt = $pdo->prepare("
            SELECT cs.id as course_student_id, s.student_number, s.full_name
            FROM course_students cs
            JOIN students s ON cs.student_id = s.id
            WHERE cs.course_id = ?
            ORDER BY s.full_name
        ");
        $stmt->execute([$courseId]);
        $students = $stmt->fetchAll();
        
        // Get scores
        $scores = [];
        foreach ($students as $student) {
            $scores[$student['student_number']] = [];
            
            foreach ($items as $item) {
                $stmt = $pdo->prepare("
                    SELECT score FROM individual_scores 
                    WHERE course_student_id = ? AND item_type = ? AND item_id = ?
                ");
                $stmt->execute([$student['course_student_id'], $type, $item['id']]);
                $score = $stmt->fetchColumn();
                
                $scores[$student['student_number']][$item['id']] = $score !== false ? floatval($score) : 0;
            }
        }
        
        echo json_encode([
            'success' => true, 
            'items' => $items, 
            'students' => $students, 
            'scores' => $scores
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function addItem($input) {
    global $pdo;
    
    $courseId = $input['course_id'] ?? '';
    $term = $input['term'] ?? '';
    $type = $input['type'] ?? '';
    $name = $input['name'] ?? '';
    $maxScore = $input['max_score'] ?? 100;
    $date = $input['date'] ?? null;
    
    if (!$courseId || !$term || !$type || !$name) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        return;
    }
    
    try {
        $tableName = $type . '_items';
        $stmt = $pdo->prepare("INSERT INTO {$tableName} (course_id, term, name, max_score, date_given) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$courseId, $term, $name, $maxScore, $date]);
        
        $itemId = $pdo->lastInsertId();
        
        echo json_encode(['success' => true, 'item_id' => $itemId, 'message' => 'Item added successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function updateItem($input) {
    global $pdo;
    
    $itemId = $input['item_id'] ?? '';
    $type = $input['type'] ?? '';
    $name = $input['name'] ?? '';
    $maxScore = $input['max_score'] ?? 100;
    $date = $input['date'] ?? null;
    
    if (!$itemId || !$type || !$name) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        return;
    }
    
    try {
        $tableName = $type . '_items';
        $stmt = $pdo->prepare("UPDATE {$tableName} SET name = ?, max_score = ?, date_given = ? WHERE id = ?");
        $stmt->execute([$name, $maxScore, $date, $itemId]);
        
        echo json_encode(['success' => true, 'message' => 'Item updated successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function deleteItem($input) {
    global $pdo;
    
    $itemId = $input['item_id'] ?? '';
    $type = $input['type'] ?? '';
    
    if (!$itemId || !$type) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Delete all scores for this item
        $stmt = $pdo->prepare("DELETE FROM individual_scores WHERE item_type = ? AND item_id = ?");
        $stmt->execute([$type, $itemId]);
        
        // Delete the item
        $tableName = $type . '_items';
        $stmt = $pdo->prepare("DELETE FROM {$tableName} WHERE id = ?");
        $stmt->execute([$itemId]);
        
        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => 'Item deleted successfully']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function saveScores($input) {
    global $pdo;
    
    $courseId = $input['course_id'] ?? '';
    $term = $input['term'] ?? '';
    $type = $input['type'] ?? '';
    $scores = $input['scores'] ?? [];
    
    if (!$courseId || !$term || !$type) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        foreach ($scores as $studentNumber => $studentScores) {
            // Get course_student_id
            $stmt = $pdo->prepare("
                SELECT cs.id 
                FROM course_students cs 
                JOIN students s ON cs.student_id = s.id 
                WHERE cs.course_id = ? AND s.student_number = ?
            ");
            $stmt->execute([$courseId, $studentNumber]);
            $courseStudentId = $stmt->fetchColumn();
            
            if (!$courseStudentId) continue;
            
            foreach ($studentScores as $itemId => $score) {
                $stmt = $pdo->prepare("
                    INSERT INTO individual_scores (course_student_id, item_type, item_id, score) 
                    VALUES (?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE score = VALUES(score), updated_at = CURRENT_TIMESTAMP
                ");
                $stmt->execute([$courseStudentId, $type, $itemId, $score]);
            }
        }
        
        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => 'Scores saved successfully']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function updateScore($input) {
    global $pdo;
    
    $courseId = $input['course_id'] ?? '';
    $studentNumber = $input['student_number'] ?? '';
    $itemId = $input['item_id'] ?? '';
    $type = $input['type'] ?? '';
    $score = $input['score'] ?? 0;
    
    if (!$courseId || !$studentNumber || !$itemId || !$type) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        return;
    }
    
    try {
        // Get course_student_id
        $stmt = $pdo->prepare("
            SELECT cs.id 
            FROM course_students cs 
            JOIN students s ON cs.student_id = s.id 
            WHERE cs.course_id = ? AND s.student_number = ?
        ");
        $stmt->execute([$courseId, $studentNumber]);
        $courseStudentId = $stmt->fetchColumn();
        
        if (!$courseStudentId) {
            echo json_encode(['success' => false, 'message' => 'Student not found in course']);
            return;
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO individual_scores (course_student_id, item_type, item_id, score) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE score = VALUES(score), updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$courseStudentId, $type, $itemId, $score]);
        
        echo json_encode(['success' => true, 'message' => 'Score updated successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>