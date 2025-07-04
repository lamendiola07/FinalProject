<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Reports - PUP Grading System</title>
    <link rel="stylesheet" href="css/reports_style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <img src="img/PUPLogo.png" alt="PUP Logo" class="pup-logo" onerror="this.style.display='none'">
                <h1>Academic Reports</h1>
            </div>
            <div class="user-info">
                <div class="user-dropdown">
                    <div class="dropdown-header" onclick="toggleUserDropdown()">
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <span class="dropdown-icon">▼</span>
                    </div>
                    <div class="dropdown-content" id="userDropdown">
                        <div class="dropdown-item">
                            <div class="dropdown-label">Faculty ID:</div>
                            <div class="dropdown-value"><?php echo htmlspecialchars($_SESSION['faculty_id']); ?></div>
                        </div>
                        <button class="logout-btn" onclick="logout()">Logout</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="navigation-buttons">
                <button class="btn btn-secondary" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
                <button class="btn btn-primary" onclick="window.location.href='course_selection.php'">Course Selection</button>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3 id="totalCourses">0</h3>
                    <p>Total Courses</p>
                </div>
                <div class="stat-card">
                    <h3 id="totalStudents">0</h3>
                    <p>Total Students</p>
                </div>
                <div class="stat-card">
                    <h3 id="averageGrade">0.00</h3>
                    <p>Average Grade</p>
                </div>
                <div class="stat-card">
                    <h3 id="passRate">0%</h3>
                    <p>Pass Rate</p>
                </div>
            </div>

            <div class="form-group">
                <label for="reportCourse">Course Report</label>
                <select id="reportCourse" class="form-control" onchange="generateCourseReport()">
                    <option value="">Select a course for detailed report</option>
                </select>
            </div>

            <div id="courseReportSection" style="display: none;">
                <h3>Course Performance Report</h3>
                <div class="table-container">
                    <table id="reportTable">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Student ID</th>
                                <th>First Grade</th>
                                <th>Second Grade</th>
                                <th>Computed Grade</th>
                                <th>Final Grade</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="reportTableBody">
                            <!-- Report data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="alertContainer"></div>
    </div>

    <script src="javascript/reports_script.js?v=<?php echo time(); ?>"></script>
</body>
</html>