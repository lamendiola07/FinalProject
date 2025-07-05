<?php
// Turn off PHP error display in the output
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get the raw input and decode it
$input_json = file_get_contents('php://input');
$input = json_decode($input_json, true);

// Check if JSON is valid
if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON: ' . json_last_error_msg()]);
    exit;
}

$email = trim($input['email'] ?? '');

// Validation
if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

try {
    // Check if email exists in the database
    $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Don't reveal that the email doesn't exist for security reasons
        echo json_encode(['success' => true, 'message' => 'If your email exists in our system, you will receive password reset instructions']);
        exit;
    }
    
    // Generate a unique token
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + 3600); // Token expires in 1 hour
    
    // Store the token in the database
    $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user['id'], $token, $expires]);
    
    // Create the reset link
    $resetLink = "http://{$_SERVER['HTTP_HOST']}/FinalProject/reset_password.php?token=$token";
    
    // For development/testing purposes - bypass email sending and return the link directly
    // This is a workaround since XAMPP mail() function often doesn't work without configuration
    echo json_encode([
        'success' => true, 
        'message' => 'Password reset instructions sent. For testing, use this link:', 
        'resetLink' => $resetLink
    ]);
    
    /* 
    // Uncomment this section when you have a working mail server
    $to = $email;
    $subject = "PUP FACULTY SYSTEM- RESET PASSWORD";
    $message = "Hello {$user['full_name']},\n\n";
    $message .= "You have requested to reset your password. Please click the link below to set a new password:\n\n";
    $message .= "$resetLink\n\n";
    $message .= "This link will expire in 1 hour.\n\n";
    $message .= "If you did not request this password reset, please ignore this email.\n\n";
    $message .= "Regards,\nPUP Login System";
    
    $headers = "From: noreply@pup.edu.ph\r\n";
    $headers .= "Reply-To: noreply@pup.edu.ph\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    if (mail($to, $subject, $message, $headers)) {
        echo json_encode(['success' => true, 'message' => 'If your email exists in our system, you will receive password reset instructions']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send email. Please try again later.']);
    }
    */
    
} catch (PDOException $e) {
    // Log the error to a file instead of displaying it
    error_log("Password reset error: " . $e->getMessage(), 3, "error_log.txt");
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
}
?>