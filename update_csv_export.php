<?php
require_once 'config.php';

try {
    // Path to the review_grading_sheet.php file
    $filePath = __DIR__ . '/review_grading_sheet.php';
    
    // Check if the file exists
    if (!file_exists($filePath)) {
        throw new Exception("File review_grading_sheet.php not found");
    }
    
    // Read the current content of the file
    $currentContent = file_get_contents($filePath);
    if ($currentContent === false) {
        throw new Exception("Unable to read file review_grading_sheet.php");
    }
    
    // Create the message to add - escape $ with \ in JavaScript template literals
    $message = "<!-- 
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

csvContent += ',Midterm Quiz Avg,Midterm Activity Avg,Midterm Assignment Avg,Midterm Recitation Avg,Midterm Exam Avg,Final Quiz Avg,Final Activity Avg,Final Assignment Avg,Final Recitation Avg,Final Exam Avg,Attendance Rate\n';

Also update the Promise.all to include recitation and exam scores:

const [mQuizScores, mActivityScores, mAssignmentScores, mRecitationScores, mExamScores, fQuizScores, fActivityScores, fAssignmentScores, fRecitationScores, fExamScores] = await Promise.all([
    fetch(`items_api.php?action=get_scores&course_id=\${courseId}&term=midterm&type=quiz`).then(r => r.json()),
    fetch(`items_api.php?action=get_scores&course_id=\${courseId}&term=midterm&type=activity`).then(r => r.json()),
    fetch(`items_api.php?action=get_scores&course_id=\${courseId}&term=midterm&type=assignment`).then(r => r.json()),
    fetch(`items_api.php?action=get_scores&course_id=\${courseId}&term=midterm&type=recitation`).then(r => r.json()),
    fetch(`items_api.php?action=get_scores&course_id=\${courseId}&term=midterm&type=exam`).then(r => r.json()),
    fetch(`items_api.php?action=get_scores&course_id=\${courseId}&term=final&type=quiz`).then(r => r.json()),
    fetch(`items_api.php?action=get_scores&course_id=\${courseId}&term=final&type=activity`).then(r => r.json()),
    fetch(`items_api.php?action=get_scores&course_id=\${courseId}&term=final&type=assignment`).then(r => r.json()),
    fetch(`items_api.php?action=get_scores&course_id=\${courseId}&term=final&type=recitation`).then(r => r.json()),
    fetch(`items_api.php?action=get_scores&course_id=\${courseId}&term=final&type=exam`).then(r => r.json())
]);

And add the item scores for recitation and exam:

addItemScores(midtermRecitations, mRecitationScores);
addItemScores(midtermExams, mExamScores);
addItemScores(finalRecitations, fRecitationScores);
addItemScores(finalExams, fExamScores);

Finally, add the averages for recitation and exam:

row += ',' + calculateAverage(midtermRecitations, mRecitationScores).toFixed(2);
row += ',' + calculateAverage(midtermExams, mExamScores).toFixed(2);
row += ',' + calculateAverage(finalRecitations, fRecitationScores).toFixed(2);
row += ',' + calculateAverage(finalExams, fExamScores).toFixed(2);
-->";
    
    // Find a good position to insert the message (before the closing PHP tag)
    $closingTagPos = strrpos($currentContent, '?>');
    if ($closingTagPos === false) {
        // If no closing tag, append to the end of the file
        $newContent = $currentContent . "\n" . $message;
    } else {
        // Insert before the closing tag
        $newContent = substr($currentContent, 0, $closingTagPos) . "\n" . $message . "\n" . substr($currentContent, $closingTagPos);
    }
    
    // Write the updated content back to the file
    if (file_put_contents($filePath, $newContent) === false) {
        throw new Exception("Unable to write to file review_grading_sheet.php");
    }
    
    echo "Successfully updated review_grading_sheet.php with CSV export functionality instructions.";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>