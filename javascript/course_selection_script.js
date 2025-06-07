// Sample course data
let courseData = [
    {
        id: 1,
        code: "CMPE 30250",
        subject: "Computer Engineering Practice and Design 1",
        sectionCode: "BSCOE-4A",
        schedule: "MWF 10:00-11:30 AM",
        schoolYear: "2023-2024",
        semester: "Second"
    },
    {
        id: 2,
        code: "CMPE 30253",
        subject: "Microprocessor Systems",
        sectionCode: "BSCOE-4B",
        schedule: "TTH 1:00-2:30 PM",
        schoolYear: "2023-2024",
        semester: "Second"
    },
    {
        id: 3,
        code: "MATH 30143",
        subject: "Differential Equations",
        sectionCode: "BSMATH-3A",
        schedule: "MWF 8:00-9:30 AM",
        schoolYear: "2023-2024",
        semester: "First"
    },
    {
        id: 4,
        code: "PHYS 30123",
        subject: "Electronics",
        sectionCode: "BSPHYS-2A",
        schedule: "TTH 10:00-11:30 AM",
        schoolYear: "2022-2023",
        semester: "Second"
    },
    {
        id: 5,
        code: "CMPE 30251",
        subject: "Digital Signal Processing",
        sectionCode: "BSCOE-4C",
        schedule: "MWF 2:00-3:30 PM",
        schoolYear: "2023-2024",
        semester: "Second"
    }
];

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
            <td><span class="section-code">${course.sectionCode}</span></td>
            <td><span class="schedule">${course.schedule}</span></td>
            <td>
                <div class="action-buttons">
                    <button class="view-btn" onclick="viewGradingSheet(${course.id})">View</button>
                    <button class="delete-btn" onclick="deleteCourse(${course.id}, '${course.code}', '${course.sectionCode}')">Delete</button>
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
        filteredCourses = filteredCourses.filter(course => course.schoolYear === schoolYear);
    }

    // Filter by semester
    if (semester) {
        filteredCourses = filteredCourses.filter(course => course.semester === semester);
    }

    populateCoursesTable(filteredCourses);
}

// Function to view grading sheet
function viewGradingSheet(courseId) {
    const course = courseData.find(c => c.id === courseId);
    
    if (course) {
        // Create URL with course parameters
        const params = new URLSearchParams({
            code: course.code,
            title: course.subject,
            section: course.sectionCode,
            schedule: course.schedule,
            schoolYear: course.schoolYear,
            semester: course.semester
        });
        
        // Navigate to grading system page with parameters
        window.location.href = `gradingsystem.html?${params.toString()}`;
    }
}

// Function to delete a course
function deleteCourse(courseId, courseCode, sectionCode) {
    // Show confirmation dialog
    const confirmMessage = `Are you sure you want to delete the course:\n\n${courseCode} - ${sectionCode}\n\nThis action cannot be undone.`;
    
    if (confirm(confirmMessage)) {
        // Find the course index
        const courseIndex = courseData.findIndex(course => course.id === courseId);
        
        if (courseIndex !== -1) {
            // Remove the course from the array
            const deletedCourse = courseData.splice(courseIndex, 1)[0];
            
            console.log('Course deleted:', deletedCourse);
            console.log('Remaining courses:', courseData.length);
            
            // Show success message
            showSuccessMessage(`Course "${courseCode} - ${sectionCode}" deleted successfully!`);
            
            // Refresh the table with current filters
            setTimeout(() => {
                filterCourses();
            }, 100);
        } else {
            alert('Course not found. Unable to delete.');
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

// Function to add new course
function addNewCourse(event) {
    event.preventDefault();
    
    const form = document.getElementById('addCourseForm');
    const formData = new FormData(form);
    
    // Validate form data
    const courseCode = formData.get('courseCode').trim();
    const courseSubject = formData.get('courseSubject').trim();
    const courseSectionCode = formData.get('courseSectionCode').trim();
    const courseSchedule = formData.get('courseSchedule').trim();
    const courseSchoolYear = formData.get('courseSchoolYear');
    const courseSemester = formData.get('courseSemester');
    
    // Check if all fields are filled
    if (!courseCode || !courseSubject || !courseSectionCode || !courseSchedule || !courseSchoolYear || !courseSemester) {
        alert('Please fill in all required fields.');
        return;
    }
    
    // Check for duplicate course code and section
    const isDuplicate = courseData.some(course => 
        course.code.toLowerCase() === courseCode.toLowerCase() && 
        course.sectionCode.toLowerCase() === courseSectionCode.toLowerCase() &&
        course.schoolYear === courseSchoolYear &&
        course.semester === courseSemester
    );
    
    if (isDuplicate) {
        alert('A course with the same code and section already exists for this school year and semester.');
        return;
    }
    
    // Generate new ID
    const newId = courseData.length > 0 ? Math.max(...courseData.map(c => c.id)) + 1 : 1;
    
    // Create new course object
    const newCourse = {
        id: newId,
        code: courseCode,
        subject: courseSubject,
        sectionCode: courseSectionCode,
        schedule: courseSchedule,
        schoolYear: courseSchoolYear,
        semester: courseSemester
    };
    
    // Add to course data
    courseData.push(newCourse);
    
    console.log('Course added:', newCourse);
    console.log('Total courses:', courseData.length);
    
    // Close modal and reset form
    closeAddCourseModal();
    
    // Show success message
    showSuccessMessage('Course added successfully!');
    
    // Force refresh the table - apply the current filters again
    setTimeout(() => {
        const currentSchoolYear = document.getElementById('schoolYear').value;
        const currentSemester = document.getElementById('semester').value;
        
        console.log('Current filters - School Year:', currentSchoolYear, 'Semester:', currentSemester);
        
        let filteredCourses = courseData.slice(); // Create a copy of the array
        
        // Apply filters
        if (currentSchoolYear) {
            filteredCourses = filteredCourses.filter(course => course.schoolYear === currentSchoolYear);
        }
        
        if (currentSemester) {
            filteredCourses = filteredCourses.filter(course => course.semester === currentSemester);
        }
        
        console.log('Filtered courses:', filteredCourses);
        
        // Populate table with filtered results
        populateCoursesTable(filteredCourses);
        
        // Check if the new course should be visible with current filters
        const shouldBeVisible = (!currentSchoolYear || newCourse.schoolYear === currentSchoolYear) &&
                              (!currentSemester || newCourse.semester === currentSemester);
        
        if (shouldBeVisible) {
            // Highlight the new course row
            setTimeout(() => {
                const tableRows = document.querySelectorAll('#coursesTableBody tr');
                if (tableRows.length > 0) {
                    // Find the row that contains our new course
                    for (let row of tableRows) {
                        const codeCell = row.querySelector('.course-code');
                        if (codeCell && codeCell.textContent === newCourse.code) {
                            row.style.backgroundColor = '#d4edda';
                            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            
                            setTimeout(() => {
                                row.style.backgroundColor = '';
                            }, 3000);
                            break;
                        }
                    }
                }
            }, 200);
        }
    }, 100);
}

// Function to reset filters
function resetFilters() {
    document.getElementById('schoolYear').value = '';
    document.getElementById('semester').value = '';
    populateCoursesTable(courseData);
}

// Function to handle logout
function handleLogout() {
    if (confirm('Are you sure you want to logout?')) {
        // Clear any stored data if needed
        // Redirect to login page or home page
        window.location.href = 'login.html';
    }
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Set default filter values and populate table
    document.getElementById('schoolYear').value = '2023-2024';
    document.getElementById('semester').value = 'Second';
    
    // Apply initial filter
    filterCourses();
    
    // Add event listeners for filter changes
    document.getElementById('schoolYear').addEventListener('change', filterCourses);
    document.getElementById('semester').addEventListener('change', filterCourses);
    
    // Add form submission handler
    document.getElementById('addCourseForm').addEventListener('submit', addNewCourse);
    
    // Add logout button event listener
    const logoutBtn = document.querySelector('.logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', handleLogout);
    }
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(event) {
        // ESC key to close modal
        if (event.key === 'Escape') {
            const modal = document.getElementById('addCourseModal');
            if (modal.style.display === 'block') {
                closeAddCourseModal();
            }
            
            // Close dropdown if open
            const dropdown = document.getElementById('userDropdown');
            if (dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        }
        
        // Ctrl+A to add new course
        if (event.ctrlKey && event.key === 'a') {
            event.preventDefault();
            openAddCourseModal();
        }
    });
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