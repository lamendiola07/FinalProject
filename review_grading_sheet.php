<?php
session_start();

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}

// Debug session information
error_log("Session data in review_grading_sheet.php: " . json_encode($_SESSION));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Grading Sheet</title>
    <link rel="stylesheet" href="css/grading_style.css">
    <style>
        /* Additional styles for the review grading sheet */
        .grade-category {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            background-color: #f9f9f9;
        }
        
        .category-header {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 16px;
            color: #700000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .category-percentage {
            font-size: 14px;
            color: #555;
        }
        
        .attendance-table, .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .attendance-table th, .attendance-table td,
        .grades-table th, .grades-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        
        .attendance-table th, .grades-table th {
            background-color: #f2f2f2;
        }
        
        .attendance-present {
            background-color: #d4edda;
        }
        
        .attendance-absent {
            background-color: #f8d7da;
        }
        
        .term-section {
            margin-top: 30px;
            border-top: 2px solid #700000;
            padding-top: 20px;
        }
        
        .term-header {
            font-size: 18px;
            font-weight: bold;
            color: #700000;
            margin-bottom: 15px;
        }
        
        .grade-input {
            width: 60px;
            text-align: center;
            padding: 5px;
        }
        
        .computed-total {
            font-weight: bold;
            background-color: #e9ecef;
        }
        
        .save-button {
            background-color: #700000;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 15px;
        }
        
        .save-button:hover {
            background-color: #8B0000;
        }
        
        .final-grade-section {
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        .final-grade-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .final-grade-table th, .final-grade-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        
        .final-grade-table th {
            background-color: #e9ecef;
        }
        
        .grade-scale-table {
            width: 300px;
            margin-top: 20px;
            border-collapse: collapse;
        }
        
        .grade-scale-table th, .grade-scale-table td {
            border: 1px solid #ddd;
            padding: 5px 10px;
            text-align: center;
        }
        
        .grade-scale-table th {
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="logo-space">
                <img src="img/PUPLogo.png" alt="PUP Logo">
            </div>
            <h1>Review Grading Sheet</h1>
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

    <div class="save-indicator" id="saveIndicator">
        Saving...
    </div>

    <div class="container">
        <div class="button-group">
            <button class="btn btn-primary" onclick="exportToCSV()">Export to CSV</button>
            <button class="btn btn-secondary" onclick="window.location.href='gradingsystem.php?id=' + getUrlParameter('id') + '&code=' + getUrlParameter('code') + '&title=' + getUrlParameter('title') + '&section=' + getUrlParameter('section') + '&schedule=' + getUrlParameter('schedule') + '&schoolYear=' + getUrlParameter('schoolYear') + '&semester=' + getUrlParameter('semester')">Back</button>
        </div>

        <div class="status-section">
            <div>
                <div class="status-item">
                    <div class="status-label">Subject</div>
                    <div class="status-value" id="subjectInfo">CMPE 30250: Computer Engineering Practice and Design 1</div>
                </div>
                <div class="status-item">
                    <div class="status-label">Faculty</div>
                    <div class="status-value" id="facultyInfo"><?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?></div>
                </div>
            </div>
            <div>
                <div class="status-item">
                    <div class="status-label">School Year</div>
                    <div class="status-value" id="schoolYearInfo">2023-2024</div>
                </div>
                <div class="status-item">
                    <div class="status-label">Semester</div>
                    <div class="status-value" id="semesterInfo">Second</div>
                </div>
                <div class="status-item">
                    <div class="status-label">Section</div>
                    <div class="status-value" id="sectionInfo">BSCOE-4A</div>
                </div>
                <div class="status-item">
                    <div class="status-label">Schedule</div>
                    <div class="status-value" id="scheduleInfo">TBA</div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="message">
            For best experience, please access grading sheet through PC or Laptop
        </div>

        <div id="studentGradingContainer">
            <!-- Student selection dropdown -->
            <div class="form-group">
                <label for="studentSelect">Select Student:</label>
                <select id="studentSelect" onchange="loadStudentGrades()">
                    <option value="">-- Select a student --</option>
                    <!-- Students will be loaded dynamically -->
                </select>
            </div>

            <!-- Detailed grading sheet will be loaded here -->
            <div id="gradingSheetContent" style="display: none;">
                <!-- Attendance Section -->
                <div class="grade-category">
                    <div class="category-header">
                        <span>Attendance</span>
                        <span class="category-percentage">10% of Class Participation (70%)</span>
                    </div>
                    <table class="attendance-table" id="attendanceTable">
                        <thead>
                            <tr>
                                <th>Meeting</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="attendanceTableBody">
                            <!-- Attendance records will be loaded dynamically -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">Total Attendance:</td>
                                <td id="totalAttendance">0/0</td>
                            </tr>
                            <tr>
                                <td colspan="2">Attendance Grade (10%):</td>
                                <td id="attendanceGrade">0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                    <button class="save-button" onclick="addAttendanceRecord()">Add Attendance Record</button>
                </div>

                <!-- Midterm Section -->
                <div class="term-section">
                    <div class="term-header">Midterm Grade</div>
                    
                    <!-- Class Participation (70%) -->
                    <div class="grade-category">
                        <div class="category-header">
                            <span>Class Participation</span>
                            <span class="category-percentage">70%</span>
                        </div>
                        <table class="grades-table">
                            <thead>
                                <tr>
                                    <th>Component</th>
                                    <th>Weight</th>
                                    <th>Score</th>
                                    <th>Weighted Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Attendance</td>
                                    <td>10%</td>
                                    <td id="midtermAttendanceScore">0.00</td>
                                    <td id="midtermAttendanceWeighted">0.00</td>
                                </tr>
                                <tr>
                                    <td>Quiz</td>
                                    <td>20%</td>
                                    <td>
                                        <input type="number" class="grade-input" id="midtermQuizScore" min="0" max="100" step="0.01" value="0" onchange="calculateMidtermGrade()">
                                    </td>
                                    <td id="midtermQuizWeighted">0.00</td>
                                </tr>
                                <tr>
                                    <td>Activity</td>
                                    <td>20%</td>
                                    <td>
                                        <input type="number" class="grade-input" id="midtermActivityScore" min="0" max="100" step="0.01" value="0" onchange="calculateMidtermGrade()">
                                    </td>
                                    <td id="midtermActivityWeighted">0.00</td>
                                </tr>
                                <tr>
                                    <td>Assignment</td>
                                    <td>10%</td>
                                    <td>
                                        <input type="number" class="grade-input" id="midtermAssignmentScore" min="0" max="100" step="0.01" value="0" onchange="calculateMidtermGrade()">
                                    </td>
                                    <td id="midtermAssignmentWeighted">0.00</td>
                                </tr>
                                <tr>
                                    <td>Recitation</td>
                                    <td>10%</td>
                                    <td>
                                        <input type="number" class="grade-input" id="midtermRecitationScore" min="0" max="100" step="0.01" value="0" onchange="calculateMidtermGrade()">
                                    </td>
                                    <td id="midtermRecitationWeighted">0.00</td>
                                </tr>
                                <tr class="computed-total">
                                    <td colspan="3">Class Participation Total (70%)</td>
                                    <td id="midtermClassParticipationTotal">0.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Major Examination (30%) -->
                    <div class="grade-category">
                        <div class="category-header">
                            <span>Major Examination</span>
                            <span class="category-percentage">30%</span>
                        </div>
                        <table class="grades-table">
                            <thead>
                                <tr>
                                    <th>Component</th>
                                    <th>Weight</th>
                                    <th>Score</th>
                                    <th>Weighted Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Midterm Exam</td>
                                    <td>15%</td>
                                    <td>
                                        <input type="number" class="grade-input" id="midtermExamScore" min="0" max="100" step="0.01" value="0" onchange="calculateMidtermGrade()">
                                    </td>
                                    <td id="midtermExamWeighted">0.00</td>
                                </tr>
                                <tr class="computed-total">
                                    <td colspan="3">Major Examination Total (15%)</td>
                                    <td id="midtermExamTotal">0.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Midterm Grade Summary -->
                    <div class="grade-category">
                        <div class="category-header">
                            <span>Midterm Grade Summary</span>
                        </div>
                        <table class="grades-table">
                            <thead>
                                <tr>
                                    <th>Component</th>
                                    <th>Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Class Participation (70%)</td>
                                    <td id="midtermClassParticipationSummary">0.00</td>
                                </tr>
                                <tr>
                                    <td>Major Examination (15%)</td>
                                    <td id="midtermExamSummary">0.00</td>
                                </tr>
                                <tr class="computed-total">
                                    <td>Total Midterm Grade</td>
                                    <td id="totalMidtermGrade">0.00</td>
                                </tr>
                                <tr>
                                    <td>Rounded Midterm Grade</td>
                                    <td id="roundedMidtermGrade">0.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Final Term Section -->
                <div class="term-section">
                    <div class="term-header">Final Term Grade</div>
                    
                    <!-- Class Participation (70%) -->
                    <div class="grade-category">
                        <div class="category-header">
                            <span>Class Participation</span>
                            <span class="category-percentage">70%</span>
                        </div>
                        <table class="grades-table">
                            <thead>
                                <tr>
                                    <th>Component</th>
                                    <th>Weight</th>
                                    <th>Score</th>
                                    <th>Weighted Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Attendance</td>
                                    <td>10%</td>
                                    <td id="finalAttendanceScore">0.00</td>
                                    <td id="finalAttendanceWeighted">0.00</td>
                                </tr>
                                <tr>
                                    <td>Quiz</td>
                                    <td>20%</td>
                                    <td>
                                        <input type="number" class="grade-input" id="finalQuizScore" min="0" max="100" step="0.01" value="0" onchange="calculateFinalGrade()">
                                    </td>
                                    <td id="finalQuizWeighted">0.00</td>
                                </tr>
                                <tr>
                                    <td>Activity</td>
                                    <td>20%</td>
                                    <td>
                                        <input type="number" class="grade-input" id="finalActivityScore" min="0" max="100" step="0.01" value="0" onchange="calculateFinalGrade()">
                                    </td>
                                    <td id="finalActivityWeighted">0.00</td>
                                </tr>
                                <tr>
                                    <td>Assignment</td>
                                    <td>10%</td>
                                    <td>
                                        <input type="number" class="grade-input" id="finalAssignmentScore" min="0" max="100" step="0.01" value="0" onchange="calculateFinalGrade()">
                                    </td>
                                    <td id="finalAssignmentWeighted">0.00</td>
                                </tr>
                                <tr>
                                    <td>Recitation</td>
                                    <td>10%</td>
                                    <td>
                                        <input type="number" class="grade-input" id="finalRecitationScore" min="0" max="100" step="0.01" value="0" onchange="calculateFinalGrade()">
                                    </td>
                                    <td id="finalRecitationWeighted">0.00</td>
                                </tr>
                                <tr class="computed-total">
                                    <td colspan="3">Class Participation Total (70%)</td>
                                    <td id="finalClassParticipationTotal">0.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Major Examination (30%) -->
                    <div class="grade-category">
                        <div class="category-header">
                            <span>Major Examination</span>
                            <span class="category-percentage">30%</span>
                        </div>
                        <table class="grades-table">
                            <thead>
                                <tr>
                                    <th>Component</th>
                                    <th>Weight</th>
                                    <th>Score</th>
                                    <th>Weighted Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Final Exam</td>
                                    <td>15%</td>
                                    <td>
                                        <input type="number" class="grade-input" id="finalExamScore" min="0" max="100" step="0.01" value="0" onchange="calculateFinalGrade()">
                                    </td>
                                    <td id="finalExamWeighted">0.00</td>
                                </tr>
                                <tr class="computed-total">
                                    <td colspan="3">Major Examination Total (15%)</td>
                                    <td id="finalExamTotal">0.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Final Grade Summary -->
                    <div class="grade-category">
                        <div class="category-header">
                            <span>Final Term Grade Summary</span>
                        </div>
                        <table class="grades-table">
                            <thead>
                                <tr>
                                    <th>Component</th>
                                    <th>Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Class Participation (70%)</td>
                                    <td id="finalClassParticipationSummary">0.00</td>
                                </tr>
                                <tr>
                                    <td>Major Examination (15%)</td>
                                    <td id="finalExamSummary">0.00</td>
                                </tr>
                                <tr class="computed-total">
                                    <td>Total Final Term Grade</td>
                                    <td id="totalFinalGrade">0.00</td>
                                </tr>
                                <tr>
                                    <td>Rounded Final Term Grade</td>
                                    <td id="roundedFinalGrade">0.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Semestral Grade Section -->
                <div class="final-grade-section">
                    <div class="term-header">Semestral Grade</div>
                    <table class="final-grade-table">
                        <thead>
                            <tr>
                                <th>Midterm Grade</th>
                                <th>Final Term Grade</th>
                                <th>Semestral Grade</th>
                                <th>Equivalent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td id="semestralMidtermGrade">0.00</td>
                                <td id="semestralFinalGrade">0.00</td>
                                <td id="semestralGrade">0.00</td>
                                <td id="gradeEquivalent">0.00</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                        <table class="grade-scale-table">
                            <thead>
                                <tr>
                                    <th colspan="2">Grade Scale</th>
                                </tr>
                                <tr>
                                    <th>Range</th>
                                    <th>Equivalent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>97-100</td><td>1.00</td></tr>
                                <tr><td>94-96</td><td>1.25</td></tr>
                                <tr><td>91-93</td><td>1.50</td></tr>
                                <tr><td>88-90</td><td>1.75</td></tr>
                                <tr><td>85-87</td><td>2.00</td></tr>
                                <tr><td>82-84</td><td>2.25</td></tr>
                                <tr><td>79-81</td><td>2.50</td></tr>
                                <tr><td>76-78</td><td>2.75</td></tr>
                                <tr><td>75</td><td>3.00</td></tr>
                                <tr><td>72-74</td><td>3.50</td></tr>
                                <tr><td>69-71</td><td>4.00</td></tr>
                                <tr><td>66-68</td><td>4.50</td></tr>
                                <tr><td>65 & below</td><td>5.00</td></tr>
                            </tbody>
                        </table>
                        
                        <div>
                            <button class="save-button" onclick="saveAllGrades()">Save All Grades</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let courseId = null;
        let studentData = [];
        let currentStudent = null;
        let attendanceRecords = [];
        let debounceTimers = {};

        // Function to toggle user dropdown
        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userName = document.querySelector('.user-name');
            const dropdown = document.getElementById('userDropdown');
            
            if (!userName.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

        // Function to get URL parameters
        function getUrlParameter(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            var results = regex.exec(location.search);
            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
        }

        // Load course information from URL parameters
        function loadCourseInfo() {
            courseId = getUrlParameter('id');
            const courseCode = getUrlParameter('code');
            const courseTitle = getUrlParameter('title');
            const sectionCode = getUrlParameter('section');
            const schedule = getUrlParameter('schedule');
            const schoolYear = getUrlParameter('schoolYear');
            const semester = getUrlParameter('semester');

            console.log('Course ID from URL:', courseId);

            // Update the page with course information if parameters exist
            if (courseCode && courseTitle) {
                document.getElementById('subjectInfo').textContent = `${courseCode}: ${courseTitle}`;
            }
            if (sectionCode) {
                document.getElementById('sectionInfo').textContent = sectionCode;
            }
            if (schedule) {
                document.getElementById('scheduleInfo').textContent = schedule;
            }
            if (schoolYear) {
                document.getElementById('schoolYearInfo').textContent = schoolYear;
            }
            if (semester) {
                document.getElementById('semesterInfo').textContent = semester;
            }
            
            // If we have a course ID, load students
            if (courseId) {
                console.log('Loading students for course ID:', courseId);
                loadStudents();
            } else {
                console.error('No course ID found in URL parameters');
            }
        }

        // Function to load students for the current course
        async function loadStudents() {
            try {
                console.log('Loading students with course ID:', courseId);
                const url = `grades_api.php?course_id=${courseId}`;
                console.log('API URL:', url);
                
                const response = await fetch(url);
                console.log('Response status:', response.status);
                
                const data = await response.json();
                console.log('API response:', data);
                
                if (data.success) {
                    console.log('Number of students returned:', data.students ? data.students.length : 0);
                    studentData = data.students;
                    populateStudentDropdown(data.students);
                } else {
                    console.error('Error loading students:', data.message);
                    alert('Error loading students: ' + data.message);
                }
            } catch (error) {
                console.error('Error loading students:', error);
                alert('Error loading students: ' + error.message);
            }
        }

        // Function to populate student dropdown
        function populateStudentDropdown(students) {
            const dropdown = document.getElementById('studentSelect');
            dropdown.innerHTML = '<option value="">-- Select a student --</option>';
            
            if (students.length === 0) {
                dropdown.innerHTML += '<option disabled>No students enrolled in this course</option>';
                return;
            }
            
            students.forEach(student => {
                const option = document.createElement('option');
                option.value = student.student_number;
                option.textContent = `${student.student_number} - ${student.full_name}`;
                dropdown.appendChild(option);
            });
        }

        // Function to load student grades when selected
        function loadStudentGrades() {
            const studentNumber = document.getElementById('studentSelect').value;
            if (!studentNumber) {
                document.getElementById('gradingSheetContent').style.display = 'none';
                return;
            }
            
            // Find the selected student in our data
            currentStudent = studentData.find(student => student.student_number === studentNumber);
            if (!currentStudent) {
                alert('Student data not found');
                return;
            }
            
            // Show the grading sheet content
            document.getElementById('gradingSheetContent').style.display = 'block';
            
            // Load attendance records (this would come from the database in a real implementation)
            // For now, we'll create some sample data
            loadAttendanceRecords();
            
            // Load grade data (this would come from the database in a real implementation)
            loadGradeData();
            
            // Calculate initial grades
            calculateMidtermGrade();
            calculateFinalGrade();
            calculateSemestralGrade();
        }

        // Function to load attendance records
        function loadAttendanceRecords() {
            // Initialize empty attendance records array
            attendanceRecords = [];
            
            // Update the attendance table
            updateAttendanceTable();
        }

        // Function to update the attendance table
        function updateAttendanceTable() {
            const tableBody = document.getElementById('attendanceTableBody');
            tableBody.innerHTML = '';
            
            attendanceRecords.forEach(record => {
                const row = document.createElement('tr');
                row.className = record.status === 'present' ? 'attendance-present' : 'attendance-absent';
                
                row.innerHTML = `
                    <td>${record.meeting}</td>
                    <td>${record.date}</td>
                    <td>${record.status.toUpperCase()}</td>
                `;
                
                tableBody.appendChild(row);
            });
            
            // Update attendance totals
            const totalMeetings = attendanceRecords.length;
            const presentCount = attendanceRecords.filter(record => record.status === 'present').length;
            document.getElementById('totalAttendance').textContent = `${presentCount}/${totalMeetings}`;
            
            // Calculate attendance grade (percentage of present days)
            const attendanceGrade = totalMeetings > 0 ? (presentCount / totalMeetings) * 100 : 0;
            document.getElementById('attendanceGrade').textContent = attendanceGrade.toFixed(2);
            
            // Update attendance scores in grade calculations
            document.getElementById('midtermAttendanceScore').textContent = attendanceGrade.toFixed(2);
            document.getElementById('finalAttendanceScore').textContent = attendanceGrade.toFixed(2);
            
            // Recalculate grades
            calculateMidtermGrade();
            calculateFinalGrade();
        }

        // Function to add a new attendance record
        function addAttendanceRecord() {
            // Prompt for meeting number
            const meetingNumber = prompt("Enter meeting number:", attendanceRecords.length + 1);
            if (meetingNumber === null) return; // User cancelled
            
            // Prompt for date
            const today = new Date().toISOString().split('T')[0];
            const date = prompt("Enter date (YYYY-MM-DD):", today);
            if (date === null) return; // User cancelled
            
            // Prompt for status
            const statusInput = prompt("Enter attendance status (present/absent):", "present").toLowerCase();
            if (statusInput === null) return; // User cancelled
            
            // Validate status input
            const status = (statusInput === "present" || statusInput === "absent") ? statusInput : "present";
            
            // Add the new record
            attendanceRecords.push({
                meeting: parseInt(meetingNumber),
                date: date,
                status: status
            });
            
            // Sort attendance records by meeting number
            attendanceRecords.sort((a, b) => a.meeting - b.meeting);
            
            // Update the table
            updateAttendanceTable();
        }

        // Function to load grade data
        function loadGradeData() {
            // Initialize all grade components to 0
            
            // Midterm components
            document.getElementById('midtermQuizScore').value = '0';
            document.getElementById('midtermActivityScore').value = '0';
            document.getElementById('midtermAssignmentScore').value = '0';
            document.getElementById('midtermRecitationScore').value = '0';
            document.getElementById('midtermExamScore').value = '0';
            
            // Final components
            document.getElementById('finalQuizScore').value = '0';
            document.getElementById('finalActivityScore').value = '0';
            document.getElementById('finalAssignmentScore').value = '0';
            document.getElementById('finalRecitationScore').value = '0';
            document.getElementById('finalExamScore').value = '0';
            
            // If we have actual grades from the database, use them
            if (currentStudent.grades && currentStudent.grades.length > 0) {
                // Loop through the grade components and set values
                currentStudent.grades.forEach(grade => {
                    if (grade.term === 'midterm') {
                        if (grade.component === 'quiz') {
                            document.getElementById('midtermQuizScore').value = grade.score;
                        } else if (grade.component === 'activity') {
                            document.getElementById('midtermActivityScore').value = grade.score;
                        } else if (grade.component === 'assignment') {
                            document.getElementById('midtermAssignmentScore').value = grade.score;
                        } else if (grade.component === 'recitation') {
                            document.getElementById('midtermRecitationScore').value = grade.score;
                        } else if (grade.component === 'exam') {
                            document.getElementById('midtermExamScore').value = grade.score;
                        }
                    } else if (grade.term === 'final') {
                        if (grade.component === 'quiz') {
                            document.getElementById('finalQuizScore').value = grade.score;
                        } else if (grade.component === 'activity') {
                            document.getElementById('finalActivityScore').value = grade.score;
                        } else if (grade.component === 'assignment') {
                            document.getElementById('finalAssignmentScore').value = grade.score;
                        } else if (grade.component === 'recitation') {
                            document.getElementById('finalRecitationScore').value = grade.score;
                        } else if (grade.component === 'exam') {
                            document.getElementById('finalExamScore').value = grade.score;
                        }
                    }
                });
                
                // Recalculate grades based on loaded values
                calculateMidtermGrade();
                calculateFinalGrade();
            }
            
            if (currentStudent.first_grade) {
                document.getElementById('roundedMidtermGrade').textContent = currentStudent.first_grade;
                document.getElementById('semestralMidtermGrade').textContent = currentStudent.first_grade;
            }
            
            if (currentStudent.second_grade) {
                document.getElementById('roundedFinalGrade').textContent = currentStudent.second_grade;
                document.getElementById('semestralFinalGrade').textContent = currentStudent.second_grade;
            }
            
            if (currentStudent.computed_grade) {
                document.getElementById('semestralGrade').textContent = currentStudent.computed_grade;
                document.getElementById('gradeEquivalent').textContent = getGradeEquivalent(currentStudent.computed_grade);
            }
        }

        // Function to calculate midterm grade
        function calculateMidtermGrade() {
            // Get attendance score
            const attendanceScore = parseFloat(document.getElementById('midtermAttendanceScore').textContent);
            
            // Get component scores
            const quizScore = parseFloat(document.getElementById('midtermQuizScore').value) || 0;
            const activityScore = parseFloat(document.getElementById('midtermActivityScore').value) || 0;
            const assignmentScore = parseFloat(document.getElementById('midtermAssignmentScore').value) || 0;
            const recitationScore = parseFloat(document.getElementById('midtermRecitationScore').value) || 0;
            const examScore = parseFloat(document.getElementById('midtermExamScore').value) || 0;
            
            // Calculate weighted scores for class participation (70%)
            const attendanceWeighted = (attendanceScore * 0.1);
            const quizWeighted = (quizScore * 0.2);
            const activityWeighted = (activityScore * 0.2);
            const assignmentWeighted = (assignmentScore * 0.1);
            const recitationWeighted = (recitationScore * 0.1);
            
            // Update weighted score displays
            document.getElementById('midtermAttendanceWeighted').textContent = attendanceWeighted.toFixed(2);
            document.getElementById('midtermQuizWeighted').textContent = quizWeighted.toFixed(2);
            document.getElementById('midtermActivityWeighted').textContent = activityWeighted.toFixed(2);
            document.getElementById('midtermAssignmentWeighted').textContent = assignmentWeighted.toFixed(2);
            document.getElementById('midtermRecitationWeighted').textContent = recitationWeighted.toFixed(2);
            
            // Calculate class participation total
            const classParticipationTotal = attendanceWeighted + quizWeighted + activityWeighted + assignmentWeighted + recitationWeighted;
            document.getElementById('midtermClassParticipationTotal').textContent = classParticipationTotal.toFixed(2);
            document.getElementById('midtermClassParticipationSummary').textContent = classParticipationTotal.toFixed(2);
            
            // Calculate weighted exam score (15% of 30%)
            const examWeighted = (examScore * 0.15);
            document.getElementById('midtermExamWeighted').textContent = examWeighted.toFixed(2);
            document.getElementById('midtermExamTotal').textContent = examWeighted.toFixed(2);
            document.getElementById('midtermExamSummary').textContent = examWeighted.toFixed(2);
            
            // Calculate total midterm grade
            const totalMidtermGrade = classParticipationTotal + examWeighted;
            document.getElementById('totalMidtermGrade').textContent = totalMidtermGrade.toFixed(2);
            
            // Round the grade to the nearest whole number
            const roundedMidtermGrade = Math.round(totalMidtermGrade);
            document.getElementById('roundedMidtermGrade').textContent = roundedMidtermGrade;
            document.getElementById('semestralMidtermGrade').textContent = roundedMidtermGrade;
            
            // Recalculate semestral grade
            calculateSemestralGrade();
        }

        // Function to calculate final grade
        function calculateFinalGrade() {
            // Get attendance score
            const attendanceScore = parseFloat(document.getElementById('finalAttendanceScore').textContent);
            
            // Get component scores
            const quizScore = parseFloat(document.getElementById('finalQuizScore').value) || 0;
            const activityScore = parseFloat(document.getElementById('finalActivityScore').value) || 0;
            const assignmentScore = parseFloat(document.getElementById('finalAssignmentScore').value) || 0;
            const recitationScore = parseFloat(document.getElementById('finalRecitationScore').value) || 0;
            const examScore = parseFloat(document.getElementById('finalExamScore').value) || 0;
            
            // Calculate weighted scores for class participation (70%)
            const attendanceWeighted = (attendanceScore * 0.1);
            const quizWeighted = (quizScore * 0.2);
            const activityWeighted = (activityScore * 0.2);
            const assignmentWeighted = (assignmentScore * 0.1);
            const recitationWeighted = (recitationScore * 0.1);
            
            // Update weighted score displays
            document.getElementById('finalAttendanceWeighted').textContent = attendanceWeighted.toFixed(2);
            document.getElementById('finalQuizWeighted').textContent = quizWeighted.toFixed(2);
            document.getElementById('finalActivityWeighted').textContent = activityWeighted.toFixed(2);
            document.getElementById('finalAssignmentWeighted').textContent = assignmentWeighted.toFixed(2);
            document.getElementById('finalRecitationWeighted').textContent = recitationWeighted.toFixed(2);
            
            // Calculate class participation total
            const classParticipationTotal = attendanceWeighted + quizWeighted + activityWeighted + assignmentWeighted + recitationWeighted;
            document.getElementById('finalClassParticipationTotal').textContent = classParticipationTotal.toFixed(2);
            document.getElementById('finalClassParticipationSummary').textContent = classParticipationTotal.toFixed(2);
            
            // Calculate weighted exam score (15% of 30%)
            const examWeighted = (examScore * 0.15);
            document.getElementById('finalExamWeighted').textContent = examWeighted.toFixed(2);
            document.getElementById('finalExamTotal').textContent = examWeighted.toFixed(2);
            document.getElementById('finalExamSummary').textContent = examWeighted.toFixed(2);
            
            // Calculate total final grade
            const totalFinalGrade = classParticipationTotal + examWeighted;
            document.getElementById('totalFinalGrade').textContent = totalFinalGrade.toFixed(2);
            
            // Round the grade to the nearest whole number
            const roundedFinalGrade = Math.round(totalFinalGrade);
            document.getElementById('roundedFinalGrade').textContent = roundedFinalGrade;
            document.getElementById('semestralFinalGrade').textContent = roundedFinalGrade;
            
            // Recalculate semestral grade
            calculateSemestralGrade();
        }

        // Function to calculate semestral grade
        function calculateSemestralGrade() {
            const midtermGrade = parseInt(document.getElementById('semestralMidtermGrade').textContent) || 0;
            const finalGrade = parseInt(document.getElementById('semestralFinalGrade').textContent) || 0;
            
            // Calculate semestral grade (average of midterm and final)
            const semestralGrade = (midtermGrade + finalGrade) / 2;
            document.getElementById('semestralGrade').textContent = semestralGrade.toFixed(2);
            
            // Get the equivalent grade based on the scale
            const equivalent = getGradeEquivalent(semestralGrade);
            document.getElementById('gradeEquivalent').textContent = equivalent;
        }

        // Function to get grade equivalent based on scale
        function getGradeEquivalent(score) {
            if (score >= 97) return '1.00';
            if (score >= 94) return '1.25';
            if (score >= 91) return '1.50';
            if (score >= 88) return '1.75';
            if (score >= 85) return '2.00';
            if (score >= 82) return '2.25';
            if (score >= 79) return '2.50';
            if (score >= 76) return '2.75';
            if (score >= 75) return '3.00';
            if (score >= 72) return '3.50';
            if (score >= 69) return '4.00';
            if (score >= 66) return '4.50';
            return '5.00';
        }

        // Function to save all grades
        function saveAllGrades() {
            if (!currentStudent) {
                alert('No student selected');
                return;
            }
            
            const midtermGrade = parseInt(document.getElementById('roundedMidtermGrade').textContent) || 0;
            const finalGrade = parseInt(document.getElementById('roundedFinalGrade').textContent) || 0;
            
            // Show saving indicator
            showSaveIndicator();
            
            // Save to database
            fetch('grades_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    course_id: courseId,
                    student_number: currentStudent.student_number,
                    full_name: currentStudent.full_name,
                    first_grade: midtermGrade,
                    second_grade: finalGrade
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the student data in our local array
                    const studentIndex = studentData.findIndex(s => s.student_number === currentStudent.student_number);
                    if (studentIndex !== -1) {
                        studentData[studentIndex].first_grade = midtermGrade;
                        studentData[studentIndex].second_grade = finalGrade;
                        studentData[studentIndex].computed_grade = data.computed_grade;
                    }
                    
                    // Update the current student object
                    currentStudent.first_grade = midtermGrade;
                    currentStudent.second_grade = finalGrade;
                    currentStudent.computed_grade = data.computed_grade;
                    
                    // Hide saving indicator
                    hideSaveIndicator();
                    alert('Grades saved successfully!');
                } else {
                    alert('Error saving grades: ' + data.message);
                    hideSaveIndicator();
                }
            })
            .catch(error => {
                alert('Error saving grades: ' + error.message);
                hideSaveIndicator();
            });
        }

        // Function to export to CSV
        function exportToCSV() {
            if (studentData.length === 0) {
                alert('No student data to export');
                return;
            }
            
            // Create CSV content
            let csvContent = 'Student Number,Full Name,First Grading,Second Grading,Computed Grade\n';
            
            studentData.forEach(student => {
                csvContent += `${student.student_number},"${student.full_name}",${student.first_grade || ''},${student.second_grade || ''},${student.computed_grade || ''}\n`;
            });
            
            // Create download link
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.setAttribute('href', url);
            link.setAttribute('download', 'grading_sheet.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function showSaveIndicator() {
            const indicator = document.getElementById('saveIndicator');
            indicator.style.display = 'block';
            indicator.style.opacity = '1';
        }

        function hideSaveIndicator() {
            const indicator = document.getElementById('saveIndicator');
            indicator.style.opacity = '0';
            setTimeout(() => {
                indicator.style.display = 'none';
            }, 300);
        }

        function logout() {
            window.location.href = 'auth_handler.php?action=logout';
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadCourseInfo();
        });
    </script>
</body>
</html>