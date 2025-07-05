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
        // If course_id is provided, get detailed report for that course
        if (isset($_GET['course_id'])) {
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
        } 
        // Otherwise, get summary statistics
        else {
            try {
                // Get total courses
                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM courses WHERE faculty_id = ?");
                $stmt->execute([$faculty_id]);
                $totalCourses = $stmt->fetch()['total'];
                
                // Get total students (unique students enrolled in faculty's courses)
                $stmt = $pdo->prepare("SELECT COUNT(DISTINCT cs.student_id) as total 
                                     FROM course_students cs 
                                     JOIN courses c ON cs.course_id = c.id 
                                     WHERE c.faculty_id = ?");
                $stmt->execute([$faculty_id]);
                $totalStudents = $stmt->fetch()['total'];
                
                // Get average grade
                $stmt = $pdo->prepare("SELECT AVG(g.computed_grade) as average 
                                     FROM grades g 
                                     JOIN course_students cs ON g.course_student_id = cs.id 
                                     JOIN courses c ON cs.course_id = c.id 
                                     WHERE c.faculty_id = ? AND g.computed_grade IS NOT NULL");
                $stmt->execute([$faculty_id]);
                $averageGrade = $stmt->fetch()['average'];
                
                // Get pass rate (using course-specific passing grade)
                $stmt = $pdo->prepare("SELECT 
                                     c.id, c.passing_grade,
                                     g.computed_grade,
                                     COUNT(g.id) as total_grades
                                     FROM grades g 
                                     JOIN course_students cs ON g.course_student_id = cs.id 
                                     JOIN courses c ON cs.course_id = c.id 
                                     WHERE c.faculty_id = ? AND g.computed_grade IS NOT NULL
                                     GROUP BY c.id, g.id");
                $stmt->execute([$faculty_id]);
                $gradeData = $stmt->fetchAll();
                
                $totalGrades = 0;
                $passedGrades = 0;
                
                foreach ($gradeData as $grade) {
                    $totalGrades++;
                    $passingGrade = $grade['passing_grade'] ?? 75;
                    
                    // Check if the grade is in PUP format (1.00-5.00) or numerical format (0-100)
                    if ($grade['computed_grade'] <= 5.00 && $grade['computed_grade'] >= 1.00) {
                        // It's in PUP format (lower is better)
                        $equivalentPassing = 3.00; // Default equivalent
                        if ($passingGrade >= 50 && $passingGrade <= 100) {
                            // Convert percentage passing grade to PUP scale
                            // This is an approximation - adjust the formula as needed
                            $equivalentPassing = 3.00; // Default for 75%
                            if ($passingGrade < 75) {
                                $equivalentPassing = 3.00 + ((75 - $passingGrade) / 25);
                            } else if ($passingGrade > 75) {
                                $equivalentPassing = 3.00 - (($passingGrade - 75) / 25);
                            }
                        }
                        
                        if ($grade['computed_grade'] <= $equivalentPassing) {
                            $passedGrades++;
                        }
                    } else {
                        // It's in numerical format (higher is better)
                        if ($grade['computed_grade'] >= $passingGrade) {
                            $passedGrades++;
                        }
                    }
                }
                
                $passRate = $totalGrades > 0 ? ($passedGrades / $totalGrades) * 100 : 0;
                
                // Get all courses for dropdown
                $stmt = $pdo->prepare("SELECT * FROM courses WHERE faculty_id = ? ORDER BY created_at DESC");
                $stmt->execute([$faculty_id]);
                $courses = $stmt->fetchAll();
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'totalCourses' => $totalCourses,
                    'totalStudents' => $totalStudents,
                    'averageGrade' => $averageGrade ? round($averageGrade, 2) : 0,
                    'passRate' => round($passRate, 1),
                    'courses' => $courses
                ]);
            } catch(PDOException $e) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error fetching statistics: ' . $e->getMessage()]);
            }
        }
        break;
        
    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>