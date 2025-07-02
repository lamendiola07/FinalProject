// Global variables
let courseId = null;
let studentData = [];
let debounceTimers = {};

// Add this function to handle the back button
function goBackToCourseSelection() {
    console.log('Back button clicked, navigating to course selection');
    window.location.href = 'course_selection.php';
}

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

// Handle input changes with debouncing to prevent premature saving
function handleGradeInput(input) {
    const studentId = input.dataset.student;
    const gradeType = input.dataset.gradeType;
    const key = `${studentId}-${gradeType}`;
    
    // Clear existing timer for this input
    if (debounceTimers[key]) {
        clearTimeout(debounceTimers[key]);
    }
    
    // Remove any existing state classes
    input.classList.remove('saving', 'saved', 'error');
    
    // Set a new timer to save after user stops typing (1.5 seconds)
    debounceTimers[key] = setTimeout(() => {
        saveGrade(input);
    }, 1500);
}

// Handle grade changes (when user leaves the input field)
function handleGradeChange(input) {
    const studentId = input.dataset.student;
    const gradeType = input.dataset.gradeType;
    const key = `${studentId}-${gradeType}`;
    
    // Clear any pending debounced save
    if (debounceTimers[key]) {
        clearTimeout(debounceTimers[key]);
        delete debounceTimers[key];
    }
    
    // Save the grade immediately
    saveGrade(input);
}

// Save grade to the server
function saveGrade(input) {
    const studentNumber = input.dataset.student;
    const gradeType = input.dataset.gradeType;
    const value = parseFloat(input.value);
    
    // Validate grade range
    if (isNaN(value) || value < 1.00 || value > 5.00) {
        input.classList.add('error');
        return;
    }
    
    // Show saving indicator
    showSaveIndicator();
    input.classList.add('saving');
    
    try {
        // Prepare data for API
        const data = {
            course_id: courseId,
            student_number: studentNumber,
            full_name: getStudentName(studentNumber)
        };
        
        // Add the appropriate grade field
        if (gradeType === 'first') {
            data.first_grade = value;
        } else if (gradeType === 'second') {
            data.second_grade = value;
        }
        
        // Send to server
        fetch('grades_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                input.classList.remove('saving', 'error');
                input.classList.add('saved');
                
                // Update computed grade if available
                if (result.computed_grade !== null) {
                    const computedRatingCell = document.querySelector(`.computed-rating[data-student="${studentNumber}"]`);
                    const finalRatingCell = document.querySelector(`.final-rating[data-student="${studentNumber}"]`);
                    
                    if (computedRatingCell) {
                        computedRatingCell.textContent = result.computed_grade.toFixed(2);
                    }
                    
                    if (finalRatingCell) {
                        finalRatingCell.textContent = result.computed_grade.toFixed(2);
                    }
                }
                
                // Hide saving indicator after a short delay
                setTimeout(hideSaveIndicator, 500);
            } else {
                input.classList.remove('saving', 'saved');
                input.classList.add('error');
                alert('Error saving grade: ' + result.message);
                hideSaveIndicator();
            }
        })
        .catch(error => {
            input.classList.remove('saving', 'saved');
            input.classList.add('error');
            alert('Error saving grade: ' + error.message);
            hideSaveIndicator();
        });
    } catch (error) {
        input.classList.remove('saving', 'saved');
        input.classList.add('error');
        alert('Error saving grade: ' + error.message);
        hideSaveIndicator();
    }
}

// Helper function to get student name from student number
function getStudentName(studentNumber) {
    const studentRow = document.querySelector(`tr[data-student="${studentNumber}"]`);
    if (studentRow) {
        const nameCell = studentRow.querySelector('td:nth-child(2)');
        return nameCell ? nameCell.textContent.trim() : '';
    }
    return '';
}

// Function to load students and grades for the current course
async function loadStudentsAndGrades() {
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
            console.log('Course data:', data.course);
            // Populate student table
            populateStudentTable(data.students);
        } else {
            console.error('Error loading students:', data.message);
            alert('Error loading students: ' + data.message);
        }
    } catch (error) {
        console.error('Error loading students:', error);
        alert('Error loading students: ' + error.message);
    }
}

// Function to populate student table
function populateStudentTable(students) {
    const tableBody = document.getElementById('studentsTableBody');
    tableBody.innerHTML = '';
    
    if (students.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="7" style="text-align: center; padding: 20px; color: #666;">No students enrolled in this course.</td>';
        tableBody.appendChild(row);
        return;
    }
    
    students.forEach(student => {
        const row = document.createElement('tr');
        row.className = 'student-row';
        row.dataset.student = student.student_number;
        
        row.innerHTML = `
            <td>${student.student_number}</td>
            <td>${student.full_name}</td>
            <td>
                <input type="number" 
                       class="grade-input" 
                       placeholder="0.00"
                       min="1.00" 
                       max="5.00" 
                       step="0.01"
                       value="${student.first_grade || ''}"
                       data-student="${student.student_number}"
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
                       value="${student.second_grade || ''}"
                       data-student="${student.student_number}"
                       data-grade-type="second"
                       onchange="handleGradeChange(this)"
                       oninput="handleGradeInput(this)">
            </td>
            <td class="computed-rating" data-student="${student.student_number}">${student.computed_grade || ''}</td>
            <td class="final-rating" data-student="${student.student_number}">${student.final_grade || ''}</td>
            <td></td>
        `;
        
        tableBody.appendChild(row);
    });
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


function logout() {
    window.location.href = 'auth_handler.php?action=logout';
}

// Add click event listener to logout buttons
document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.querySelector('.logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', logout);
    }
});