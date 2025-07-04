<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}

// Handle student deletion
if (isset($_POST['delete_student'])) {
    $studentNumber = trim($_POST['delete_student']);
    
    try {
        // Get student ID
        $stmt = $pdo->prepare("SELECT id FROM students WHERE student_number = ?");
        $stmt->execute([$studentNumber]);
        $student = $stmt->fetch();
        
        if ($student) {
            // Delete student (cascade will handle related records)
            $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
            $stmt->execute([$student['id']]);
            $success = "Student deleted successfully";
        } else {
            $error = "Student not found";
        }
    } catch (PDOException $e) {
        $error = "Error deleting student: " . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentNumber = trim($_POST['student_number'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $courseCode = trim($_POST['course_code'] ?? ''); // Changed from code to course_code
    $sectionCode = trim($_POST['section_code'] ?? '');
    
    // Validation
    if (empty($studentNumber) || empty($fullName)) {
        $error = "Student number and full name are required";
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
            
            // If course and section are provided, check for enrollment
            if (!empty($courseCode) && !empty($sectionCode)) {
                // Check if there are any courses with this course code and section
                $stmt = $pdo->prepare("SELECT id FROM courses WHERE code = ? AND section_code = ?");
                $stmt->execute([$courseCode, $sectionCode]);
                $courses = $stmt->fetchAll();
                
                if (empty($courses)) {
                    // No matching courses found
                    $error = "Warning: No course found with code '$courseCode' and section '$sectionCode'. Student information saved, but not enrolled in any course.";
                } else {
                    // Get student ID
                    $stmt = $pdo->prepare("SELECT id FROM students WHERE student_number = ?");
                    $stmt->execute([$studentNumber]);
                    $student = $stmt->fetch();
                    
                    if ($student) {
                        $studentId = $student['id'];
                        $enrolledInAnyCourse = false;
                        
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
                                $enrolledInAnyCourse = true;
                            } else {
                                $enrolledInAnyCourse = true;
                            }
                        }
                        
                        if (!$enrolledInAnyCourse) {
                            $error = "Error: Failed to enroll student in the course. Please try again.";
                        }
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
    
    // Get all available courses for dropdowns
    $coursesStmt = $pdo->query("SELECT DISTINCT code, section_code FROM courses ORDER BY code, section_code");
    $availableCourses = $coursesStmt->fetchAll();
    
    // Get all courses each student is enrolled in
    $studentEnrollments = [];
    foreach ($students as $student) {
        $enrollmentStmt = $pdo->prepare("SELECT c.id, c.code, c.section_code 
                                      FROM courses c 
                                      JOIN course_students cs ON c.id = cs.course_id 
                                      JOIN students s ON s.id = cs.student_id 
                                      WHERE s.student_number = ?");
        $enrollmentStmt->execute([$student['student_number']]);
        $enrollments = $enrollmentStmt->fetchAll();
        $studentEnrollments[$student['student_number']] = $enrollments;
    }
} catch (PDOException $e) {
    $error = "Error fetching students: " . $e->getMessage();
    $students = [];
    $availableCourses = [];
    $studentEnrollments = [];
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
        .enrollment-info {
            margin-top: 5px;
            font-size: 0.9em;
            color: #666;
        }
        .enrollment-badge {
            display: inline-block;
            background-color: #e9ecef;
            padding: 2px 8px;
            border-radius: 4px;
            margin-right: 5px;
            margin-bottom: 5px;
            font-size: 0.85em;
        }
        .edit-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }
        .delete-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .nav-buttons {
            margin-top: -10px;
            margin-bottom: 20px;
        }
        .dashboard-btn {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        .course-selection-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .course-dropdown {
            padding: 5px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
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
            <button class="dashboard-btn" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
            <button class="course-selection-btn" onclick="window.location.href='course_selection.php'">Course Selection</button>
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
                    <select id="course_code" name="course_code">
                        <option value="">Select Course Code</option>
                        <?php 
                        $uniqueCourses = [];
                        foreach ($availableCourses as $course) {
                            if (!in_array($course['code'], $uniqueCourses)) {
                                $uniqueCourses[] = $course['code'];
                                echo '<option value="' . htmlspecialchars($course['code']) . '">' . htmlspecialchars($course['code']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="section_code">Section Code</label>
                    <select id="section_code" name="section_code">
                        <option value="">Select Section Code</option>
                        <?php 
                        $uniqueSections = [];
                        foreach ($availableCourses as $course) {
                            if (!in_array($course['section_code'], $uniqueSections)) {
                                $uniqueSections[] = $course['section_code'];
                                echo '<option value="' . htmlspecialchars($course['section_code']) . '">' . htmlspecialchars($course['section_code']) . '</option>';
                            }
                        }
                        ?>
                    </select>
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
                    <th>Course, Year and Section</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No students found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['student_number']); ?></td>
                            <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                            <td>
                                <?php if (isset($studentEnrollments[$student['student_number']]) && !empty($studentEnrollments[$student['student_number']])): ?>
                                    <select class="course-dropdown" onchange="showSelectedCourse(this)">
                                        <?php foreach ($studentEnrollments[$student['student_number']] as $enrollment): ?>
                                            <option value="<?php echo htmlspecialchars($enrollment['code'] . '|' . $enrollment['section_code']); ?>">
                                                <?php echo htmlspecialchars($enrollment['code'] . ' | ' . $enrollment['section_code']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <select class="course-dropdown" disabled>
                                        <option>
                                            <?php 
                                                if (!empty($student['course_code']) && !empty($student['section_code'])) {
                                                    echo htmlspecialchars($student['course_code'] . ' | ' . $student['section_code']);
                                                } else {
                                                    echo 'N/A';
                                                }
                                            ?>
                                        </option>
                                    </select>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="edit-btn" onclick="editStudent('<?php echo htmlspecialchars($student['student_number']); ?>', '<?php echo htmlspecialchars($student['full_name']); ?>', '<?php echo htmlspecialchars($student['course_code'] ?? ''); ?>', '<?php echo htmlspecialchars($student['section_code'] ?? ''); ?>')">Edit</button>
                                <button class="delete-btn" onclick="deleteStudent('<?php echo htmlspecialchars($student['student_number']); ?>')">Delete</button>
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
            
            // Set the course code dropdown
            const courseSelect = document.getElementById('course_code');
            for (let i = 0; i < courseSelect.options.length; i++) {
                if (courseSelect.options[i].value === courseCode) {
                    courseSelect.selectedIndex = i;
                    break;
                }
            }
            
            // Set the section code dropdown
            const sectionSelect = document.getElementById('section_code');
            for (let i = 0; i < sectionSelect.options.length; i++) {
                if (sectionSelect.options[i].value === sectionCode) {
                    sectionSelect.selectedIndex = i;
                    break;
                }
            }
            
            // Scroll to the form
            document.querySelector('.form-container').scrollIntoView({ behavior: 'smooth' });
        }
        
        function deleteStudent(studentNumber) {
            if (confirm('Are you sure you want to delete this student?')) {
                // Create a form to submit the delete request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                
                // Create hidden input for student number
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_student';
                input.value = studentNumber;
                
                // Append input to form and form to document, then submit
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function showSelectedCourse(selectElement) {
            // This function can be used to perform actions when a course is selected from the dropdown
            console.log('Selected course: ' + selectElement.value);
        }
    </script>
</body>
</html>