// Global variables
let courseData = [];

function toggleUserDropdown() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('show');
}

// Load statistics and course dropdown on page load
document.addEventListener('DOMContentLoaded', function() {
    loadStatistics();
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('userDropdown');
        const dropdownHeader = document.querySelector('.dropdown-header');
        
        if (dropdown.classList.contains('show') && 
            !dropdown.contains(event.target) && 
            !dropdownHeader.contains(event.target)) {
            dropdown.classList.remove('show');
        }
    });
});

async function loadStatistics() {
    try {
        const response = await fetch('reports_api.php');
        const data = await response.json();
        
        if (data.success) {
            // Update statistics
            document.getElementById('totalCourses').textContent = data.totalCourses;
            document.getElementById('totalStudents').textContent = data.totalStudents;
            document.getElementById('averageGrade').textContent = data.averageGrade;
            document.getElementById('passRate').textContent = data.passRate + '%';
            
            // Populate course dropdown
            courseData = data.courses;
            populateCourseDropdown();
        } else {
            showAlert('Error loading statistics: ' + data.message, 'error');
        }
    } catch (error) {
        showAlert('Error loading statistics: ' + error.message, 'error');
    }
}

function populateCourseDropdown() {
    const dropdown = document.getElementById('reportCourse');
    
    // Clear dropdown except for the first option
    while (dropdown.options.length > 1) {
        dropdown.remove(1);
    }
    
    // Add courses to dropdown
    courseData.forEach(course => {
        const option = document.createElement('option');
        option.value = course.id;
        option.textContent = `${course.code} - ${course.subject} (${course.section_code})`;
        dropdown.appendChild(option);
    });
}

async function generateCourseReport() {
    const courseId = document.getElementById('reportCourse').value;
    
    if (!courseId) {
        document.getElementById('courseReportSection').style.display = 'none';
        return;
    }
    
    try {
        const response = await fetch(`reports_api.php?course_id=${courseId}`);
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('courseReportSection').style.display = 'block';
            const tbody = document.getElementById('reportTableBody');
            tbody.innerHTML = '';
            
            if (data.students.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px; color: #666;">No students found for this course</td></tr>';
                return;
            }
            
            data.students.forEach(student => {
                // Calculate status based on computed grade
                const computedGrade = parseFloat(student.computed_grade);
                let status = '';
                let statusColor = '';
                
                if (computedGrade !== null && !isNaN(computedGrade)) {
                    status = computedGrade < 3.00 ? 'PASSED' : 'FAILED';
                    statusColor = computedGrade < 3.00 ? 'green' : 'red';
                }
                
                const row = `
                    <tr>
                        <td>${student.full_name || ''}</td>
                        <td>${student.student_number || ''}</td>
                        <td>${student.first_grade || ''}</td>
                        <td>${student.second_grade || ''}</td>
                        <td>${student.computed_grade || ''}</td>
                        <td>${student.final_grade || ''}</td>
                        <td style="color: ${statusColor}; font-weight: bold;">${status}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        } else {
            showAlert('Error generating report: ' + data.message, 'error');
        }
    } catch (error) {
        showAlert('Error generating report: ' + error.message, 'error');
    }
}

async function logout() {
    if (confirm('Are you sure you want to logout?')) {
        try {
            const response = await fetch('auth_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'logout' })
            });

            const data = await response.json();
            
            if (data.success) {
                window.location.href = 'index.html';
            } else {
                showAlert('Logout failed: ' + data.message, 'error');
            }
        } catch (error) {
            showAlert('Error during logout: ' + error.message, 'error');
        }
    }
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
    
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass}`;
    alert.textContent = message;
    
    alertContainer.appendChild(alert);
    
    // Remove alert after 5 seconds
    setTimeout(() => {
        alert.remove();
    }, 5000);
}