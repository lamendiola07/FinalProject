// Global variable to store course data
let courseData = [];

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

// Function to populate the courses table
function populateCoursesTable(courses) {
    const tableBody = document.getElementById('coursesTableBody');
    tableBody.innerHTML = '';

    courses.forEach((course, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${index + 1}</td>
            <td><span class="course-code">${course.code}</span></td>
            <td>${course.subject}</td>
            <td><span class="section-code">${course.section_code}</span></td>
            <td><span class="schedule">${course.schedule}</span></td>
            <td>
                <div class="action-buttons">
                    <button class="view-btn" onclick="viewGradingSheet(${course.id})">View</button>
                    <button class="add-student-btn" onclick="openAddStudentModal(${course.id}, '${course.code}', '${course.section_code}')">Add Student</button>
                    <button class="btn-settings" onclick="openSettingsModal(${course.id}, '${course.code}', '${course.section_code}')">Settings</button>
                    <button class="delete-btn" onclick="deleteCourse(${course.id}, '${course.code}', '${course.section_code}')">Delete</button>
                </div>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Function to filter courses based on selected criteria
function filterCourses() {
    const schoolYear = document.getElementById('schoolYear').value;
    const semester = document.getElementById('semester').value;

    let filteredCourses = courseData;

    // Filter by school year
    if (schoolYear) {
        filteredCourses = filteredCourses.filter(course => course.school_year === schoolYear);
    }

    // Filter by semester
    if (semester) {
        filteredCourses = filteredCourses.filter(course => course.semester === semester);
    }

    populateCoursesTable(filteredCourses);
}

// Function to view grading sheet
function viewGradingSheet(courseId) {
    const course = courseData.find(c => c.id == courseId);
    
    if (course) {
        // Create URL with course parameters
        const params = new URLSearchParams({
            id: course.id,
            code: course.code,
            title: course.subject,
            section: course.section_code,
            schedule: course.schedule,
            schoolYear: course.school_year,
            semester: course.semester
        });
        
        // Navigate to grading system page with parameters
        window.location.href = `gradingsystem.php?${params.toString()}`;
    }
}

// Function to delete a course
async function deleteCourse(courseId, courseCode, sectionCode) {
    // Show confirmation dialog
    const confirmMessage = `Are you sure you want to delete the course:\n\n${courseCode} - ${sectionCode}\n\nThis action cannot be undone.`;
    
    if (confirm(confirmMessage)) {
        try {
            const response = await fetch('course_api.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: courseId })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Show success message
                showSuccessMessage(`Course "${courseCode} - ${sectionCode}" deleted successfully!`);
                
                // Reload courses
                loadCourses();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            alert('Error deleting course: ' + error.message);
        }
    }
}

// Function to open add course modal
function openAddCourseModal() {
    document.getElementById('addCourseModal').style.display = 'block';
}

// Function to close add course modal
function closeAddCourseModal() {
    document.getElementById('addCourseModal').style.display = 'none';
    document.getElementById('addCourseForm').reset();
}

// Function to show success message
function showSuccessMessage(message) {
    // Remove any existing success message
    const existingMessage = document.querySelector('.success-message');
    if (existingMessage) {
        existingMessage.remove();
    }

    // Create new success message
    const successDiv = document.createElement('div');
    successDiv.className = 'success-message';
    successDiv.textContent = message;
    
    // Add to body
    document.body.appendChild(successDiv);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        successDiv.classList.add('fade-out');
        setTimeout(() => {
            if (successDiv.parentNode) {
                successDiv.parentNode.removeChild(successDiv);
            }
        }, 300);
    }, 3000);
}

// Function to load courses from the server
async function loadCourses() {
    try {
        const response = await fetch('course_api.php');
        const data = await response.json();
        
        if (data.success) {
            courseData = data.courses;
            populateCoursesTable(courseData); // Changed this line to show all courses initially
            filterCourses(); // Then apply any filters
        } else {
            alert('Error loading courses: ' + data.message);
        }
    } catch (error) {
        alert('Error loading courses: ' + error.message);
    }
}

// Function to add a new course
async function addCourse(event) {
    event.preventDefault();
    
    const courseCode = document.getElementById('courseCode').value;
    const courseSubject = document.getElementById('courseSubject').value;
    const courseSectionCode = document.getElementById('courseSectionCode').value;
    const courseSchedule = document.getElementById('courseSchedule').value;
    const courseSchoolYear = document.getElementById('courseSchoolYear').value;
    const courseSemester = document.getElementById('courseSemester').value;
    
    try {
        const response = await fetch('course_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                code: courseCode,
                subject: courseSubject,
                sectionCode: courseSectionCode,
                schedule: courseSchedule,
                schoolYear: courseSchoolYear,
                semester: courseSemester
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeAddCourseModal();
            showSuccessMessage(data.message);
            loadCourses();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error adding course: ' + error.message);
    }
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Load courses from server
    loadCourses();
    
    // Set up form submission handler
    document.getElementById('addCourseForm').addEventListener('submit', addCourse);
});

// Close modal when clicking outside of it
window.addEventListener('click', function(event) {
    const modal = document.getElementById('addCourseModal');
    if (event.target === modal) {
        closeAddCourseModal();
    }
});

// Prevent form submission on Enter key in input fields (except textarea)
document.addEventListener('keydown', function(event) {
    if (event.key === 'Enter' && event.target.tagName !== 'TEXTAREA' && event.target.tagName !== 'BUTTON') {
        const form = event.target.closest('form');
        if (form && form.id === 'addCourseForm') {
            // Allow Enter to submit the form when focus is on form elements
            return;
        }
    }
});

// Export functions for global access
window.toggleUserDropdown = toggleUserDropdown;
window.filterCourses = filterCourses;
window.viewGradingSheet = viewGradingSheet;
window.deleteCourse = deleteCourse;
window.resetFilters = resetFilters;
window.openAddCourseModal = openAddCourseModal;
window.closeAddCourseModal = closeAddCourseModal;


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

// Function to open add student modal
function openAddStudentModal(courseId, courseCode, sectionCode) {
    document.getElementById('studentCourseId').value = courseId;
    document.getElementById('studentCourseCode').value = courseCode;
    document.getElementById('studentSectionCode').value = sectionCode;
    document.getElementById('courseInfoDisplay').textContent = `${courseCode} - ${sectionCode}`;
    document.getElementById('addStudentModal').style.display = 'block';
    document.getElementById('addStudentForm').reset();
}

// Function to close add student modal
function closeAddStudentModal() {
    document.getElementById('addStudentModal').style.display = 'none';
    document.getElementById('addStudentForm').reset();
}

// Function to add a student to a course
async function addStudent(event) {
    event.preventDefault();
    
    const courseId = document.getElementById('studentCourseId').value;
    const courseCode = document.getElementById('studentCourseCode').value;
    const sectionCode = document.getElementById('studentSectionCode').value;
    const studentNumber = document.getElementById('studentNumber').value;
    const studentName = document.getElementById('studentName').value;
    
    try {
        const response = await fetch('grades_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                course_id: courseId,
                student_number: studentNumber,
                full_name: studentName,
                course_code: courseCode,
                section_code: sectionCode
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show success message
            showSuccessMessage(`Student "${studentName}" added successfully to ${courseCode}!`);
            
            // Close modal
            closeAddStudentModal();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error adding student: ' + error.message);
    }
}

// Add event listener for the student form submission
document.addEventListener('DOMContentLoaded', function() {
    // Existing event listeners...
    
    // Add student form submission
    const addStudentForm = document.getElementById('addStudentForm');
    if (addStudentForm) {
        addStudentForm.addEventListener('submit', addStudent);
    }
    
    // Close add student modal when clicking outside
    window.addEventListener('click', function(event) {
        const addStudentModal = document.getElementById('addStudentModal');
        if (event.target === addStudentModal) {
            closeAddStudentModal();
        }
    });
});

// Export functions for global access
window.openAddStudentModal = openAddStudentModal;
window.closeAddStudentModal = closeAddStudentModal;
window.addStudent = addStudent;


// Function to reset filters
function resetFilters() {
    document.getElementById('schoolYear').value = '';
    document.getElementById('semester').value = '';
    filterCourses();
}

// Add this to the addCourse function where the form data is collected
function addCourse() {
    // Existing code...
    
    const passingGrade = document.getElementById('passingGrade').value || 75;
    const gradeComputationMethod = document.getElementById('gradeComputationMethod').value;
    
    // Add to the data object
    const data = {
        code: courseCode,
        subject: courseSubject,
        sectionCode: sectionCode,
        schedule: schedule,
        schoolYear: schoolYear,
        semester: semester,
        passingGrade: passingGrade,
        gradeComputationMethod: gradeComputationMethod
    };
    
    // Rest of the existing code...
}

// Settings Modal Functions
function openSettingsModal(courseId, courseCode, sectionCode) {
    document.getElementById('settingsCourseId').value = courseId;
    document.getElementById('settingsCourseCode').value = courseCode;
    document.getElementById('settingsSectionCode').value = sectionCode;
    document.getElementById('settingsCourseInfoDisplay').textContent = `${courseCode} - ${sectionCode}`;
    
    // Load current settings for this course
    loadCourseSettings(courseId);
    
    document.getElementById('settingsModal').style.display = 'block';
}

function closeSettingsModal() {
    document.getElementById('settingsModal').style.display = 'none';
    document.getElementById('settingsForm').reset();
}

// Load course-specific settings
async function loadCourseSettings(courseId) {
    try {
        const response = await fetch(`course_api.php?id=${courseId}`);
        const data = await response.json();
        
        if (data.success && data.course) {
            document.getElementById('settingsPassingGrade').value = data.course.passing_grade || 75;
            document.getElementById('settingsGradeComputationMethod').value = data.course.grade_computation_method || 'base_50';
        }
    } catch (error) {
        console.error('Error loading course settings:', error);
    }
}

// Save course settings
async function saveCourseSettings(event) {
    event.preventDefault();
    
    const courseId = document.getElementById('settingsCourseId').value;
    const passingGrade = document.getElementById('settingsPassingGrade').value;
    const gradeComputationMethod = document.getElementById('settingsGradeComputationMethod').value;
    
    try {
        const response = await fetch('course_api.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: courseId,
                passing_grade: passingGrade,
                grade_computation_method: gradeComputationMethod
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeSettingsModal();
            showSuccessMessage('Course settings updated successfully!');
            loadCourses(); // Refresh the course list
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error saving settings: ' + error.message);
    }
}

// Update the loadCourseGradingSettings function (around line 390)
async function loadCourseGradingSettings(courseId) {
    try {
        const response = await fetch(`settings_api.php?course_id=${courseId}`);
        const data = await response.json();
        
        if (data.success) {
            gradingSettings = data.settings;
            
            // Populate form with course settings
            document.getElementById('passingGrade').value = gradingSettings.passing_grade;
            document.getElementById('computationBase').value = gradingSettings.computation_base;
            populateGradeScale(gradingSettings.grade_scale);
        } else {
            console.error('Error loading course settings:', data.message);
            // Set default values
            document.getElementById('passingGrade').value = 75;
            document.getElementById('computationBase').value = 'base_50';
            populateGradeScale([]);
        }
    } catch (error) {
        console.error('Error loading course settings:', error);
        // Set default values
        document.getElementById('passingGrade').value = 75;
        document.getElementById('computationBase').value = 'base_50';
        populateGradeScale([]);
    }
}

// Update the settings form submission (around line 415)
document.addEventListener('DOMContentLoaded', function() {
    const settingsForm = document.getElementById('settingsForm');
    if (settingsForm) {
        settingsForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const courseId = document.getElementById('settingsCourseId').value;
            const passingGrade = parseFloat(document.getElementById('passingGrade').value);
            const computationBase = document.getElementById('computationBase').value;
            
            // Collect grade scale entries
            const gradeScaleEntries = [];
            const entries = document.querySelectorAll('.grade-scale-entry');
            
            entries.forEach(entry => {
                const min = parseFloat(entry.querySelector('.grade-min').value);
                const max = parseFloat(entry.querySelector('.grade-max').value);
                const equivalent = entry.querySelector('.grade-equivalent').value;
                
                if (!isNaN(min) && !isNaN(max) && equivalent) {
                    gradeScaleEntries.push({ min, max, equivalent });
                }
            });
            
            // Sort by min score descending
            gradeScaleEntries.sort((a, b) => b.min - a.min);
            
            try {
                const response = await fetch('settings_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        course_id: courseId,
                        passing_grade: passingGrade,
                        computation_base: computationBase,
                        grade_scale: gradeScaleEntries
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Course settings saved successfully!');
                    closeSettingsModal();
                    // Optionally reload courses to reflect changes
                    loadCourses();
                } else {
                    alert('Error saving settings: ' + data.message);
                }
            } catch (error) {
                alert('Error saving settings: ' + error.message);
            }
        });
    }
});