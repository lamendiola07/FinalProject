<?php
session_start();

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}

// Debug session information
error_log("Session data in gradingsystem.php: " . json_encode($_SESSION));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grading Sheet</title>
    <link rel="stylesheet" href="css/grading_style.css">
    <script src="javascript/grading_script.js"></script>
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

    <div class="save-indicator" id="saveIndicator">
        Saving...
    </div>

    <div class="container">
        <div class="button-group">
            <button class="btn btn-primary" onclick="window.location.href='review_grading_sheet.php?id=' + getUrlParameter('id') + '&code=' + getUrlParameter('code') + '&title=' + getUrlParameter('title') + '&section=' + getUrlParameter('section') + '&schedule=' + getUrlParameter('schedule') + '&schoolYear=' + getUrlParameter('schoolYear') + '&semester=' + getUrlParameter('semester')">Review Grading Sheet</button>
            <button class="btn btn-primary" onclick="openAddStudentModal()">Add Student</button>
            <button class="btn btn-secondary" onclick="window.location.href='course_selection.php'">Back</button>
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
                <div class="status-item">
                    <div class="status-label">Passing Grade</div>
                    <div class="status-value" id="coursePassingGrade">75.00</div>
                </div>
                <div class="status-item">
                    <div class="status-label">Grade Computation</div>
                    <div class="status-value" id="courseGradeMethod">Base 50</div>
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
                    <!-- Students will be loaded dynamically from the database -->
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
            courseId = getUrlParameter('id'); // Make sure courseId is set globally
            const courseCode = getUrlParameter('code');
            const courseTitle = getUrlParameter('title');
            const sectionCode = getUrlParameter('section');
            const schedule = getUrlParameter('schedule');
            const schoolYear = getUrlParameter('schoolYear');
            const semester = getUrlParameter('semester');

            console.log('Course ID from URL:', courseId);

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
            
            // If we have a course ID, load students and grades
            if (courseId) {
                console.log('Loading students for course ID:', courseId);
                loadStudentsAndGrades();
            } else {
                console.error('No course ID found in URL parameters');
            }
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadCourseInfo();
        });
    </script>

<!-- Add these modal elements before the closing body tag -->
<div id="studentModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h2 id="studentModalTitle">Add Student</h2>
        <form id="studentForm" onsubmit="handleStudentSubmit(event)">
            <input type="hidden" id="editMode" value="add">
            <div class="form-group">
                <label for="studentNumber">Student Number:</label>
                <input type="text" id="studentNumber" required>
            </div>
            <div class="form-group">
                <label for="studentName">Full Name:</label>
                <input type="text" id="studentName" required>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-secondary" onclick="closeStudentModal()">Cancel</button>
        </form>
    </div>
</div>

<script>
    function openAddStudentModal() {
        document.getElementById('studentModal').style.display = 'block';
        document.getElementById('studentModalTitle').textContent = 'Add Student';
        document.getElementById('editMode').value = 'add';
        document.getElementById('studentForm').reset();
    }

    function openEditStudentModal(studentId) {
        const row = document.querySelector(`tr[data-student-id="${studentId}"]`);
        document.getElementById('studentModal').style.display = 'block';
        document.getElementById('studentModalTitle').textContent = 'Edit Student';
        document.getElementById('editMode').value = 'edit';
        document.getElementById('studentNumber').value = row.cells[0].textContent;
        document.getElementById('studentName').value = row.cells[1].textContent;
    }

    function closeStudentModal() {
        document.getElementById('studentModal').style.display = 'none';
    }

    function importCSV() {
        const fileInput = document.getElementById('csvFile');
        const file = fileInput.files[0];
        if (!file) {
            alert('Please select a CSV file first');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const text = e.target.result;
            const rows = text.split('\n');
            rows.forEach((row, index) => {
                if (index === 0) return; // Skip header row
                const columns = row.split(',');
                if (columns.length >= 4) {
                    const studentNumber = columns[0].trim();
                    const studentName = columns[1].trim();
                    const firstGrade = columns[2].trim();
                    const secondGrade = columns[3].trim();
                    updateStudentGrades(studentNumber, firstGrade, secondGrade);
                }
            });
            alert('Grades imported successfully!');
        };
        reader.readAsText(file);
    }

    function updateStudentGrades(studentNumber, firstGrade, secondGrade) {
        const firstGradeInput = document.querySelector(`input[data-student="${studentNumber}"][data-grade-type="first"]`);
        const secondGradeInput = document.querySelector(`input[data-student="${studentNumber}"][data-grade-type="second"]`);
        
        if (firstGradeInput && secondGradeInput) {
            firstGradeInput.value = firstGrade;
            secondGradeInput.value = secondGrade;
            calculateRatings(studentNumber);
        }
    }

    function handleStudentSubmit(event) {
        event.preventDefault();
        const studentNumber = document.getElementById('studentNumber').value;
        const studentName = document.getElementById('studentName').value;
        const editMode = document.getElementById('editMode').value;
        const courseId = getUrlParameter('id');
    
        // Debug information
        console.log('Submitting student with course ID:', courseId);
    
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
                student_number: studentNumber,
                full_name: studentName,
                first_grade: null,  // Add this to satisfy API requirements
                second_grade: null,  // Add this to satisfy API requirements
                course_code: getUrlParameter('code'),
                section_code: getUrlParameter('section')
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeStudentModal();
                hideSaveIndicator();
                
                // Reload students and grades to ensure data is displayed correctly
                loadStudentsAndGrades();
            } else {
                alert('Error saving student: ' + data.message);
                hideSaveIndicator();
            }
        })
        .catch(error => {
            alert('Error saving student: ' + error.message);
            hideSaveIndicator();
        });
    }

    function addStudentToTable(studentNumber, studentName) {
        const tbody = document.getElementById('studentsTableBody');
        const newRow = document.createElement('tr');
        newRow.className = 'student-row';
        newRow.dataset.student = studentNumber;
        newRow.dataset.studentId = studentNumber; // Add this for compatibility with edit/delete functions

        newRow.innerHTML = `
            <td>${studentNumber}</td>
            <td>${studentName}</td>
            <td>
                <input type="number" class="grade-input" placeholder="0.00"
                       min="1.00" max="5.00" step="0.01"
                       data-student="${studentNumber}" data-grade-type="first"
                       onchange="handleGradeChange(this)" oninput="handleGradeInput(this)">
            </td>
            <td>
                <input type="number" class="grade-input" placeholder="0.00"
                       min="1.00" max="5.00" step="0.01"
                       data-student="${studentNumber}" data-grade-type="second"
                       onchange="handleGradeChange(this)" oninput="handleGradeInput(this)">
            </td>
            <td class="computed-rating" data-student="${studentNumber}"></td>
            <td class="final-rating" data-student="${studentNumber}"></td>
            <td>
                <button onclick="openEditStudentModal('${studentNumber}')">Edit</button>
                <button onclick="deleteStudent('${studentNumber}')">Delete</button>
            </td>
        `;

        tbody.appendChild(newRow);
    }

    function updateStudentInTable(studentNumber, studentName) {
        const row = document.querySelector(`tr[data-student-id="${studentNumber}"]`);
        if (row) {
            row.cells[1].textContent = studentName;
        }
    }

    function deleteStudent(studentNumber) {
        if (confirm('Are you sure you want to delete this student?')) {
            const courseId = getUrlParameter('id');
            
            // Show saving indicator
            showSaveIndicator();
            
            // Delete from database
            fetch('grades_api.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    course_id: courseId,
                    student_number: studentNumber
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove row from table
                    const row = document.querySelector(`tr[data-student="${studentNumber}"]`);
                    if (row) {
                        row.remove();
                    }
                    hideSaveIndicator();
                } else {
                    alert('Error deleting student: ' + data.message);
                    hideSaveIndicator();
                }
            })
            .catch(error => {
                alert('Error deleting student: ' + error.message);
                hideSaveIndicator();
            });
        }
    }
</script>
</body>
</html>