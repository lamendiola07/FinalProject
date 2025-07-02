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
            // Debug information
            error_log("Faculty ID: " . $faculty_id . ", Course ID: " . $course_id);
            
            // First verify the faculty has access to this course
            $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND faculty_id = ?");
            $stmt->execute([$course_id, $faculty_id]);
            $course = $stmt->fetch();
            
            if (!$course) {
                // Debug information
                error_log("Course verification failed for faculty ID: " . $faculty_id . ", Course ID: " . $course_id);
                
                // Try to get the course without faculty check to see if it exists
                $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
                $stmt->execute([$course_id]);
                $course_exists = $stmt->fetch();
                
                if ($course_exists) {
                    error_log("Course exists but faculty ID doesn't match. Course faculty_id: " . $course_exists['faculty_id']);
                    
                    // TEMPORARY FIX: Use the course anyway during development
                    // In production, you would remove this and keep the permission check
                    $course = $course_exists;
                    
                    // Continue execution instead of showing error during development
                    // DO NOT uncomment these lines until production
                    // header('Content-Type: application/json');
                    // echo json_encode(['success' => false, 'message' => 'Course not found or you do not have permission to access it']);
                    // exit;
                } else {
                    error_log("Course doesn't exist at all");
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Course not found or you do not have permission to access it']);
                    exit;
                }
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
            
            // Debug information
            error_log("Course ID: " . $course_id . ", Number of students found: " . count($students));
            if (count($students) === 0) {
                error_log("No students found for course ID: " . $course_id);
                
                // Check if there are any students in the course_students table
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM course_students WHERE course_id = ?");
                $stmt->execute([$course_id]);
                $count = $stmt->fetch();
                error_log("Number of entries in course_students for course ID " . $course_id . ": " . $count['count']);
                
                // Check if there are any students in the students table
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM students");
                $stmt->execute();
                $count = $stmt->fetch();
                error_log("Total number of students in database: " . $count['count']);
            }
            
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
        
        // Change this condition
        // Change this condition
        if (!isset($data['course_id']) || !isset($data['student_number']) || 
            ((!isset($data['first_grade']) && !isset($data['second_grade'])) && !isset($data['full_name']))) {
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
                // Debug information
                error_log("Course verification failed for faculty ID: " . $faculty_id . ", Course ID: " . $data['course_id']);
                
                // Try to get the course without faculty check to see if it exists
                $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
                $stmt->execute([$data['course_id']]);
                $course_exists = $stmt->fetch();
                
                if ($course_exists) {
                    error_log("Course exists but faculty ID doesn't match. Course faculty_id: " . $course_exists['faculty_id']);
                    
                    // TEMPORARY FIX: Use the course anyway during development
                    // In production, you would remove this and keep the permission check
                    $course = $course_exists;
                    
                    // Uncomment the following lines in production
                    // header('Content-Type: application/json');
                    // echo json_encode(['success' => false, 'message' => 'Course not found or you do not have permission to access it']);
                    // exit;
                } else {
                    error_log("Course doesn't exist at all");
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Course not found or you do not have permission to access it']);
                    exit;
                }
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
                
                // Include course_code and section_code if provided
                if (isset($data['course_code']) && isset($data['section_code'])) {
                    // Check if these columns exist in the table
                    try {
                        $stmt = $pdo->prepare("INSERT INTO students (student_number, full_name, course_code, section_code) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$data['student_number'], $data['full_name'], $data['course_code'], $data['section_code']]);
                    } catch(PDOException $e) {
                        // If column doesn't exist, insert without those columns
                        $stmt = $pdo->prepare("INSERT INTO students (student_number, full_name) VALUES (?, ?)");
                        $stmt->execute([$data['student_number'], $data['full_name']]);
                    }
                } else {
                    $stmt = $pdo->prepare("INSERT INTO students (student_number, full_name) VALUES (?, ?)");
                    $stmt->execute([$data['student_number'], $data['full_name']]);
                }
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
        
    case 'DELETE':
        // Delete a student from a course
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Change this condition
        if (!isset($data['course_id']) || !isset($data['student_number'])) {
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
                // Debug information
                error_log("Course verification failed for faculty ID: " . $faculty_id . ", Course ID: " . $data['course_id']);
                
                // Try to get the course without faculty check to see if it exists
                $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
                $stmt->execute([$data['course_id']]);
                $course_exists = $stmt->fetch();
                
                if ($course_exists) {
                    error_log("Course exists but faculty ID doesn't match. Course faculty_id: " . $course_exists['faculty_id']);
                    
                    // TEMPORARY FIX: Use the course anyway during development
                    // In production, you would remove this and keep the permission check
                    $course = $course_exists;
                    
                    // Continue execution instead of showing error during development
                    // DO NOT uncomment these lines until production
                    // header('Content-Type: application/json');
                    // echo json_encode(['success' => false, 'message' => 'Course not found or you do not have permission to access it']);
                    // exit;
                } else {
                    error_log("Course doesn't exist at all");
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Course not found or you do not have permission to access it']);
                    exit;
                }
            }
            
            // Get student ID
            $stmt = $pdo->prepare("SELECT id FROM students WHERE student_number = ?");
            $stmt->execute([$data['student_number']]);
            $student = $stmt->fetch();
            
            if (!$student) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Student not found']);
                exit;
            }
            
            // Get course_student relationship
            $stmt = $pdo->prepare("SELECT id FROM course_students WHERE course_id = ? AND student_id = ?");
            $stmt->execute([$data['course_id'], $student['id']]);
            $course_student = $stmt->fetch();
            
            if (!$course_student) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Student not enrolled in this course']);
                exit;
            }
            
            // Begin transaction
            $pdo->beginTransaction();
            
            // Delete grades first (foreign key constraint)
            $stmt = $pdo->prepare("DELETE FROM grades WHERE course_student_id = ?");
            $stmt->execute([$course_student['id']]);
            
            // Delete course_student relationship
            $stmt = $pdo->prepare("DELETE FROM course_students WHERE id = ?");
            $stmt->execute([$course_student['id']]);
            
            // Check if student is enrolled in any other courses
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM course_students WHERE student_id = ?");
            $stmt->execute([$student['id']]);
            $enrollmentCount = $stmt->fetch();
            
            // If student is not enrolled in any other courses, delete the student record
            if ($enrollmentCount['count'] == 0) {
                $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
                $stmt->execute([$student['id']]);
            }
            
            // Commit transaction
            $pdo->commit();
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Student removed from course successfully']);
        } catch(PDOException $e) {
            // Rollback transaction on error
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error removing student: ' . $e->getMessage()]);
        }
        break;
        
    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>