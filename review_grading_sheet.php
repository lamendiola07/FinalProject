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
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex; /* Add flex display */
            align-items: center; /* Center vertically */
            justify-content: center; /* Center horizontally */
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 0 auto; /* Changed from 10% auto to 0 auto */
            padding: 30px;
            border: none;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh; /* Limit height to 80% of viewport height */
            overflow-y: auto; /* Enable vertical scrolling for content */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #333;
            font-size: 1.5em;
        }
        
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .close:hover,
        .close:focus {
            color: #000;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
        }
        
        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 25px;
        }
        
        .modal-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .modal-btn.primary {
            background-color: #4CAF50;
            color: white;
        }
        
        .modal-btn.primary:hover {
            background-color: #45a049;
            transform: translateY(-2px);
        }
        
        .modal-btn.secondary {
            background-color: #f44336;
            color: white;
        }
        
        .modal-btn.secondary:hover {
            background-color: #da190b;
            transform: translateY(-2px);
        }
        
        .error-message {
            color: #f44336;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }
        
        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        
        .btn-sm:hover {
            background-color: #0056b3;
        }
        
        .item-manager-content {
            margin-bottom: 20px;
        }
        
        .add-item-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .form-row {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .items-scores-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        
        .items-scores-table th,
        .items-scores-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: center;
        }
        
        .items-scores-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        .items-scores-table input {
            width: 60px;
            padding: 2px;
            text-align: center;
            border: 1px solid #ccc;
        }
        
        .summary-section {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .table-container {
            position: relative;
        }
        
        /* Excel-like table styles */
        .excel-manager-content {
            height: calc(95vh - 200px);
            display: flex;
            flex-direction: column;
        }
        
        .excel-toolbar {
            padding: 10px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #ddd;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .toolbar-info {
            margin-left: auto;
            font-weight: bold;
            color: #666;
        }
        
        .excel-table-container {
            flex: 1;
            overflow: auto;
            border: 1px solid #ddd;
            position: relative;
        }
        
        .excel-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 12px;
            background-color: white;
        }
        
        .excel-table th,
        .excel-table td {
            border: 1px solid #ddd;
            padding: 4px 6px;
            text-align: center;
            position: relative;
            min-width: 80px;
        }
        
        .excel-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .fixed-column {
            position: sticky;
            left: 0;
            background-color: #f8f9fa !important;
            z-index: 11;
            min-width: 200px;
            max-width: 200px;
            text-align: left;
            padding-left: 8px;
        }
        
        .student-column {
            font-weight: bold;
        }
        
        .add-column {
            background-color: #e3f2fd !important;
            cursor: pointer;
            color: #1976d2;
            font-weight: bold;
            min-width: 100px;
        }
        
        .add-column:hover {
            background-color: #bbdefb !important;
        }
        
        .average-column {
            background-color: #fff3e0 !important;
            font-weight: bold;
            min-width: 80px;
        }
        
        .item-header {
            background-color: #e8f5e8 !important;
            position: relative;
            min-width: 100px;
        }
        
        .item-header input {
            width: 100%;
            border: none;
            background: transparent;
            text-align: center;
            font-weight: bold;
            padding: 2px;
        }
        
        .item-header .delete-btn {
            position: absolute;
            top: 2px;
            right: 2px;
            background: #ff4444;
            color: white;
            border: none;
            border-radius: 2px;
            width: 16px;
            height: 16px;
            font-size: 10px;
            cursor: pointer;
            display: none;
        }
        
        .item-header:hover .delete-btn {
            display: block;
        }
        
        .score-input {
            width: 100%;
            border: none;
            text-align: center;
            padding: 2px;
            background: transparent;
        }
        
        .score-input:focus {
            background-color: #fff;
            border: 2px solid #4CAF50;
            outline: none;
        }
        
        .excel-table tbody tr:hover {
            background-color: #f5f5f5;
        }
        
        .excel-table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }
        
        .average-cell {
            font-weight: bold;
            background-color: #fff3e0;
        }
        
        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin: 2px;
        }
        
        .btn-primary { background-color: #007bff; color: white; }
        .btn-success { background-color: #28a745; color: white; }
        .btn-info { background-color: #17a2b8; color: white; }
        .btn-danger { background-color: #dc3545; color: white; }
        
        .btn-sm:hover { opacity: 0.8; }
        
        /* Grade Weights Modal Styles */
        .settings-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        .settings-section h4 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #700000;
            font-size: 16px;
        }
        
        .weight-percentage {
            display: inline-block;
            margin-left: 10px;
            font-weight: bold;
            color: #555;
        }
        
        .total-weight {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
            font-weight: bold;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-primary {
            background-color: #700000;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-info {
            background-color: #17a2b8;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        /* Additional styles for the settings section */
        .settings-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        
        .settings-section h4 {
            margin-top: 0;
            color: #700000;
            margin-bottom: 15px;
        }
        
        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .form-group label {
            width: 150px;
            font-weight: bold;
        }
        
        .form-group input, .form-group select {
            width: 80px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .weight-percentage {
            margin-left: 10px;
            width: 50px;
        }
        
        .total-weight {
            font-weight: bold;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
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
            <button class="btn btn-info" onclick="openGradeWeightsModal()">Adjust Grade Weights</button>
        </div>

        <div class="status-section">
            <div>
                <div class="status-item">
                    <div class="status-label">Subject</div>
                    <div class="status-value" id="subjectInfo">CMPE 30250: Computer Engineering Practice and Design 1</div>
                </div>
                <div class="status-item">
                    <div class="status-label">Faculty</div>
                    <div class="status-value" id="facultyInfo"><?php echo htmlspecialchars($_SESSION['user_name'] ?? '');?> 
<!--
TODO: Update the CSV export functionality in the exportToCSV() function to include recitation and exam scores.
Add the following code after the assignment scores section:

if (midtermRecitations.success) {
    midtermRecitations.items.forEach(item => {
        csvContent += ',Midterm Recitation: ' + item.name + ' (' + item.max_score + 'pts)';
    });
}
if (midtermExams.success) {
    midtermExams.items.forEach(item => {
        csvContent += ',Midterm Exam: ' + item.name + ' (' + item.max_score + 'pts)';
    });
}

if (finalRecitations.success) {
    finalRecitations.items.forEach(item => {
        csvContent += ',Final Recitation: ' + item.name + ' (' + item.max_score + 'pts)';
    });
}
if (finalExams.success) {
    finalExams.items.forEach(item => {
        csvContent += ',Final Exam: ' + item.name + ' (' + item.max_score + 'pts)';
    });
}

And update the header row to include recitation and exam averages:

csvContent += ',Midterm Quiz Avg,Midterm Activity Avg,Midterm Assignment Avg,Midterm Recitation Avg,Midterm Exam Avg,Final Quiz Avg,Final Activity Avg,Final Assignment Avg,Final Recitation Avg,Final Exam Avg,Attendance Rate
';

Also update the Promise.all to include recitation and exam scores:

const [mQuizScores, mActivityScores, mAssignmentScores, mRecitationScores, mExamScores, fQuizScores, fActivityScores, fAssignmentScores, fRecitationScores, fExamScores] = await Promise.all([
    fetch(`items_api.php?action=get_scores&course_id=&term=midterm&type=quiz`).then(r => r.json()),
    fetch(`items_api.php?action=get_scores&course_id=&term=midterm&type=activity`).then(r => r.json()),
    fetch(`items_api.php?action=get_scores&course_id=&term=midterm&type=assignment`).then(r => r.json()),
    fetch(`items_api.php?action=get_scores&course_id=&term=midterm&type=recitation`).then(r => r.json()),
    fetch(`items_api.php?action=get_scores&course_id=&term=midterm&type=exam`).then(r => r.json()),
    fetch(`items_api.php?action=get_scores&course_id=&term=final&type=quiz`).then(r => r.json()),
    fetch(`items_api.php?action=get_scores&course_id=&term=final&type=activity`).then(r => r.json()),
    fetch(`items_api.php?action=get_scores&course_id=&term=final&type=assignment`).then(r => r.json()),
    fetch(`items_api.php?action=get_scores&course_id=&term=final&type=recitation`).then(r => r.json()),
    fetch(`items_api.php?action=get_scores&course_id=&term=final&type=exam`).then(r => r.json())
]);

And add the item scores for recitation and exam:

addItemScores(midtermRecitations, mRecitationScores);
-->

?></div>
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
                    <button class="save-button" onclick="openAttendanceModal()">Add Attendance Record</button>
                </div>
                
                <!-- Attendance Modal -->
                <div id="attendanceModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Add Attendance Record</h3>
                            <span class="close" onclick="closeAttendanceModal()">&times;</span>
                        </div>
                        <form id="attendanceForm">
                            <div class="form-group">
                                <label for="meetingNumber">Meeting Number:</label>
                                <input type="number" id="meetingNumber" min="1" required>
                                <div class="error-message" id="meetingError">Meeting number already exists</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="meetingDate">Meeting Date:</label>
                                <input type="date" id="meetingDate" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="attendanceStatus">Attendance Status:</label>
                                <select id="attendanceStatus" required>
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                </select>
                            </div>
                            
                            <div class="modal-buttons">
                                <button type="button" class="modal-btn secondary" onclick="closeAttendanceModal()">Cancel</button>
                                <button type="submit" class="modal-btn primary">Add Record</button>
                            </div>
                        </form>
                    </div>
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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Attendance</td>
                                <td>10%</td>
                                <td id="midtermAttendanceScore">0.00</td>
                                <td id="midtermAttendanceWeighted">0.00</td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>Quiz</td>
                                <td>20%</td>
                                <td>
                                    <input type="number" class="grade-input" id="midtermQuizScore" min="0" max="100" step="0.01" value="0" onchange="calculateMidtermGrade()" readonly>
                                </td>
                                <td id="midtermQuizWeighted">0.00</td>
                                <td><button class="btn btn-sm" onclick="openExcelManager('quiz', 'midterm')">Add Quiz</button></td>
                            </tr>
                            <tr>
                                <td>Activity</td>
                                <td>20%</td>
                                <td>
                                    <input type="number" class="grade-input" id="midtermActivityScore" min="0" max="100" step="0.01" value="0" onchange="calculateMidtermGrade()" readonly>
                                </td>
                                <td id="midtermActivityWeighted">0.00</td>
                                <td><button class="btn btn-sm" onclick="openExcelManager('activity', 'midterm')">Add Activity</button></td>
                            </tr>
                            <tr>
                                <td>Assignment</td>
                                <td>10%</td>
                                <td>
                                    <input type="number" class="grade-input" id="midtermAssignmentScore" min="0" max="100" step="0.01" value="0" onchange="calculateMidtermGrade()" readonly>
                                </td>
                                <td id="midtermAssignmentWeighted">0.00</td>
                                <td><button class="btn btn-sm" onclick="openExcelManager('assignment', 'midterm')">Add Assignment</button></td>
                            </tr>
                                <tr>
                                <td>Recitation</td>
                                <td>10%</td>
                                <td>
                                    <input type="number" class="grade-input" id="midtermRecitationScore" min="0" max="100" step="0.01" value="0" onchange="calculateMidtermGrade()" readonly>
                                </td>
                                <td id="midtermRecitationWeighted">0.00</td>
                                <td><button class="btn btn-sm" onclick="openExcelManager('recitation', 'midterm')">Add Recitation</button></td>
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Midterm Exam</td>
                                    <td>15%</td>
                                    <td>
                                        <input type="number" class="grade-input" id="midtermExamScore" min="0" max="100" step="0.01" value="0" onchange="calculateMidtermGrade()" readonly>
                                    </td>
                                    <td id="midtermExamWeighted">0.00</td>
                                    <td><button class="btn btn-sm" onclick="openExcelManager('exam', 'midterm')">Add Exam</button></td>
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Attendance</td>
                                    <td>10%</td>
                                    <td id="finalAttendanceScore">0.00</td>
                                    <td id="finalAttendanceWeighted">0.00</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                <td>Quiz</td>
                                <td>20%</td>
                                <td>
                                    <input type="number" class="grade-input" id="finalQuizScore" min="0" max="100" step="0.01" value="0" onchange="calculateFinalGrade()" readonly>
                                </td>
                                <td id="finalQuizWeighted">0.00</td>
                                <td><button class="btn btn-sm" onclick="openExcelManager('quiz', 'final')">Add Quiz</button></td>
                            </tr>
                            <tr>
                                <td>Activity</td>
                                <td>20%</td>
                                <td>
                                    <input type="number" class="grade-input" id="finalActivityScore" min="0" max="100" step="0.01" value="0" onchange="calculateFinalGrade()" readonly>
                                </td>
                                <td id="finalActivityWeighted">0.00</td>
                                <td><button class="btn btn-sm" onclick="openExcelManager('activity', 'final')">Add Activity</button></td>
                            </tr>
                            <tr>
                                <td>Assignment</td>
                                <td>10%</td>
                                <td>
                                    <input type="number" class="grade-input" id="finalAssignmentScore" min="0" max="100" step="0.01" value="0" onchange="calculateFinalGrade()" readonly>
                                </td>
                                <td id="finalAssignmentWeighted">0.00</td>
                                <td><button class="btn btn-sm" onclick="openExcelManager('assignment', 'final')">Add Assignment</button></td>
                            </tr>
                                <tr>
                                    <td>Recitation</td>
                                    <td>10%</td>
                                    <td>
                                        <input type="number" class="grade-input" id="finalRecitationScore" min="0" max="100" step="0.01" value="0" onchange="calculateFinalGrade()" readonly>
                                    </td>
                                    <td id="finalRecitationWeighted">0.00</td>
                                    <td><button class="btn btn-sm" onclick="openExcelManager('recitation', 'final')">Add Recitation</button></td>
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Final Exam</td>
                                    <td>15%</td>
                                    <td>
                                        <input type="number" class="grade-input" id="finalExamScore" min="0" max="100" step="0.01" value="0" onchange="calculateFinalGrade()" readonly>
                                    </td>
                                    <td id="finalExamWeighted">0.00</td>
                                    <td><button class="btn btn-sm" onclick="openExcelManager('exam', 'final')">Add Exam</button></td>
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

    <!-- Item Manager Modal -->
    <div id="itemManagerModal" class="modal" style="z-index: 1001;">
        <div class="modal-content" style="width: 95%; max-width: 1200px; max-height: 80vh; overflow-y: auto;">
            <div class="modal-header">
                <h3 id="itemManagerTitle">Manage Items</h3>
                <span class="close" onclick="closeItemManager()">&times;</span>
            </div>
            
            <div class="item-manager-content">
                <!-- Add New Item Section -->
                <div class="add-item-section">
                    <h4>Add New Item</h4>
                    <form id="addItemForm">
                        <div class="form-row">
                            <div class="form-group" style="flex: 1; margin-right: 10px;">
                                <label for="itemName">Item Name:</label>
                                <input type="text" id="itemName" required>
                            </div>
                            <div class="form-group" style="flex: 1; margin-right: 10px;">
                                <label for="itemMaxScore">Max Score:</label>
                                <input type="number" id="itemMaxScore" min="1" value="100" required>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label for="itemDate">Date:</label>
                                <input type="date" id="itemDate" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Item</button>
                    </form>
                </div>
                
                <!-- Items and Scores Table -->
                <div class="items-table-section">
                    <h4>Items and Student Scores</h4>
                    <div class="table-container" style="max-height: 400px; overflow: auto;">
                        <table class="items-scores-table" id="itemsScoresTable">
                            <thead>
                                <tr>
                                    <th style="position: sticky; left: 0; background: #f2f2f2; z-index: 10;">Student</th>
                                    <!-- Item columns will be added dynamically -->
                                    <th style="background: #e9ecef;">Average</th>
                                    <th style="background: #e9ecef;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="itemsScoresTableBody">
                                <!-- Rows will be added dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Summary Section -->
                <div class="summary-section">
                    <h4>Summary</h4>
                    <p>Current Average: <span id="currentAverage">0.00</span></p>
                    <p>Total Items: <span id="totalItems">0</span></p>
                </div>
            </div>
            
            <div class="modal-buttons">
                <button type="button" class="modal-btn secondary" onclick="closeItemManager()">Close</button>
                <button type="button" class="modal-btn primary" onclick="saveItemScores()">Save All Scores</button>
            </div>
        </div>
    </div>

    <!-- Grade Weights Settings Modal -->
    <div id="gradeWeightsModal" class="modal">
        <div class="modal-content" style="max-height: 80vh; overflow-y: auto;">
            <div class="modal-header">
                <h3>Grade Component Weights</h3>
                <span class="close" onclick="closeGradeWeightsModal()">&times;</span>
            </div>
            <form id="gradeWeightsForm" onsubmit="saveGradeWeights(event)">
                <div class="settings-section">
                    <h4>Class Participation Components (70%)</h4>
                    <div class="form-group">
                        <label for="attendanceWeight">Attendance Weight:</label>
                        <input type="number" id="attendanceWeight" min="0" max="1" step="0.01" value="0.1" required>
                        <span class="weight-percentage">10%</span>
                    </div>
                    <div class="form-group">
                        <label for="quizWeight">Quiz Weight:</label>
                        <input type="number" id="quizWeight" min="0" max="1" step="0.01" value="0.2" required>
                        <span class="weight-percentage">20%</span>
                    </div>
                    <div class="form-group">
                        <label for="activityWeight">Activity Weight:</label>
                        <input type="number" id="activityWeight" min="0" max="1" step="0.01" value="0.2" required>
                        <span class="weight-percentage">20%</span>
                    </div>
                    <div class="form-group">
                        <label for="assignmentWeight">Assignment Weight:</label>
                        <input type="number" id="assignmentWeight" min="0" max="1" step="0.01" value="0.1" required>
                        <span class="weight-percentage">10%</span>
                    </div>
                    <div class="form-group">
                        <label for="recitationWeight">Recitation Weight:</label>
                        <input type="number" id="recitationWeight" min="0" max="1" step="0.01" value="0.1" required>
                        <span class="weight-percentage">10%</span>
                    </div>
                    <div class="form-group total-weight">
                        <label>Total Class Participation:</label>
                        <span id="totalClassParticipationWeight">70%</span>
                    </div>
                </div>
                
                <div class="settings-section">
                    <h4>Major Examination Component</h4>
                    <div class="form-group">
                        <label for="examWeight">Exam Weight:</label>
                        <input type="number" id="examWeight" min="0" max="1" step="0.01" value="0.3" required>
                        <span class="weight-percentage">30%</span>
                    </div>
                </div>
                
                <div class="settings-section">
                    <h4>Grade Computation Base</h4>
                    <div class="form-group">
                        <label for="gradeComputationMethod">Computation Method:</label>
                        <select id="gradeComputationMethod" required>
                            <option value="base_50">Base 50 (50-100)</option>
                            <option value="base_0">Base 0 (0-100)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="passingGradeInput">Passing Grade:</label>
                        <input type="number" id="passingGradeInput" min="50" max="100" step="0.01" value="75.00" required>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeGradeWeightsModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Excel-like Manager Modal -->
    <div id="excelManagerModal" class="modal" style="z-index: 1001; display: none; align-items: center; justify-content: center;">
        <div class="modal-content" style="width: 98%; max-width: 1400px; max-height: 80vh; overflow-y: auto; margin: 0 auto;">
            <div class="modal-header">
                <h3 id="excelManagerTitle">Manage Items</h3>
                <span class="close" onclick="closeExcelManager()">&times;</span>
            </div>
            
            <div class="excel-manager-content" style="height: calc(100% - 100px); display: flex; flex-direction: column;">
                <!-- Toolbar -->
                <div class="excel-toolbar">
                    <button class="btn btn-primary" onclick="addNewItem()">+ Add New Item</button>
                    <button class="btn btn-success" onclick="saveAllData()">💾 Save All</button>
                    <button class="btn btn-info" onclick="refreshData()">🔄 Refresh</button>
                    <span class="toolbar-info">Total Items: <span id="itemCount">0</span> | Students: <span id="studentCount">0</span></span>
                </div>
                
                <!-- Excel-like Table Container -->
                <div class="excel-table-container" style="flex: 1; overflow: auto; border: 1px solid #ddd;">
                    <table class="excel-table" id="excelTable">
                        <thead>
                            <tr id="excelTableHeader">
                                <th class="fixed-column student-column">Student Name</th>
                                <th class="add-column" onclick="addNewItem()">+ Add Item</th>
                                <th class="average-column">Average</th>
                            </tr>
                            <tr id="excelTableSubHeader">
                                <th class="fixed-column"></th>
                                <th class="add-column"></th>
                                <th class="average-column"></th>
                            </tr>
                        </thead>
                        <tbody id="excelTableBody">
                            <!-- Rows will be generated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="modal-buttons">
                <button type="button" class="modal-btn secondary" onclick="closeExcelManager()">Close</button>
                <button type="button" class="modal-btn primary" onclick="saveAllData()">Save All Changes</button>
            </div>
        </div>
    </div>

    <script>
        // Helper function to format scores consistently
        function formatScore(score) {
            // If the score is a whole number, display it as an integer
            return Number.isInteger(parseFloat(score)) ? parseInt(score) : parseFloat(score).toFixed(2);
        }

        // Global variables
        let courseId = null;
        let studentData = [];
        let currentStudent = null;
        let attendanceRecords = [];
        let debounceTimers = {};
        let coursePassingGrade = 75.00;
        let courseGradeComputationMethod = 'base_50';
        let currentItemType = '';
        let currentTerm = '';
        let currentItems = [];
        let currentStudentScores = {};
        
        // Grade component weights
        let gradeWeights = {
            attendance: 0.1,
            quiz: 0.2,
            activity: 0.2,
            assignment: 0.1,
            recitation: 0.1,
            exam: 0.3
        };


        // Function to open item manager
        function openItemManager(itemType, term) {
            if (!currentStudent) {
                alert('Please select a student first');
                return;
            }
            
            currentItemType = itemType;
            currentTerm = term;
            
            const modal = document.getElementById('itemManagerModal');
            const title = document.getElementById('itemManagerTitle');
            title.textContent = `Manage ${itemType.charAt(0).toUpperCase() + itemType.slice(1)}s - ${term.charAt(0).toUpperCase() + term.slice(1)} Term`;
            
            // Load items and scores
            loadItemsAndScores();
            
            modal.style.display = 'block';
        }
        
        // Function to close item manager
        function closeItemManager() {
            const modal = document.getElementById('itemManagerModal');
            modal.style.display = 'none';
            document.getElementById('addItemForm').reset();
        }
        
        // Function to load items and scores
        async function loadItemsAndScores() {
            try {
                // Load items for this course, term, and type
                const itemsResponse = await fetch(`items_api.php?action=get_items&course_id=${courseId}&term=${currentTerm}&type=${currentItemType}`);
                const itemsData = await itemsResponse.json();
                
                if (itemsData.success) {
                    currentItems = itemsData.items;
                } else {
                    currentItems = [];
                }
                
                // Load scores for all students
                const scoresResponse = await fetch(`items_api.php?action=get_scores&course_id=${courseId}&term=${currentTerm}&type=${currentItemType}`);
                const scoresData = await scoresResponse.json();
                
                if (scoresData.success) {
                    currentStudentScores = scoresData.scores;
                } else {
                    currentStudentScores = {};
                }
                
                // Render the table
                renderItemsTable();
                updateSummary();
                
            } catch (error) {
                console.error('Error loading items and scores:', error);
                alert('Error loading data: ' + error.message);
            }
        }
        
        // Function to render items table
        function renderItemsTable() {
            const table = document.getElementById('itemsScoresTable');
            const thead = table.querySelector('thead tr');
            const tbody = document.getElementById('itemsScoresTableBody');
            
            // Clear existing content
            thead.innerHTML = '<th style="position: sticky; left: 0; background: #f2f2f2; z-index: 10;">Student</th>';
            tbody.innerHTML = '';
            
            // Add item columns
            currentItems.forEach(item => {
                const th = document.createElement('th');
                th.innerHTML = `${item.name}<br><small>(${item.max_score} pts)</small><br><button class="btn btn-sm" onclick="deleteItem(${item.id})" style="background: #dc3545; margin-top: 2px;">Delete</button>`;
                thead.appendChild(th);
            });
            
            // Add average and actions columns
            thead.innerHTML += '<th style="background: #e9ecef;">Average</th>';
            
            // Add student rows
            studentData.forEach(student => {
                const row = document.createElement('tr');
                
                // Student name column
                const nameCell = document.createElement('td');
                nameCell.style.cssText = 'position: sticky; left: 0; background: white; font-weight: bold; text-align: left; padding-left: 8px;';
                nameCell.textContent = student.full_name;
                row.appendChild(nameCell);
                
                // Score columns for each item
                let totalScore = 0;
                let totalMaxScore = 0;
                
                currentItems.forEach(item => {
                    const scoreCell = document.createElement('td');
                    const currentScore = currentStudentScores[student.student_number]?.[item.id] || 0;
                    
                    scoreCell.innerHTML = `<input type="number" min="0" max="${item.max_score}" step="0.01" value="${currentScore}" onchange="updateStudentScore('${student.student_number}', ${item.id}, this.value, ${item.max_score})">`;
                    row.appendChild(scoreCell);
                    
                    totalScore += parseFloat(currentScore);
                    totalMaxScore += parseFloat(item.max_score);
                });
                
                // Average column
                const avgCell = document.createElement('td');
                const average = totalMaxScore > 0 ? (totalScore / totalMaxScore * 100) : 0;
                avgCell.textContent = average.toFixed(2) + '%';
                avgCell.style.fontWeight = 'bold';
                row.appendChild(avgCell);
                
                tbody.appendChild(row);
            });
        }
        
        // Function to update student score
        function updateStudentScore(studentNumber, itemId, score, maxScore) {
            if (!currentStudentScores[studentNumber]) {
                currentStudentScores[studentNumber] = {};
            }
            
            // Validate score
            const numScore = parseFloat(score) || 0;
            if (numScore > maxScore) {
                alert(`Score cannot exceed ${maxScore}`);
                return;
            }
            
            currentStudentScores[studentNumber][itemId] = numScore;
            
            // Recalculate averages
            renderItemsTable();
            updateSummary();
            
            // Update the main grade if this is the current student
            if (studentNumber === currentStudent.student_number) {
                updateMainGradeFromItems();
            }
        }
        
        // Function to update main grade from items
        function updateMainGradeFromItems() {
            let totalScore = 0;
            let totalMaxScore = 0;
            
            currentItems.forEach(item => {
                const score = currentStudentScores[currentStudent.student_number]?.[item.id] || 0;
                totalScore += parseFloat(score);
                totalMaxScore += parseFloat(item.max_score);
            });
            
            const average = totalMaxScore > 0 ? (totalScore / totalMaxScore * 100) : 0;
            
            // Update the appropriate grade input
            const gradeInputId = `${currentTerm}${currentItemType.charAt(0).toUpperCase() + currentItemType.slice(1)}Score`;
            const gradeInput = document.getElementById(gradeInputId);
            if (gradeInput) {
                gradeInput.value = average.toFixed(2);
                
                // Trigger grade calculation
                if (currentTerm === 'midterm') {
                    calculateMidtermGrade();
                } else {
                    calculateFinalGrade();
                }
            }
        }
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

            // Load course settings
            fetch(`course_api.php?id=${courseId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.course) {
                        coursePassingGrade = parseFloat(data.course.passing_grade) || 75.00;
                        courseGradeComputationMethod = data.course.grade_computation_method || 'base_50';
                        
                        // Load grade weights if available
                        if (data.course.grade_weights) {
                            try {
                                const weights = JSON.parse(data.course.grade_weights);
                                if (weights) {
                                    gradeWeights = weights;
                                }
                            } catch (e) {
                                console.error('Error parsing grade weights:', e);
                            }
                        }
                        
                        // Update UI to show current settings
                        document.getElementById('coursePassingGrade').textContent = coursePassingGrade;
                        document.getElementById('courseGradeMethod').textContent = 
                            courseGradeComputationMethod === 'base_50' ? 'Base 50' : 'Base 0';
                            
                        // Update grade weights form
                        updateGradeWeightsForm();
                    }
                })
                .catch(error => console.error('Error loading course settings:', error));
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

        // Function to open attendance modal
        function openAttendanceModal() {
            const modal = document.getElementById('attendanceModal');
            const meetingNumberInput = document.getElementById('meetingNumber');
            const meetingDateInput = document.getElementById('meetingDate');
            const attendanceStatusSelect = document.getElementById('attendanceStatus');
            const errorMessage = document.getElementById('meetingError');
            
            // Set default values
            meetingNumberInput.value = attendanceRecords.length + 1;
            meetingDateInput.value = new Date().toISOString().split('T')[0];
            attendanceStatusSelect.value = 'present';
            errorMessage.style.display = 'none';
            
            modal.style.display = 'block';
            meetingNumberInput.focus();
        }

        // Function to close attendance modal
        function closeAttendanceModal() {
            const modal = document.getElementById('attendanceModal');
            modal.style.display = 'none';
            document.getElementById('attendanceForm').reset();
        }

        // Function to validate meeting number
        function validateMeetingNumber(meetingNum) {
            return !attendanceRecords.some(record => record.meeting === meetingNum);
        }

        // Enhanced function to add attendance record
async function addAttendanceRecord(meetingNum, date, status) {
    // Check if meeting number already exists
    const existingRecord = attendanceRecords.find(record => record.meeting === meetingNum);
    if (existingRecord) {
        const overwrite = confirm(`Meeting ${meetingNum} already exists. Do you want to overwrite it?`);
        if (!overwrite) return false;
        
        // Remove the existing record
        const index = attendanceRecords.findIndex(record => record.meeting === meetingNum);
        attendanceRecords.splice(index, 1);
    }
    
    // Add the new record
    attendanceRecords.push({
        meeting: meetingNum,
        date: date,
        status: status
    });
    
    // Sort attendance records by meeting number
    attendanceRecords.sort((a, b) => a.meeting - b.meeting);
    
    // Update the table
    updateAttendanceTable();
    
    // Save the attendance record to the database immediately
    try {
        const attendanceData = [{
            meeting_number: meetingNum,
            meeting_date: date,
            status: status
        }];
        
        const response = await fetch('detailed_grades_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                course_id: courseId,
                student_number: currentStudent.student_number,
                grades: [], // No grades to update
                attendance: attendanceData
            })
        });
        
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message);
        }
        
        console.log('Attendance record saved to database successfully');
    } catch (error) {
        console.error('Error saving attendance record:', error);
        alert('Error saving attendance record: ' + error.message);
    }
    
    console.log('Attendance records after adding:', attendanceRecords);
    return true;
}

        // Function to load grade data
        function loadGradeData() {
            // Initialize all grade components to 0
            document.getElementById('midtermQuizScore').value = '0';
            document.getElementById('midtermActivityScore').value = '0';
            document.getElementById('midtermAssignmentScore').value = '0';
            document.getElementById('midtermRecitationScore').value = '0';
            document.getElementById('midtermExamScore').value = '0';
            
            document.getElementById('finalQuizScore').value = '0';
            document.getElementById('finalActivityScore').value = '0';
            document.getElementById('finalAssignmentScore').value = '0';
            document.getElementById('finalRecitationScore').value = '0';
            document.getElementById('finalExamScore').value = '0';
            
            // Load detailed grades from database
            fetch(`detailed_grades_api.php?course_id=${courseId}&student_number=${currentStudent.student_number}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Load grade components
                        data.grades.forEach(grade => {
                            const elementId = `${grade.term}${grade.component.charAt(0).toUpperCase() + grade.component.slice(1)}Score`;
                            const element = document.getElementById(elementId);
                            if (element) {
                                element.value = grade.score;
                            }
                        });
                        
                        // Load attendance records
                        attendanceRecords = data.attendance.map(record => ({
                            meeting: record.meeting_number,
                            date: record.meeting_date,
                            status: record.status
                        }));
                        
                        updateAttendanceTable();
                        calculateMidtermGrade();
                        calculateFinalGrade();
                    }
                })
                .catch(error => {
                    console.error('Error loading detailed grades:', error);
                });
            
            // Load basic grades if available
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
            const attendanceScore = parseFloat(document.getElementById('midtermAttendanceScore').textContent) || 0;
            
            // Get component scores
            const quizScore = parseFloat(document.getElementById('midtermQuizScore').value) || 0;
            const activityScore = parseFloat(document.getElementById('midtermActivityScore').value) || 0;
            const assignmentScore = parseFloat(document.getElementById('midtermAssignmentScore').value) || 0;
            const recitationScore = parseFloat(document.getElementById('midtermRecitationScore').value) || 0;
            const examScore = parseFloat(document.getElementById('midtermExamScore').value) || 0;
            
            // Calculate weighted scores using dynamic weights
            const attendanceWeighted = (attendanceScore * gradeWeights.attendance);
            const quizWeighted = (quizScore * gradeWeights.quiz);
            const activityWeighted = (activityScore * gradeWeights.activity);
            const assignmentWeighted = (assignmentScore * gradeWeights.assignment);
            const recitationWeighted = (recitationScore * gradeWeights.recitation);
            
            // Update weighted score displays
            document.getElementById('midtermAttendanceWeighted').textContent = formatScore(attendanceWeighted);
            document.getElementById('midtermQuizWeighted').textContent = formatScore(quizWeighted);
            document.getElementById('midtermActivityWeighted').textContent = formatScore(activityWeighted);
            document.getElementById('midtermAssignmentWeighted').textContent = formatScore(assignmentWeighted);
            document.getElementById('midtermRecitationWeighted').textContent = formatScore(recitationWeighted);
            
            // Calculate class participation total
            const classParticipationTotal = attendanceWeighted + quizWeighted + activityWeighted + assignmentWeighted + recitationWeighted;
            document.getElementById('midtermClassParticipationTotal').textContent = formatScore(classParticipationTotal);
            document.getElementById('midtermClassParticipationSummary').textContent = formatScore(classParticipationTotal);
            
            // Calculate weighted exam score using dynamic weight
            const examWeighted = (examScore * gradeWeights.exam);
            document.getElementById('midtermExamWeighted').textContent = formatScore(examWeighted);
            document.getElementById('midtermExamTotal').textContent = formatScore(examWeighted);
            document.getElementById('midtermExamSummary').textContent = formatScore(examWeighted);
            
            // Calculate total midterm grade
            const totalMidtermGrade = classParticipationTotal + examWeighted;
            document.getElementById('totalMidtermGrade').textContent = formatScore(totalMidtermGrade);
            
            // Apply the grade computation method before rounding
            const adjustedGrade = applyGradeComputationMethod(totalMidtermGrade, 100);

            // Round the grade to the nearest whole number
            const roundedMidtermGrade = Math.round(adjustedGrade);
            document.getElementById('roundedMidtermGrade').textContent = roundedMidtermGrade;
            document.getElementById('semestralMidtermGrade').textContent = roundedMidtermGrade;
            
            // Recalculate semestral grade
            calculateSemestralGrade();
        }

        // Function to calculate final grade
        function calculateFinalGrade() {
            // Get attendance score
            const attendanceScore = parseFloat(document.getElementById('finalAttendanceScore').textContent) || 0;
            
            // Get component scores
            const quizScore = parseFloat(document.getElementById('finalQuizScore').value) || 0;
            const activityScore = parseFloat(document.getElementById('finalActivityScore').value) || 0;
            const assignmentScore = parseFloat(document.getElementById('finalAssignmentScore').value) || 0;
            const recitationScore = parseFloat(document.getElementById('finalRecitationScore').value) || 0;
            const examScore = parseFloat(document.getElementById('finalExamScore').value) || 0;
            
            // Calculate weighted scores using dynamic weights
            const attendanceWeighted = (attendanceScore * gradeWeights.attendance);
            const quizWeighted = (quizScore * gradeWeights.quiz);
            const activityWeighted = (activityScore * gradeWeights.activity);
            const assignmentWeighted = (assignmentScore * gradeWeights.assignment);
            const recitationWeighted = (recitationScore * gradeWeights.recitation);
            
            // Update weighted score displays
            document.getElementById('finalAttendanceWeighted').textContent = formatScore(attendanceWeighted);
            document.getElementById('finalQuizWeighted').textContent = formatScore(quizWeighted);
            document.getElementById('finalActivityWeighted').textContent = formatScore(activityWeighted);
            document.getElementById('finalAssignmentWeighted').textContent = formatScore(assignmentWeighted);
            document.getElementById('finalRecitationWeighted').textContent = formatScore(recitationWeighted);
            
            // Calculate class participation total
            const classParticipationTotal = attendanceWeighted + quizWeighted + activityWeighted + assignmentWeighted + recitationWeighted;
            document.getElementById('finalClassParticipationTotal').textContent = formatScore(classParticipationTotal);
            document.getElementById('finalClassParticipationSummary').textContent = formatScore(classParticipationTotal);
            
            // Calculate weighted exam score using dynamic weight
            const examWeighted = (examScore * gradeWeights.exam);
            document.getElementById('finalExamWeighted').textContent = formatScore(examWeighted);
            document.getElementById('finalExamTotal').textContent = formatScore(examWeighted);
            document.getElementById('finalExamSummary').textContent = formatScore(examWeighted);
            
            // Calculate total final grade
            const totalFinalGrade = classParticipationTotal + examWeighted;
            document.getElementById('totalFinalGrade').textContent = formatScore(totalFinalGrade);
            
            // Apply the grade computation method before rounding
            const adjustedGrade = applyGradeComputationMethod(totalFinalGrade, 100);
            
            // Round the grade to the nearest whole number
            const roundedFinalGrade = Math.round(adjustedGrade);
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
            document.getElementById('semestralGrade').textContent = formatScore(semestralGrade);
            
            // Get the equivalent grade based on the scale
            const equivalent = getGradeEquivalent(semestralGrade);
            document.getElementById('gradeEquivalent').textContent = equivalent;
        }

        // Function to get grade equivalent based on scale
        function getGradeEquivalent(score) {
            // If score is below passing grade, it's a failing grade
            if (score < coursePassingGrade) return '5.00';
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
            
            // Collect detailed grade data
            const detailedGrades = [
                // Midterm grades
                { term: 'midterm', component: 'quiz', score: parseFloat(document.getElementById('midtermQuizScore').value) || 0 },
                { term: 'midterm', component: 'activity', score: parseFloat(document.getElementById('midtermActivityScore').value) || 0 },
                { term: 'midterm', component: 'assignment', score: parseFloat(document.getElementById('midtermAssignmentScore').value) || 0 },
                { term: 'midterm', component: 'recitation', score: parseFloat(document.getElementById('midtermRecitationScore').value) || 0 },
                { term: 'midterm', component: 'exam', score: parseFloat(document.getElementById('midtermExamScore').value) || 0 },
                // Final grades
                { term: 'final', component: 'quiz', score: parseFloat(document.getElementById('finalQuizScore').value) || 0 },
                { term: 'final', component: 'activity', score: parseFloat(document.getElementById('finalActivityScore').value) || 0 },
                { term: 'final', component: 'assignment', score: parseFloat(document.getElementById('finalAssignmentScore').value) || 0 },
                { term: 'final', component: 'recitation', score: parseFloat(document.getElementById('finalRecitationScore').value) || 0 },
                { term: 'final', component: 'exam', score: parseFloat(document.getElementById('finalExamScore').value) || 0 }
            ];
            
            // Collect attendance data
            const attendanceData = attendanceRecords.map(record => ({
                meeting_number: record.meeting,
                meeting_date: record.date,
                status: record.status
            }));
            
            showSaveIndicator();
            
            // Save detailed data first
            fetch('detailed_grades_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    course_id: courseId,
                    student_number: currentStudent.student_number,
                    grades: detailedGrades,
                    attendance: attendanceData
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Now save the summary grades
                    return fetch('grades_api.php', {
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
                    });
                } else {
                    throw new Error(data.message);
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update local data
                    const studentIndex = studentData.findIndex(s => s.student_number === currentStudent.student_number);
                    if (studentIndex !== -1) {
                        studentData[studentIndex].first_grade = midtermGrade;
                        studentData[studentIndex].second_grade = finalGrade;
                        studentData[studentIndex].computed_grade = data.computed_grade;
                    }
                    
                    currentStudent.first_grade = midtermGrade;
                    currentStudent.second_grade = finalGrade;
                    currentStudent.computed_grade = data.computed_grade;
                    
                    hideSaveIndicator();
                    alert('All grades and attendance saved successfully!');
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                alert('Error saving data: ' + error.message);
                hideSaveIndicator();
            });
        }

        // Enhanced CSV export function
        async function exportToCSV() {
            if (studentData.length === 0) {
                alert('No student data to export');
                return;
            }
            
            try {
                // Get all individual items for both terms
                const [midtermQuizzes, midtermActivities, midtermAssignments, midtermRecitations, midtermExams, finalQuizzes, finalActivities, finalAssignments, finalRecitations, finalExams] = await Promise.all([
                    fetch(`items_api.php?action=get_items&course_id=${courseId}&term=midterm&type=quiz`).then(r => r.json()),
                    fetch(`items_api.php?action=get_items&course_id=${courseId}&term=midterm&type=activity`).then(r => r.json()),
                    fetch(`items_api.php?action=get_items&course_id=${courseId}&term=midterm&type=assignment`).then(r => r.json()),
                    fetch(`items_api.php?action=get_items&course_id=${courseId}&term=midterm&type=recitation`).then(r => r.json()),
                    fetch(`items_api.php?action=get_items&course_id=${courseId}&term=midterm&type=exam`).then(r => r.json()),
                    fetch(`items_api.php?action=get_items&course_id=${courseId}&term=final&type=quiz`).then(r => r.json()),
                    fetch(`items_api.php?action=get_items&course_id=${courseId}&term=final&type=activity`).then(r => r.json()),
                    fetch(`items_api.php?action=get_items&course_id=${courseId}&term=final&type=assignment`).then(r => r.json()),
                    fetch(`items_api.php?action=get_items&course_id=${courseId}&term=final&type=recitation`).then(r => r.json()),
                    fetch(`items_api.php?action=get_items&course_id=${courseId}&term=final&type=exam`).then(r => r.json())
                ]);
                
                // Build CSV header
                let csvContent = 'Student Number,Full Name,First Grading,Second Grading,Computed Grade';
                
                // Add midterm item headers
                if (midtermQuizzes.success) {
                    midtermQuizzes.items.forEach(item => {
                        csvContent += `,Midterm Quiz: ${item.name} (${item.max_score}pts)`;
                    });
                }
                if (midtermActivities.success) {
                    midtermActivities.items.forEach(item => {
                        csvContent += `,Midterm Activity: ${item.name} (${item.max_score}pts)`;
                    });
                }
                if (midtermAssignments.success) {
                    midtermAssignments.items.forEach(item => {
                        csvContent += `,Midterm Assignment: ${item.name} (${item.max_score}pts)`;
                    });
                }
                
                // Add final item headers
                if (finalQuizzes.success) {
                    finalQuizzes.items.forEach(item => {
                        csvContent += `,Final Quiz: ${item.name} (${item.max_score}pts)`;
                    });
                }
                if (finalActivities.success) {
                    finalActivities.items.forEach(item => {
                        csvContent += `,Final Activity: ${item.name} (${item.max_score}pts)`;
                    });
                }
                if (finalAssignments.success) {
                    finalAssignments.items.forEach(item => {
                        csvContent += `,Final Assignment: ${item.name} (${item.max_score}pts)`;
                    });
                }

                // Add midterm recitation and exam headers
                if (midtermRecitations.success) {
                    midtermRecitations.items.forEach(item => {
                        csvContent += `,Midterm Recitation: ${item.name} (${item.max_score}pts)`;
                    });
                }
                if (midtermExams.success) {
                    midtermExams.items.forEach(item => {
                        csvContent += `,Midterm Exam: ${item.name} (${item.max_score}pts)`;
                    });
                }

                // Add final recitation and exam headers
                if (finalRecitations.success) {
                    finalRecitations.items.forEach(item => {
                        csvContent += `,Final Recitation: ${item.name} (${item.max_score}pts)`;
                    });
                }
                if (finalExams.success) {
                    finalExams.items.forEach(item => {
                        csvContent += `,Final Exam: ${item.name} (${item.max_score}pts)`;
                    });
                }
                
                csvContent += ',Midterm Quiz Avg,Midterm Activity Avg,Midterm Assignment Avg,Midterm Recitation Avg,Midterm Exam Avg,Final Quiz Avg,Final Activity Avg,Final Assignment Avg,Final Recitation Avg,Final Exam Avg,Attendance Rate\n';
                
                // Add student data
                for (const student of studentData) {
                    let row = `${student.student_number},"${student.full_name}",${student.first_grade || ''},${student.second_grade || ''},${student.computed_grade || ''}`;
                    
                    // Get individual scores for this student
                    const [mQuizScores, mActivityScores, mAssignmentScores, mRecitationScores, mExamScores, fQuizScores, fActivityScores, fAssignmentScores, fRecitationScores, fExamScores] = await Promise.all([
                            fetch(`items_api.php?action=get_scores&course_id=${courseId}&term=midterm&type=quiz`).then(r => r.json()),
                            fetch(`items_api.php?action=get_scores&course_id=${courseId}&term=midterm&type=activity`).then(r => r.json()),
                            fetch(`items_api.php?action=get_scores&course_id=${courseId}&term=midterm&type=assignment`).then(r => r.json()),
                            fetch(`items_api.php?action=get_scores&course_id=${courseId}&term=midterm&type=recitation`).then(r => r.json()),
                            fetch(`items_api.php?action=get_scores&course_id=${courseId}&term=midterm&type=exam`).then(r => r.json()),
                            fetch(`items_api.php?action=get_scores&course_id=${courseId}&term=final&type=quiz`).then(r => r.json()),
                            fetch(`items_api.php?action=get_scores&course_id=${courseId}&term=final&type=activity`).then(r => r.json()),
                            fetch(`items_api.php?action=get_scores&course_id=${courseId}&term=final&type=assignment`).then(r => r.json()),
                            fetch(`items_api.php?action=get_scores&course_id=${courseId}&term=final&type=recitation`).then(r => r.json()),
                            fetch(`items_api.php?action=get_scores&course_id=${courseId}&term=final&type=exam`).then(r => r.json())
                        ]);
                    
                    // Add individual item scores
                    const addItemScores = (items, scores) => {
                        if (items.success && items.items) {
                            items.items.forEach(item => {
                                const score = scores.success ? (scores.scores[student.student_number]?.[item.id] || 0) : 0;
                                row += `,${score}`;
                            });
                        }
                    };
                    
                    addItemScores(midtermQuizzes, mQuizScores);
                    addItemScores(midtermActivities, mActivityScores);
                    addItemScores(midtermAssignments, mAssignmentScores);
                    addItemScores(finalQuizzes, fQuizScores);
                    addItemScores(finalActivities, fActivityScores);
                    addItemScores(finalAssignments, fAssignmentScores);
                    addItemScores(midtermRecitations, mRecitationScores);
                    addItemScores(midtermExams, mExamScores);
                    addItemScores(finalRecitations, fRecitationScores);
                    addItemScores(finalExams, fExamScores);
                    
                    // Calculate and add averages
                    const calculateAverage = (items, scores) => {
                        if (!items.success || !items.items || items.items.length === 0) return 0;
                        let total = 0, maxTotal = 0;
                        items.items.forEach(item => {
                            const score = scores.success ? (scores.scores[student.student_number]?.[item.id] || 0) : 0;
                            total += parseFloat(score);
                            maxTotal += parseFloat(item.max_score);
                        });
                        return maxTotal > 0 ? (total / maxTotal * 100) : 0;
                    };
                    
                    row += `,${calculateAverage(midtermQuizzes, mQuizScores).toFixed(2)}`;
                    row += `,${calculateAverage(midtermActivities, mActivityScores).toFixed(2)}`;
                    row += `,${calculateAverage(midtermAssignments, mAssignmentScores).toFixed(2)}`;
                    row += `,${calculateAverage(finalQuizzes, fQuizScores).toFixed(2)}`;
                    row += `,${calculateAverage(finalActivities, fActivityScores).toFixed(2)}`;
                    row += `,${calculateAverage(finalAssignments, fAssignmentScores).toFixed(2)}`;
                    row += `,${calculateAverage(midtermRecitations, mRecitationScores).toFixed(2)}`;
                    row += `,${calculateAverage(midtermExams, mExamScores).toFixed(2)}`;
                    row += `,${calculateAverage(finalRecitations, fRecitationScores).toFixed(2)}`;
                    row += `,${calculateAverage(finalExams, fExamScores).toFixed(2)}`;

                    // Add attendance rate
                    try {
                        const attendanceResponse = await fetch(`detailed_grades_api.php?course_id=${courseId}&student_number=${student.student_number}`);
                        const attendanceData = await attendanceResponse.json();
                        if (attendanceData.success && attendanceData.attendance.length > 0) {
                            const presentCount = attendanceData.attendance.filter(a => a.status === 'present').length;
                            const attendanceRate = ((presentCount / attendanceData.attendance.length) * 100).toFixed(2);
                            row += `,${attendanceRate}%`;
                        } else {
                            row += `,N/A`;
                        }
                    } catch {
                        row += `,N/A`;
                    }
                    
                    csvContent += row + '\n';
                }
                
                // Create and download CSV
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.setAttribute('href', url);
                link.setAttribute('download', `detailed_grading_sheet_${new Date().toISOString().split('T')[0]}.csv`);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
            } catch (error) {
                alert('Error exporting CSV: ' + error.message);
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

        function logout() {
            window.location.href = 'auth_handler.php?action=logout';
        }

        // Add a function to apply the grade computation method
        function applyGradeComputationMethod(rawScore, maxScore) {
            if (courseGradeComputationMethod === 'base_0') {
                return rawScore;
            } else { // base_50
                // Map the score from 0-100 to 50-100 range
                return 50 + (rawScore / 2);
            }
        }

        // Excel Manager Variables
        let excelCurrentType = '';
        let excelCurrentTerm = '';
        let excelItems = [];
        let excelStudents = [];
        let excelScores = {};
        let isDataModified = false;
        
        // Open Excel Manager
        function openExcelManager(itemType, term) {
            if (!currentStudent) {
                alert('Please select a student first');
                return;
            }
            
            excelCurrentType = itemType;
            excelCurrentTerm = term;
            
            const modal = document.getElementById('excelManagerModal');
            const title = document.getElementById('excelManagerTitle');
            title.textContent = `Manage ${itemType.charAt(0).toUpperCase() + itemType.slice(1)}s - ${term.charAt(0).toUpperCase() + term.slice(1)} Term`;
            
            loadExcelData();
            modal.style.display = 'block';
        }
        
        // Close Excel Manager
        function closeExcelManager() {
            if (isDataModified) {
                if (!confirm('You have unsaved changes. Are you sure you want to close?')) {
                    return;
                }
            }
            
            const modal = document.getElementById('excelManagerModal');
            modal.style.display = 'none';
            isDataModified = false;
        }
        
        // Load Excel Data
        async function loadExcelData() {
            try {
                const response = await fetch(`items_api.php?action=get_all_data&course_id=${courseId}&term=${excelCurrentTerm}&type=${excelCurrentType}`);
                const data = await response.json();
                
                if (data.success) {
                    excelItems = data.items;
                    excelStudents = data.students;
                    excelScores = data.scores;
                    
                    renderExcelTable();
                    updateExcelCounts();
                } else {
                    alert('Error loading data: ' + data.message);
                }
            } catch (error) {
                alert('Error loading data: ' + error.message);
            }
        }
        
        // Render Excel Table
        function renderExcelTable() {
            const table = document.getElementById('excelTable');
            const header = document.getElementById('excelTableHeader');
            const subHeader = document.getElementById('excelTableSubHeader');
            const tbody = document.getElementById('excelTableBody');
            
            // Clear existing content
            header.innerHTML = '<th class="fixed-column student-column">Student Name</th>';
            subHeader.innerHTML = '<th class="fixed-column"></th>';
            tbody.innerHTML = '';
            
            // Add item columns
            excelItems.forEach((item, index) => {
                const th = document.createElement('th');
                th.className = 'item-header';
                th.innerHTML = `
                    <input type="text" value="${item.name}" onchange="updateItemName(${item.id}, this.value)" title="Item Name">
                    <button class="delete-btn" onclick="deleteExcelItem(${item.id})" title="Delete Item">×</button>
                `;
                header.appendChild(th);
                
                const subTh = document.createElement('th');
                subTh.className = 'item-header';
                subTh.innerHTML = `
                    <input type="number" value="${item.max_score}" onchange="updateItemMaxScore(${item.id}, this.value)" title="Max Score" style="width: 50px;">
                    <input type="date" value="${item.date_given || ''}" onchange="updateItemDate(${item.id}, this.value)" title="Date" style="width: 100px; font-size: 10px;">
                `;
                subHeader.appendChild(subTh);
            });
            
            // Add "Add Item" column
            header.innerHTML += '<th class="add-column" onclick="addNewItem()">+ Add Item</th>';
            subHeader.innerHTML += '<th class="add-column"></th>';
            
            // Add average column
            header.innerHTML += '<th class="average-column">Average</th>';
            subHeader.innerHTML += '<th class="average-column">%</th>';
            
            // Add student rows
            excelStudents.forEach(student => {
                const row = document.createElement('tr');
                
                // Student name column
                const nameCell = document.createElement('td');
                nameCell.className = 'fixed-column student-column';
                nameCell.textContent = student.full_name;
                row.appendChild(nameCell);
                
                // Score columns
                let totalScore = 0;
                let totalMaxScore = 0;
                
                excelItems.forEach(item => {
                    const scoreCell = document.createElement('td');
                    const currentScore = excelScores[student.student_number]?.[item.id] || 0;
                    
                    scoreCell.innerHTML = `<input type="number" class="score-input" min="0" max="${item.max_score}" step="0.01" value="${currentScore}" onchange="updateExcelScore('${student.student_number}', ${item.id}, this.value, ${item.max_score})">`;
                    row.appendChild(scoreCell);
                    
                    totalScore += parseFloat(currentScore);
                    totalMaxScore += parseFloat(item.max_score);
                });
                
                // Add item column (empty)
                const addCell = document.createElement('td');
                addCell.className = 'add-column';
                row.appendChild(addCell);
                
                // Average column
                const avgCell = document.createElement('td');
                avgCell.className = 'average-cell';
                const average = totalMaxScore > 0 ? (totalScore / totalMaxScore * 100) : 0;
                avgCell.textContent = formatScore(average) + '%';
                row.appendChild(avgCell);
                
                tbody.appendChild(row);
            });
        }
        
        // Add New Item
        async function addNewItem() {
            const itemName = prompt('Enter item name:');
            if (!itemName) return;
            
            const maxScore = prompt('Enter max score:', '100');
            if (!maxScore || isNaN(maxScore)) return;
            
            const date = prompt('Enter date (YYYY-MM-DD):');
            
            try {
                const response = await fetch('items_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'add_item',
                        course_id: courseId,
                        term: excelCurrentTerm,
                        type: excelCurrentType,
                        name: itemName,
                        max_score: maxScore,
                        date: date
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    await loadExcelData();
                    isDataModified = true;
                } else {
                    alert('Error adding item: ' + data.message);
                }
            } catch (error) {
                alert('Error adding item: ' + error.message);
            }
        }
        
        // Update Excel Score
        function updateExcelScore(studentNumber, itemId, score, maxScore) {
            const numScore = parseFloat(score) || 0;
            
            if (numScore > maxScore) {
                alert(`Score cannot exceed ${maxScore}`);
                return;
            }
            
            if (!excelScores[studentNumber]) {
                excelScores[studentNumber] = {};
            }
            
            excelScores[studentNumber][itemId] = numScore;
            isDataModified = true;
            
            // Recalculate averages
            renderExcelTable();
            
            // Update main grade if this is the current student
            if (studentNumber === currentStudent.student_number) {
                updateMainGradeFromExcel();
            }
        }
        
        // Update Item Name
        async function updateItemName(itemId, newName) {
            if (!newName.trim()) return;
            
            try {
                const response = await fetch('items_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'update_item',
                        item_id: itemId,
                        type: excelCurrentType,
                        name: newName,
                        max_score: excelItems.find(item => item.id == itemId)?.max_score || 100,
                        date: excelItems.find(item => item.id == itemId)?.date_given
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Update local data
                    const item = excelItems.find(item => item.id == itemId);
                    if (item) item.name = newName;
                    isDataModified = true;
                } else {
                    alert('Error updating item: ' + data.message);
                    await loadExcelData();
                }
            } catch (error) {
                alert('Error updating item: ' + error.message);
            }
        }
        
        // Update Item Max Score
        async function updateItemMaxScore(itemId, newMaxScore) {
            const maxScore = parseFloat(newMaxScore);
            if (isNaN(maxScore) || maxScore <= 0) return;
            
            try {
                const item = excelItems.find(item => item.id == itemId);
                const response = await fetch('items_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'update_item',
                        item_id: itemId,
                        type: excelCurrentType,
                        name: item?.name || '',
                        max_score: maxScore,
                        date: item?.date_given
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Update local data
                    if (item) item.max_score = maxScore;
                    renderExcelTable();
                    isDataModified = true;
                } else {
                    alert('Error updating item: ' + data.message);
                    await loadExcelData();
                }
            } catch (error) {
                alert('Error updating item: ' + error.message);
            }
        }
        
        // Update Item Date
        async function updateItemDate(itemId, newDate) {
            try {
                const item = excelItems.find(item => item.id == itemId);
                const response = await fetch('items_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'update_item',
                        item_id: itemId,
                        type: excelCurrentType,
                        name: item?.name || '',
                        max_score: item?.max_score || 100,
                        date: newDate
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Update local data
                    if (item) item.date_given = newDate;
                    isDataModified = true;
                } else {
                    alert('Error updating item: ' + data.message);
                    await loadExcelData();
                }
            } catch (error) {
                alert('Error updating item: ' + error.message);
            }
        }
        
        // Delete Excel Item
        async function deleteExcelItem(itemId) {
            if (!confirm('Are you sure you want to delete this item? All scores will be lost.')) {
                return;
            }
            
            try {
                const response = await fetch('items_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'delete_item',
                        item_id: itemId,
                        type: excelCurrentType
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    await loadExcelData();
                    updateMainGradeFromExcel();
                    isDataModified = true;
                } else {
                    alert('Error deleting item: ' + data.message);
                }
            } catch (error) {
                alert('Error deleting item: ' + error.message);
            }
        }
        
        // Save All Data
        async function saveAllData() {
            try {
                const response = await fetch('items_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'save_scores',
                        course_id: courseId,
                        term: excelCurrentTerm,
                        type: excelCurrentType,
                        scores: excelScores
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('All data saved successfully!');
                    isDataModified = false;
                    updateMainGradeFromExcel();
                } else {
                    alert('Error saving data: ' + data.message);
                }
            } catch (error) {
                alert('Error saving data: ' + error.message);
            }
        }
        
        // Refresh Data
        async function refreshData() {
            if (isDataModified) {
                if (!confirm('You have unsaved changes. Are you sure you want to refresh?')) {
                    return;
                }
            }
            
            await loadExcelData();
            isDataModified = false;
        }
        
        // Update Excel Counts
        function updateExcelCounts() {
            document.getElementById('itemCount').textContent = excelItems.length;
            document.getElementById('studentCount').textContent = excelStudents.length;
        }
        
        // Update Main Grade from Excel
        function updateMainGradeFromExcel() {
            if (!currentStudent || excelItems.length === 0) return;
            
            let totalScore = 0;
            let totalMaxScore = 0;
            
            excelItems.forEach(item => {
                const score = excelScores[currentStudent.student_number]?.[item.id] || 0;
                totalScore += parseFloat(score);
                totalMaxScore += parseFloat(item.max_score);
            });
            
            const average = totalMaxScore > 0 ? (totalScore / totalMaxScore * 100) : 0;
            
            // Update the appropriate grade input
            const gradeInputId = `${excelCurrentTerm}${excelCurrentType.charAt(0).toUpperCase() + excelCurrentType.slice(1)}Score`;
            const gradeInput = document.getElementById(gradeInputId);
            if (gradeInput) {
                // Use formatScore for display but keep precision for calculations
                gradeInput.value = formatScore(average);
                
                // Trigger grade calculation
                if (excelCurrentTerm === 'midterm') {
                    calculateMidtermGrade();
                } else {
                    calculateFinalGrade();
                }
            }
        }
        
        // Function to open item manager (placeholder for now)
        function openItemManager(component, term) {
            alert(`Opening ${component} manager for ${term} term. This feature will be implemented in the next update.`);
        }

        // Grade Weights Modal Functions
        function openGradeWeightsModal() {
            updateGradeWeightsForm();
            document.getElementById('gradeWeightsModal').style.display = 'block';
        }
        
        function closeGradeWeightsModal() {
            document.getElementById('gradeWeightsModal').style.display = 'none';
        }
        
        function updateGradeWeightsForm() {
            // Set current values in the form
            document.getElementById('attendanceWeight').value = gradeWeights.attendance;
            document.getElementById('quizWeight').value = gradeWeights.quiz;
            document.getElementById('activityWeight').value = gradeWeights.activity;
            document.getElementById('assignmentWeight').value = gradeWeights.assignment;
            document.getElementById('recitationWeight').value = gradeWeights.recitation;
            document.getElementById('examWeight').value = gradeWeights.exam;

            // Trigger the update of all percentage labels
            document.getElementById('attendanceWeight').dispatchEvent(new Event('input'));
            document.getElementById('quizWeight').dispatchEvent(new Event('input'));
            document.getElementById('activityWeight').dispatchEvent(new Event('input'));
            document.getElementById('assignmentWeight').dispatchEvent(new Event('input'));
            document.getElementById('recitationWeight').dispatchEvent(new Event('input'));
            document.getElementById('examWeight').dispatchEvent(new Event('input'));

            // Set computation method and passing grade
            document.getElementById('gradeComputationMethod').value = courseGradeComputationMethod;
            document.getElementById('passingGradeInput').value = coursePassingGrade;
        }
        
        function saveGradeWeights(event) {
            event.preventDefault();
            
            // Get values from form
            const attendance = parseFloat(document.getElementById('attendanceWeight').value);
            const quiz = parseFloat(document.getElementById('quizWeight').value);
            const activity = parseFloat(document.getElementById('activityWeight').value);
            const assignment = parseFloat(document.getElementById('assignmentWeight').value);
            const recitation = parseFloat(document.getElementById('recitationWeight').value);
            const exam = parseFloat(document.getElementById('examWeight').value);
            
            // Validate total is 1.0
            const total = attendance + quiz + activity + assignment + recitation + exam;
            if (Math.abs(total - 1.0) > 0.01) {
                alert('The sum of all weights must equal 100%. Current total: ' + (total * 100).toFixed(0) + '%');
                return;
            }
            
            // Update grade weights
            gradeWeights = {
                attendance,
                quiz,
                activity,
                assignment,
                recitation,
                exam
            };
            
            // Update computation method and passing grade
            courseGradeComputationMethod = document.getElementById('gradeComputationMethod').value;
            coursePassingGrade = parseFloat(document.getElementById('passingGradeInput').value);
            
            // Save to database
            fetch('grade_weights_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    course_id: courseId,
                    grade_weights: gradeWeights,
                    grade_computation_method: courseGradeComputationMethod,
                    passing_grade: coursePassingGrade
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Grade weights and settings saved successfully!');
                    closeGradeWeightsModal();
                    
                    // Recalculate grades with new weights
                    if (currentStudent) {
                        calculateMidtermGrade();
                        calculateFinalGrade();
                        calculateSemestralGrade();
                        
                        // Save the recalculated grades to the database
                        saveAllGrades();
                    }
                } else {
                    alert('Error saving grade weights: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error saving grade weights: ' + error.message);
            });
        }
        
        // Add event listeners for weight inputs
        function setupWeightInputListeners() {
            const weightInputs = ['attendanceWeight', 'quizWeight', 'activityWeight', 'assignmentWeight', 'recitationWeight', 'examWeight'];
            
            weightInputs.forEach(inputId => {
                const input = document.getElementById(inputId);
                if (input) {
                    input.addEventListener('input', updateWeightPercentages);
                }
            });
        }
        
        // Function to update weight percentages when inputs change
        function updateWeightPercentages() {
            // Update the percentage label next to the input that changed
            const value = parseFloat(this.value) * 100;
            const percentageLabel = this.nextElementSibling;
            if (percentageLabel && percentageLabel.classList.contains('weight-percentage')) {
                percentageLabel.textContent = value.toFixed(0) + '%';
            }
            
            // Update total class participation weight
            const attendance = parseFloat(document.getElementById('attendanceWeight').value) || 0;
            const quiz = parseFloat(document.getElementById('quizWeight').value) || 0;
            const activity = parseFloat(document.getElementById('activityWeight').value) || 0;
            const assignment = parseFloat(document.getElementById('assignmentWeight').value) || 0;
            const recitation = parseFloat(document.getElementById('recitationWeight').value) || 0;
            
            const totalClassParticipation = (attendance + quiz + activity + assignment + recitation) * 100;
            document.getElementById('totalClassParticipationWeight').textContent = totalClassParticipation.toFixed(0) + '%';
            
            // Recalculate grades with the new weights if a student is selected
            if (currentStudent) {
                // Update gradeWeights object with current values
                gradeWeights.attendance = attendance;
                gradeWeights.quiz = quiz;
                gradeWeights.activity = activity;
                gradeWeights.assignment = assignment;
                gradeWeights.recitation = recitation;
                gradeWeights.exam = parseFloat(document.getElementById('examWeight').value) || 0;
                
                // Recalculate grades
                calculateMidtermGrade();
                calculateFinalGrade();
            }
        }
        
        // Function to setup grade input listeners
        function setupGradeInputListeners() {
            // Midterm grade inputs
            document.getElementById('midtermQuizScore').addEventListener('input', calculateMidtermGrade);
            document.getElementById('midtermActivityScore').addEventListener('input', calculateMidtermGrade);
            document.getElementById('midtermAssignmentScore').addEventListener('input', calculateMidtermGrade);
            document.getElementById('midtermRecitationScore').addEventListener('input', calculateMidtermGrade);
            document.getElementById('midtermExamScore').addEventListener('input', calculateMidtermGrade);
            
            // Final grade inputs
            document.getElementById('finalQuizScore').addEventListener('input', calculateFinalGrade);
            document.getElementById('finalActivityScore').addEventListener('input', calculateFinalGrade);
            document.getElementById('finalAssignmentScore').addEventListener('input', calculateFinalGrade);
            document.getElementById('finalRecitationScore').addEventListener('input', calculateFinalGrade);
            document.getElementById('finalExamScore').addEventListener('input', calculateFinalGrade);
        }

        // Function to setup computation method listener
        function setupComputationMethodListener() {
            const methodSelect = document.getElementById('gradeComputationMethod');
            const passingGradeInput = document.getElementById('passingGradeInput');
            
            methodSelect.addEventListener('change', function() {
                // Update the computation method
                courseGradeComputationMethod = this.value;
                
                // Recalculate grades if a student is selected
                if (currentStudent) {
                    calculateMidtermGrade();
                    calculateFinalGrade();
                    
                    // Save the updated grades to the database
                    saveAllGrades();
                }
            });
            
            passingGradeInput.addEventListener('change', function() {
                // Update the passing grade
                coursePassingGrade = parseFloat(this.value);
                
                // Recalculate grades if a student is selected
                if (currentStudent) {
                    calculateMidtermGrade();
                    calculateFinalGrade();
                    calculateSemestralGrade();
                    
                    // Save the updated grades to the database
                    saveAllGrades();
                }
            });
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadCourseInfo();
            setupWeightInputListeners();
            setupGradeInputListeners();
            setupComputationMethodListener();
            
            // Hide all modals on page load
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                modal.style.display = 'none';
            });
            
            const attendanceForm = document.getElementById('attendanceForm');
            const modal = document.getElementById('attendanceModal');
            const gradeWeightsModal = document.getElementById('gradeWeightsModal');
            
            // Close modal when clicking outside
            window.onclick = function(event) {
                if (event.target === modal) {
                    closeAttendanceModal();
                }
                if (event.target === gradeWeightsModal) {
                    closeGradeWeightsModal();
                }
            }
            
            // Handle form submission
            attendanceForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const meetingNum = parseInt(document.getElementById('meetingNumber').value);
                const date = document.getElementById('meetingDate').value;
                const status = document.getElementById('attendanceStatus').value;
                const errorMessage = document.getElementById('meetingError');
                
                // Validate meeting number
                if (!validateMeetingNumber(meetingNum)) {
                    errorMessage.style.display = 'block';
                    return;
                }
                
                errorMessage.style.display = 'none';
                
                // Add the record
                if (addAttendanceRecord(meetingNum, date, status)) {
                    closeAttendanceModal();
                    
                    // Show success message
                    const successMsg = document.createElement('div');
                    successMsg.textContent = 'Attendance record added successfully!';
                    successMsg.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #4CAF50; color: white; padding: 15px; border-radius: 5px; z-index: 1001;';
                    document.body.appendChild(successMsg);
                    
                    setTimeout(() => {
                        document.body.removeChild(successMsg);
                    }, 3000);
                }
            });
            
            // Real-time validation for meeting number
            document.getElementById('meetingNumber').addEventListener('input', function() {
                const meetingNum = parseInt(this.value);
                const errorMessage = document.getElementById('meetingError');
                
                if (meetingNum && !validateMeetingNumber(meetingNum)) {
                    errorMessage.style.display = 'block';
                } else {
                    errorMessage.style.display = 'none';
                }
            });
            // Add item form handler
            const addItemForm = document.getElementById('addItemForm');
            if (addItemForm) {
                addItemForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const itemName = document.getElementById('itemName').value;
                    const maxScore = document.getElementById('itemMaxScore').value;
                    const itemDate = document.getElementById('itemDate').value;
                    
                    try {
                        const response = await fetch('items_api.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                action: 'add_item',
                                course_id: courseId,
                                term: currentTerm,
                                type: currentItemType,
                                name: itemName,
                                max_score: maxScore,
                                date: itemDate
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            // Reload items and scores
                            await loadItemsAndScores();
                            
                            // Reset form
                            addItemForm.reset();
                            document.getElementById('itemMaxScore').value = '100';
                            
                            alert('Item added successfully!');
                        } else {
                            alert('Error adding item: ' + data.message);
                        }
                    } catch (error) {
                        alert('Error adding item: ' + error.message);
                    }
                });
            }
        });

        // Function to delete item
        async function deleteItem(itemId) {
            if (!confirm('Are you sure you want to delete this item? All associated scores will be lost.')) {
                return;
            }
            
            try {
                const response = await fetch('items_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'delete_item',
                        item_id: itemId
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    await loadItemsAndScores();
                    updateMainGradeFromItems();
                    alert('Item deleted successfully!');
                } else {
                    alert('Error deleting item: ' + data.message);
                }
            } catch (error) {
                alert('Error deleting item: ' + error.message);
            }
        }
        
        // Function to save all item scores
        async function saveItemScores() {
            try {
                const response = await fetch('items_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'save_scores',
                        course_id: courseId,
                        term: currentTerm,
                        type: currentItemType,
                        scores: currentStudentScores
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('All scores saved successfully!');
                    closeItemManager();
                } else {
                    alert('Error saving scores: ' + data.message);
                }
            } catch (error) {
                alert('Error saving scores: ' + error.message);
            }
        }
        
        // Function to update summary
        function updateSummary() {
            document.getElementById('totalItems').textContent = currentItems.length;
            
            if (currentStudent && currentItems.length > 0) {
                let totalScore = 0;
                let totalMaxScore = 0;
                
                currentItems.forEach(item => {
                    const score = currentStudentScores[currentStudent.student_number]?.[item.id] || 0;
                    totalScore += parseFloat(score);
                    totalMaxScore += parseFloat(item.max_score);
                });
                
                const average = totalMaxScore > 0 ? (totalScore / totalMaxScore * 100) : 0;
                document.getElementById('currentAverage').textContent = average.toFixed(2) + '%';
            } else {
                document.getElementById('currentAverage').textContent = '0.00%';
            }
        }
    </script>
</body>
</html>