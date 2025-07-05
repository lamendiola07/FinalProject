// Add this at the beginning of the file
console.log('Script loaded successfully');
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
    if (signUpForm) {
        signUpForm.addEventListener('submit', handleSignUp);
    }
    
    // Sign in form
    const signInForm = document.querySelector('.sign-in-container form');
    if (signInForm) {
        signInForm.addEventListener('submit', handleSignIn);
    }
    
    // Forgot password link
    const forgotPasswordLink = document.getElementById('forgotPasswordLink');
    if (forgotPasswordLink) {
        forgotPasswordLink.addEventListener('click', handleForgotPassword);
    }
});

// Add this new function to handle forgot password
function handleForgotPassword(e) {
    e.preventDefault();
    
    // Create modal for email input
    const modalHTML = `
        <div id="forgotPasswordModal" style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        ">
            <div style="
                background: linear-gradient(to right, #f8f9fa, #e9ecef);
                padding: 30px;
                border-radius: 10px;
                width: 400px;
                max-width: 90%;
                box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
            ">
                <h3 style="margin-top: 0;">Forgot Password</h3>
                <p>Enter your email address to reset your password.</p>
                <form id="forgotPasswordForm">
                    <div class="input-group">
                        <input type="email" id="resetEmail" required>
                        <label>Email Address</label>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                        <button type="button" id="cancelResetBtn" class="form-btn" style="background-color: #ccc;">Cancel</button>
                        <button type="submit" class="form-btn">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Get modal elements
    const modal = document.getElementById('forgotPasswordModal');
    const form = document.getElementById('forgotPasswordForm');
    const cancelBtn = document.getElementById('cancelResetBtn');
    
    // Handle cancel button
    cancelBtn.addEventListener('click', () => {
        document.body.removeChild(modal);
    });
    
    // Handle form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const email = document.getElementById('resetEmail').value.trim();
        
        if (!email) {
            showMessage('Email is required', 'error');
            return;
        }
        
        // Show loading
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Processing...';
        submitBtn.disabled = true;
        
        try {
            const response = await fetch('forgot_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email: email })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Remove the email input modal
                document.body.removeChild(modal);
                
                // Remove any existing reset windows
                const existingResetWindows = document.querySelectorAll('.reset-password-modal');
                existingResetWindows.forEach(window => document.body.removeChild(window));
                
                // Create a notification modal
                const resetModal = document.createElement('div');
                resetModal.className = 'reset-password-modal';
                resetModal.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background-color: rgba(0, 0, 0, 0.5);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 2000;
                `;
                
                // Create the modal content
                resetModal.innerHTML = `
                    <div style="
                        background: linear-gradient(to right, #f8f9fa, #e9ecef);
                        border-radius: 10px;
                        width: 400px;
                        max-width: 90%;
                        overflow: hidden;
                        box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
                    ">
                        <div style="
                            background: linear-gradient(to right, #700000, #9a0000);
                            color: white;
                            padding: 15px 20px;
                            font-weight: bold;
                            font-size: 18px;
                            text-align: center;
                        ">
                            PUP FACULTY SYSTEM - RESET PASSWORD
                        </div>
                        <div style="padding: 20px;">
                            <h3 style="margin-top: 0; text-align: center;">Reset Your Password</h3>
                            <p style="text-align: center;">A password reset link has been sent to your email.</p>
                            <div style="display: flex; justify-content: center; margin-top: 20px;">
                                <button id="closeResetModalBtn" style="
                                    background-color: #700000;
                                    border: none;
                                    color: white;
                                    padding: 10px 20px;
                                    border-radius: 5px;
                                    cursor: pointer;
                                    font-weight: bold;
                                ">Close</button>
                                <button id="openResetFormBtn" style="
                                    background-color: #28a745;
                                    border: none;
                                    color: white;
                                    padding: 10px 20px;
                                    border-radius: 5px;
                                    cursor: pointer;
                                    font-weight: bold;
                                    margin-left: 10px;
                                ">Reset Now</button>
                            </div>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(resetModal);
                
                // Handle the close button
                document.getElementById('closeResetModalBtn').addEventListener('click', () => {
                    document.body.removeChild(resetModal);
                });
                
                // Handle the reset now button - load the reset form in the modal
                document.getElementById('openResetFormBtn').addEventListener('click', () => {
                    // Extract token from the URL
                    const url = new URL(data.resetLink);
                    const token = url.searchParams.get('token');
                    
                    // Create an iframe to load the reset form
                    const resetFormContainer = document.createElement('div');
                    resetFormContainer.style.cssText = `
                        background: linear-gradient(to right, #f8f9fa, #e9ecef);
                        border-radius: 10px;
                        width: 400px;
                        max-width: 90%;
                        overflow: hidden;
                        box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
                    `;
                    
                    resetFormContainer.innerHTML = `
                        <div style="
                            background: linear-gradient(to right, #700000, #9a0000);
                            color: white;
                            padding: 15px 20px;
                            font-weight: bold;
                            font-size: 18px;
                            text-align: center;
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                        ">
                            <span>PUP FACULTY SYSTEM - RESET PASSWORD</span>
                            <button id="closeResetFormBtn" style="
                                background: none;
                                border: none;
                                color: white;
                                font-size: 24px;
                                cursor: pointer;
                                line-height: 1;
                            ">X</button>
                        </div>
                        <iframe src="reset_password.php?token=${token}" style="
                            width: 100%;
                            height: 400px;
                            border: none;
                        "></iframe>
                    `;
                    
                    // Replace the current modal content
                    const currentModalContent = resetModal.querySelector('div');
                    resetModal.replaceChild(resetFormContainer, currentModalContent);
                    
                    // Handle the close button for the form
                    document.getElementById('closeResetFormBtn').addEventListener('click', () => {
                        document.body.removeChild(resetModal);
                    });
                });
            } else {
                showMessage(data.message || 'Failed to process request', 'error');
            }
            
        } catch (error) {
            showMessage('Network error: ' + error.message, 'error');
        } finally {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    });
}

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