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

    if (courses.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="6" style="text-align: center; padding: 20px; color: #666;">No courses found matching the selected criteria.</td>';
        tableBody.appendChild(row);
        return;
    }

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
            // Close modal and reset form
            closeAddCourseModal();
            
            // Show success message
            showSuccessMessage(data.message);
            
            // Reload courses
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