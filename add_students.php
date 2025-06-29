<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentNumber = trim($_POST['student_number'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $courseCode = trim($_POST['course_code'] ?? '');
    $sectionCode = trim($_POST['section_code'] ?? '');
    
    // Validation
    if (empty($studentNumber) || empty($fullName) || empty($courseCode) || empty($sectionCode)) {
        $error = "All fields are required";
    } else {
        try {
            // Check if student already exists
            $stmt = $pdo->prepare("SELECT id FROM students WHERE student_number = ?");
            $stmt->execute([$studentNumber]);
            $existingStudent = $stmt->fetch();
            
            if ($existingStudent) {
                // Update existing student
                $stmt = $pdo->prepare("UPDATE students SET full_name = ?, course_code = ?, section_code = ? WHERE student_number = ?");
                $stmt->execute([$fullName, $courseCode, $sectionCode, $studentNumber]);
                $success = "Student updated successfully";
            } else {
                // Insert new student
                $stmt = $pdo->prepare("INSERT INTO students (student_number, full_name, course_code, section_code) VALUES (?, ?, ?, ?)");
                $stmt->execute([$studentNumber, $fullName, $courseCode, $sectionCode]);
                $success = "Student added successfully";
            }
            
            // Check if there are any courses with this course code and section
            $stmt = $pdo->prepare("SELECT id FROM courses WHERE code = ? AND section_code = ?");
            $stmt->execute([$courseCode, $sectionCode]);
            $courses = $stmt->fetchAll();
            
            // Get student ID
            $stmt = $pdo->prepare("SELECT id FROM students WHERE student_number = ?");
            $stmt->execute([$studentNumber]);
            $student = $stmt->fetch();
            
            if ($student) {
                $studentId = $student['id'];
                
                // Enroll student in all matching courses
                foreach ($courses as $course) {
                    // Check if already enrolled
                    $stmt = $pdo->prepare("SELECT id FROM course_students WHERE course_id = ? AND student_id = ?");
                    $stmt->execute([$course['id'], $studentId]);
                    $enrollment = $stmt->fetch();
                    
                    if (!$enrollment) {
                        // Enroll student
                        $stmt = $pdo->prepare("INSERT INTO course_students (course_id, student_id) VALUES (?, ?)");
                        $stmt->execute([$course['id'], $studentId]);
                        
                        // Create empty grade record
                        $stmt = $pdo->prepare("INSERT INTO grades (course_student_id) 
                                             SELECT id FROM course_students WHERE course_id = ? AND student_id = ?");
                        $stmt->execute([$course['id'], $studentId]);
                    }
                }
            }
            
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Get all students
try {
    // Check if course_code column exists
    $checkColumnStmt = $pdo->prepare("SHOW COLUMNS FROM students LIKE 'course_code'");
    $checkColumnStmt->execute();
    $columnExists = $checkColumnStmt->rowCount() > 0;
    
    if ($columnExists) {
        $stmt = $pdo->query("SELECT * FROM students ORDER BY course_code, section_code, full_name");
    } else {
        // If course_code doesn't exist, order by other columns
        $stmt = $pdo->query("SELECT * FROM students ORDER BY full_name");
    }
    $students = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching students: " . $e->getMessage();
    $students = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link rel="stylesheet" href="css/course_selection_style.css">
    <style>
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .student-table {
            width: 100%;
            border-collapse: collapse;
        }
        .student-table th, .student-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .student-table th {
            background-color: #f2f2f2;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="logo-space">
                <img src="img/PUPLogo.png" alt="PUP Logo">
            </div>
            <h1>Manage Students</h1>
        </div>
        <div class="user-info">
            <div class="user-icon">👤</div>
            <span class="user-name" onclick="toggleUserDropdown()"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Guest'); ?></span>
            <button class="logout-btn" onclick="logout()">Logout</button>
            <div class="user-dropdown" id="userDropdown">
                <div class="dropdown-header">User Information</div>
                <div class="dropdown-item">
                    <div class="dropdown-label">Faculty Name</div>
                    <div class="dropdown-value"><?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?></div>
                </div>
                <div class="dropdown-item">
                    <div class="dropdown-label">Faculty ID</div>
                    <div class="dropdown-value"><?php echo htmlspecialchars($_SESSION['faculty_id'] ?? ''); ?></div>
                </div>
                <div class="dropdown-item">
                    <div class="dropdown-label">Email</div>
                    <div class="dropdown-value"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="nav-buttons">
            <button onclick="window.location.href='dashboard.php'">Dashboard</button>
            <button onclick="window.location.href='course_selection.php'">Course Selection</button>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <h2>Add/Edit Student</h2>
            <form method="post" action="">
                <div class="form-group">
                    <label for="student_number">Student Number</label>
                    <input type="text" id="student_number" name="student_number" required placeholder="e.g., 2020-00001">
                </div>
                
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required placeholder="e.g., Juan Dela Cruz">
                </div>
                
                <div class="form-group">
                    <label for="course_code">Course Code</label>
                    <input type="text" id="course_code" name="course_code" required placeholder="e.g., CMPE 30250">
                </div>
                
                <div class="form-group">
                    <label for="section_code">Section Code</label>
                    <input type="text" id="section_code" name="section_code" required placeholder="e.g., BSCOE-4A">
                </div>
                
                <div class="form-buttons">
                    <button type="submit" class="btn-primary">Save Student</button>
                </div>
            </form>
        </div>
        
        <h2>Student List</h2>
        <table class="student-table">
            <thead>
                <tr>
                    <th>Student Number</th>
                    <th>Name</th>
                    <th>Course Code</th>
                    <th>Section</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">No students found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['student_number']); ?></td>
                            <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['course_code'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($student['section_code'] ?? 'N/A'); ?></td>
                            <td>
                                <button class="edit-btn" onclick="editStudent('<?php echo htmlspecialchars($student['student_number']); ?>', '<?php echo htmlspecialchars($student['full_name']); ?>', '<?php echo htmlspecialchars($student['course_code'] ?? ''); ?>', '<?php echo htmlspecialchars($student['section_code'] ?? ''); ?>')">Edit</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        function logout() {
            window.location.href = 'auth_handler.php?action=logout';
        }

        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('active');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.user-info')) {
                document.getElementById('userDropdown').classList.remove('active');
            }
        });
        
        function editStudent(studentNumber, fullName, courseCode, sectionCode) {
            document.getElementById('student_number').value = studentNumber;
            document.getElementById('full_name').value = fullName;
            document.getElementById('course_code').value = courseCode;
            document.getElementById('section_code').value = sectionCode;
            
            // Scroll to the form
            document.querySelector('.form-container').scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>