// Debounce variables to prevent premature saving
let debounceTimers = {};
let gradeData = {};

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
    
    // Save immediately when user leaves the field
    saveGrade(input);
}

// Save grade function with validation
function saveGrade(input) {
    const studentId = input.dataset.student;
    const gradeType = input.dataset.gradeType;
    const value = parseFloat(input.value);
    
    // Validate grade range (1.00 to 5.00)
    if (input.value && (isNaN(value) || value < 1.00 || value > 5.00)) {
        showSaveIndicator('Invalid grade range (1.00 - 5.00)', true);
        input.classList.add('error');
        return;
    }
    
    // Show saving state
    input.classList.add('saving');
    showSaveIndicator('Saving grade...', false);
    
    // Simulate API call with setTimeout
    setTimeout(() => {
        try {
            // Store the grade data
            if (!gradeData[studentId]) {
                gradeData[studentId] = {};
            }
            gradeData[studentId][gradeType] = value || null;
            
            // Show success state
            input.classList.remove('saving');
            input.classList.add('saved');
            showSaveIndicator('Grade saved successfully!', false);
            
            // Remove saved state after 2 seconds
            setTimeout(() => {
                input.classList.remove('saved');
            }, 2000);
            
            // Log the saved data (for debugging)
            console.log('Grade saved:', {
                student: studentId,
                gradeType: gradeType,
                value: value,
                allGrades: gradeData
            });
            
        } catch (error) {
            // Handle save error
            input.classList.remove('saving');
            input.classList.add('error');
            showSaveIndicator('Error saving grade', true);
            console.error('Save error:', error);
        }
    }, 800); // Simulate network delay
}

// Show save indicator
function showSaveIndicator(message, isError = false) {
    const indicator = document.getElementById('saveIndicator');
    indicator.textContent = message;
    indicator.classList.toggle('error', isError);
    indicator.classList.add('show');
    
    // Hide after 3 seconds
    setTimeout(() => {
        indicator.classList.remove('show');
    }, 3000);
}

// Format number input to 2 decimal places when user finishes typing
document.addEventListener('blur', function(event) {
    if (event.target.classList.contains('grade-input') && event.target.value) {
        const value = parseFloat(event.target.value);
        if (!isNaN(value)) {
            event.target.value = value.toFixed(2);
        }
    }
}, true);

// Prevent invalid characters in grade inputs
document.addEventListener('keydown', function(event) {
    if (event.target.classList.contains('grade-input')) {
        // Allow: backspace, delete, tab, escape, enter, decimal point
        if ([46, 8, 9, 27, 13, 110, 190].indexOf(event.keyCode) !== -1 ||
            // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
            (event.keyCode === 65 && event.ctrlKey === true) ||
            (event.keyCode === 67 && event.ctrlKey === true) ||
            (event.keyCode === 86 && event.ctrlKey === true) ||
            (event.keyCode === 88 && event.ctrlKey === true) ||
            // Allow: home, end, left, right, down, up
            (event.keyCode >= 35 && event.keyCode <= 40)) {
            return;
        }
        // Ensure that it is a number and stop the keypress
        if ((event.shiftKey || (event.keyCode < 48 || event.keyCode > 57)) && (event.keyCode < 96 || event.keyCode > 105)) {
            event.preventDefault();
        }
    }
});