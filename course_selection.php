<?php
session_start();

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grading Sheet - Course Selection</title>
    <link rel="stylesheet" href="css/course_selection_style.css">
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="logo-space">
                <img src="img/PUPLogo.png" alt="PUP Logo">
            </div>
            <h1>Grading Sheet</h1>
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
        <h2>Select Course to Grade</h2>
        
        <div class="filters">
            <div class="filter-group">
                <label for="schoolYear">School Year</label>
                <select id="schoolYear">
                    <option value="">All</option>
                    <option value="2023-2024" selected>2023-2024</option>
                    <option value="2022-2023">2022-2023</option>
                    <option value="2024-2025">2024-2025</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="semester">Semester</label>
                <select id="semester">
                    <option value="">All</option>
                    <option value="First">First</option>
                    <option value="Second" selected>Second</option>
                </select>
            </div>

            <div class="filter-group">
                <label></label>
                <div class="button-group">
                    <button class="search-btn" onclick="filterCourses()">Search</button>
                    <button class="add-btn" onclick="openAddCourseModal()">Add Course</button>
                    <div class="controls">
                        <button class="btn-secondary" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
                        <button class="btn-academic-reports" onclick="window.location.href='reports.php'">Academic Reports</button>
                        <!-- Removed the Add Student button as requested -->
                        <!-- Removed duplicate Add Course button -->
                    </div>
                </div>
            </div>
        </div>

        <table class="courses-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Subject Code</th>
                    <th>Course Description</th>
                    <th>Section Code</th>
                    <th>Schedule</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="coursesTableBody">
                <!-- Table content will be populated by JavaScript -->
            </tbody>
        </table>
    </div>

    <!-- Add Course Modal -->
    <div id="addCourseModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddCourseModal()">&times;</span>
            <h2>Add New Course</h2>
            <form id="addCourseForm">
                <div class="form-group">
                    <label for="courseCode">Subject Code</label>
                    <input type="text" id="courseCode" name="courseCode" required placeholder="e.g., CMPE 30250">
                </div>
                
                <div class="form-group">
                    <label for="courseSubject">Course Description</label>
                    <input type="text" id="courseSubject" name="courseSubject" required placeholder="e.g., Computer Engineering Practice and Design 1">
                </div>
                
                <div class="form-group">
                    <label for="courseSectionCode">Section Code</label>
                    <input type="text" id="courseSectionCode" name="courseSectionCode" required placeholder="e.g., BSCOE-4A">
                </div>
                
                <div class="form-group">
                    <label for="courseSchedule">Schedule</label>
                    <input type="text" id="courseSchedule" name="courseSchedule" required placeholder="e.g., MWF 10:00-11:30 AM">
                </div>
                
                <div class="form-group">
                    <label for="courseSchoolYear">School Year</label>
                    <select id="courseSchoolYear" name="courseSchoolYear" required>
                        <option value="">Select School Year</option>
                        <option value="2023-2024">2023-2024</option>
                        <option value="2022-2023">2022-2023</option>
                        <option value="2024-2025">2024-2025</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="courseSemester">Semester</label>
                    <select id="courseSemester" name="courseSemester" required>
                        <option value="">Select Semester</option>
                        <option value="First">First</option>
                        <option value="Second">Second</option>
                    </select>
                </div>
                
                <div class="form-buttons">
                    <button type="button" class="btn-secondary" onclick="closeAddCourseModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Add Course</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Student Modal -->
    <!-- Add Student Modal -->
    <div id="addStudentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddStudentModal()">&times;</span>
            <h2>Add Student to Course</h2>
            <form id="addStudentForm">
                <input type="hidden" id="studentCourseId" name="courseId">
                <input type="hidden" id="studentCourseCode" name="courseCode">
                <input type="hidden" id="studentSectionCode" name="sectionCode">
                
                <div class="form-group">
                    <label for="studentNumber">Student Number</label>
                    <input type="text" id="studentNumber" name="studentNumber" required placeholder="e.g., 2020-00001">
                </div>
                
                <div class="form-group">
                    <label for="studentName">Full Name</label>
                    <input type="text" id="studentName" name="studentName" required placeholder="e.g., Juan Dela Cruz">
                </div>
                
                <div class="form-group course-info">
                    <label>Course:</label>
                    <span id="courseInfoDisplay"></span>
                </div>
                
                <div class="form-buttons">
                    <button type="button" class="btn-secondary" onclick="closeAddStudentModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Add Student</button>
                </div>
            </form>
        </div>
    </div>
</div>
    
    <script>
        function logout() {
            fetch('auth_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'logout'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'index.html';
                }
            })
            .catch(error => console.error('Error:', error));
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
    </script>
    <script src="javascript/course_selection_script.js"></script>
</body>
</html>