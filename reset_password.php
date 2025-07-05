<?php
require_once 'config.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (empty($token)) {
    $error = 'Invalid or missing token';
} else {
    try {
        // Check if token exists and is not expired
        $stmt = $pdo->prepare("SELECT pr.user_id, pr.expires_at, u.full_name, u.email 
                              FROM password_resets pr 
                              JOIN users u ON pr.user_id = u.id 
                              WHERE pr.token = ? AND pr.used = 0");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();
        
        if (!$reset) {
            $error = 'Invalid or expired token';
        } else if (strtotime($reset['expires_at']) < time()) {
            $error = 'This reset link has expired';
        }
    } catch (PDOException $e) {
        $error = 'An error occurred: ' . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $userId = $reset['user_id'];
    
    // Validation
    if (empty($password) || empty($confirmPassword)) {
        $error = 'All fields are required';
    } else if ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        try {
            // Update user's password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);
            
            // Mark token as used
            $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
            $stmt->execute([$token]);
            
            $success = 'Your password has been updated successfully. You can now login with your new password.';
        } catch (PDOException $e) {
            $error = 'An error occurred: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Reset Password</title>
    <style>
        .reset-container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
            position: relative;
            overflow: hidden;
            width: 400px;
            max-width: 100%;
            min-height: 400px;
            margin: 50px auto;
            padding: 30px;
        }
        .reset-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0 50px;
        }
        .message {
            margin: 20px 0;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .error {
            background-color: #ffebee;
            color: #c62828;
        }
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h2 style="text-align: center;">Reset Your Password</h2>
        
        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <p style="text-align: center;">
                <a href="index.html" style="color: #ff4b2b; text-decoration: none;">Return to Login</a>
            </p>
        <?php elseif (!empty($success)): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
            <script>
                // Display success message for 2 seconds before closing
                setTimeout(function() {
                    // Notify parent window that password reset was successful
                    if (window.parent && window.parent !== window) {
                        // Close the modal in the parent window
                        window.parent.document.body.removeChild(
                            window.parent.document.querySelector('.reset-password-modal')
                        );
                    }
                }, 2000);
            </script>
        <?php else: ?>
            <p style="text-align: center;">Please enter your new password below.</p>
            <form class="reset-form" method="post">
                <div class="input-group">
                    <input type="password" name="password" required>
                    <label>New Password</label>
                </div>
                <div class="input-group">
                    <input type="password" name="confirm_password" required>
                    <label>Confirm New Password</label>
                </div>
                <button type="submit" class="form-btn">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>