const signUpButton = document.getElementById('signUp');
const signInButton = document.getElementById('signIn');
const container = document.getElementById('container');

signUpButton.addEventListener('click', () => {
    container.classList.add('right-panel-active');
});

signInButton.addEventListener('click', () => {
    container.classList.remove('right-panel-active');
});

// Handle form submissions
document.addEventListener('DOMContentLoaded', function() {
    // Sign up form
    const signUpForm = document.querySelector('.sign-up-container form');
    signUpForm.addEventListener('submit', handleSignUp);
    
    // Sign in form
    const signInForm = document.querySelector('.sign-in-container form');
    signInForm.addEventListener('submit', handleSignIn);
});

async function handleSignUp(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    
    // Get form values
    const fullName = form.querySelector('input[type="text"]').value.trim();
    const email = form.querySelector('input[type="email"]').value.trim();
    const facultyId = form.querySelectorAll('input[type="text"]')[1].value.trim();
    const password = form.querySelectorAll('input[type="password"]')[0].value;
    const confirmPassword = form.querySelectorAll('input[type="password"]')[1].value;
    
    // Basic validation
    if (!fullName || !email || !facultyId || !password || !confirmPassword) {
        showMessage('All fields are required', 'error');
        return;
    }
    
    if (password !== confirmPassword) {
        showMessage('Passwords do not match', 'error');
        return;
    }
    
    // Show loading
    const submitBtn = form.querySelector('.form-btn');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Signing Up...';
    submitBtn.disabled = true;
    
    try {
        const response = await fetch('auth_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'register',
                fullName: fullName,
                email: email,
                facultyId: facultyId,
                password: password,
                confirmPassword: confirmPassword
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage('Registration successful! You can now login.', 'success');
            form.reset();
            // Switch to login form
            setTimeout(() => {
                container.classList.remove('right-panel-active');
            }, 1500);
        } else {
            showMessage(data.message || 'Registration failed', 'error');
        }
        
    } catch (error) {
        showMessage('Network error: ' + error.message, 'error');
    } finally {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
}

async function handleSignIn(e) {
    e.preventDefault();
    
    const form = e.target;
    
    // Get form values
    const username = form.querySelector('input[type="text"]').value.trim();
    const password = form.querySelector('input[type="password"]').value;
    
    // Basic validation
    if (!username || !password) {
        showMessage('Username and password are required', 'error');
        return;
    }
    
    // Show loading
    const submitBtn = form.querySelector('.form-btn');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Logging In...';
    submitBtn.disabled = true;
    
    try {
        const response = await fetch('auth_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'login',
                username: username,
                password: password
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage('Login successful! Redirecting...', 'success');
            // Redirect to dashboard
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 1000);
        } else {
            showMessage(data.message || 'Login failed', 'error');
        }
        
    } catch (error) {
        showMessage('Network error: ' + error.message, 'error');
    } finally {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
}

function showMessage(message, type) {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.message');
    existingMessages.forEach(msg => msg.remove());
    
    // Create message element
    const messageDiv = document.createElement('div');
    messageDiv.className = `message message-${type}`;
    messageDiv.textContent = message;
    
    // Style the message
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: bold;
        z-index: 10000;
        max-width: 400px;
        word-wrap: break-word;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
        transform: translateX(100%);
        background-color: ${type === 'success' ? '#28a745' : '#dc3545'};
    `;
    
    document.body.appendChild(messageDiv);
    
    // Animate in
    setTimeout(() => {
        messageDiv.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after 5 seconds
    setTimeout(() => {
        messageDiv.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.parentNode.removeChild(messageDiv);
            }
        }, 300);
    }, 5000);
}