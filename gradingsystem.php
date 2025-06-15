<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grading Sheet</title>
    <link rel="stylesheet" href="css/grading_style.css">
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
            <div class="user-icon">
                👤
            </div>
            <p>Welcome Back!<u><span class="user-name" onclick="toggleUserDropdown()"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span></u></p>
            <button class="logout-btn">Logout</button>
            <div class="user-dropdown" id="userDropdown">
                <div class="dropdown-header">User Information</div>
                <div class="dropdown-item">
                    <div class="dropdown-label">Faculty Name</div>
                    <div class="dropdown-value">Taylor Alison Swift</div>
                </div>
                <div class="dropdown-item">
                    <div class="dropdown-label">Faculty ID</div>
                    <div class="dropdown-value">EMP-2024-001</div>
                </div>
                <div class="dropdown-item">
                    <div class="dropdown-label">Email</div>
                    <div class="dropdown-value">john.doe@pup.edu.ph</div>
                </div>
            </div>
        </div>
    </div>

    <div class="save-indicator" id="saveIndicator">
        Saving...
    </div>

    <div class="container">
        <div class="button-group">
            <button class="btn btn-primary">Review Grading Sheet</button>
            <button class="btn btn-secondary" onclick="goBackToCourseSelection()">Back</button>
        </div>

        <div class="status-section">
            <div>
                <div class="status-item">
                    <div class="status-label">Encoding of Gradesheet Status:</div>
                    <div class="status-value status-closed" id="gradingStatus">CLOSED</div>
                </div>
                <div class="status-item">
                    <div class="status-label">Status</div>
                    <div class="status-value" id="statusMessage">Grade sheet is not yet finalized</div>
                </div>
                <div class="status-item">
                    <div class="status-label">Subject</div>
                    <div class="status-value" id="subjectInfo">CMPE 30250: Computer Engineering Practice and Design 1</div>
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

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Student Number</th>
                        <th>Name</th>
                        <th>First Grading</th>
                        <th>Second Grading</th>
                        <th>Computed Rating</th>
                        <th>Final Rating</th>
                        <th>Other</th>
                    </tr>
                </thead>
                <tbody id="studentsTableBody">
                    <tr class="student-row">
                        <td>2023-10177-MN-1</td>
                        <td>GALATICO, REI ANTHONY LONOY</td>
                        <td>
                            <input type="number" 
                                   class="grade-input" 
                                   placeholder="0.00"
                                   min="1.00" 
                                   max="5.00" 
                                   step="0.01"
                                   data-student="2023-10177-MN-1"
                                   data-grade-type="first"
                                   onchange="handleGradeChange(this)"
                                   oninput="handleGradeInput(this)">
                        </td>
                        <td>
                            <input type="number" 
                                   class="grade-input" 
                                   placeholder="0.00"
                                   min="1.00" 
                                   max="5.00" 
                                   step="0.01"
                                   data-student="2023-10177-MN-1"
                                   data-grade-type="second"
                                   onchange="handleGradeChange(this)"
                                   oninput="handleGradeInput(this)">
                        </td>
                        <td class="computed-rating" data-student="2023-10177-MN-1"></td>
                        <td class="final-rating" data-student="2023-10177-MN-1"></td>
                        <td></td>
                    </tr>
                    <tr class="student-row">
                        <td>2023-10178-MN-2</td>
                        <td>SAMPLE STUDENT NAME</td>
                        <td>
                            <input type="number" 
                                   class="grade-input" 
                                   placeholder="0.00"
                                   min="1.00" 
                                   max="5.00" 
                                   step="0.01"
                                   data-student="2023-10178-MN-2"
                                   data-grade-type="first"
                                   onchange="handleGradeChange(this)"
                                   oninput="handleGradeInput(this)">
                        </td>
                        <td>
                            <input type="number" 
                                   class="grade-input" 
                                   placeholder="0.00"
                                   min="1.00" 
                                   max="5.00" 
                                   step="0.01"
                                   data-student="2023-10178-MN-2"
                                   data-grade-type="second"
                                   onchange="handleGradeChange(this)"
                                   oninput="handleGradeInput(this)">
                        </td>
                        <td class="computed-rating" data-student="2023-10178-MN-2"></td>
                        <td class="final-rating" data-student="2023-10178-MN-2"></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Function to get URL parameters
        function getUrlParameter(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            var results = regex.exec(location.search);
            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
        }

        // Load course information from URL parameters
        function loadCourseInfo() {
            const courseCode = getUrlParameter('code');
            const courseTitle = getUrlParameter('title');
            const sectionCode = getUrlParameter('section');
            const schedule = getUrlParameter('schedule');
            const schoolYear = getUrlParameter('schoolYear');
            const semester = getUrlParameter('semester');

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
        }

        // Function to go back to course selection
        function goBackToCourseSelection() {
            window.location.href = 'course_selection.html';
        }

        // Function to toggle user dropdown
        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdown');
            const userName = document.querySelector('.user-name');
            
            if (!dropdown.contains(event.target) && !userName.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

        // Grade handling functions
        function handleGradeInput(input) {
            // Real-time validation
            const value = parseFloat(input.value);
            if (value < 1.00 || value > 5.00) {
                input.style.borderColor = '#dc3545';
            } else {
                input.style.borderColor = '#28a745';
            }
        }

        function handleGradeChange(input) {
            const studentId = input.dataset.student;
            const gradeType = input.dataset.gradeType;
            const value = parseFloat(input.value);

            // Validate grade range
            if (value < 1.00 || value > 5.00) {
                alert('Grade must be between 1.00 and 5.00');
                input.focus();
                return;
            }

            // Show saving indicator
            showSaveIndicator();

            // Calculate computed and final ratings
            calculateRatings(studentId);

            // Hide saving indicator after a short delay
            setTimeout(hideSaveIndicator, 1000);
        }

        function calculateRatings(studentId) {
            const firstGradeInput = document.querySelector(`input[data-student="${studentId}"][data-grade-type="first"]`);
            const secondGradeInput = document.querySelector(`input[data-student="${studentId}"][data-grade-type="second"]`);
            const computedRatingCell = document.querySelector(`.computed-rating[data-student="${studentId}"]`);
            const finalRatingCell = document.querySelector(`.final-rating[data-student="${studentId}"]`);

            const firstGrade = parseFloat(firstGradeInput.value) || 0;
            const secondGrade = parseFloat(secondGradeInput.value) || 0;

            if (firstGrade > 0 && secondGrade > 0) {
                // Simple average calculation (you can modify this formula as needed)
                const computedRating = ((firstGrade + secondGrade) / 2).toFixed(2);
                computedRatingCell.textContent = computedRating;
                finalRatingCell.textContent = computedRating;
            } else {
                computedRatingCell.textContent = '';
                finalRatingCell.textContent = '';
            }
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

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadCourseInfo();
        });
    </script>
</body>
</html>