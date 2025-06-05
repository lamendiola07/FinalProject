<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die('Unauthorized');
}

try {
    // Fetch all users (excluding passwords for security)
    $stmt = $pdo->prepare("
        SELECT 
            id, 
            full_name, 
            email, 
            faculty_id, 
            created_at, 
            last_login 
        FROM users 
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="pup_users_' . date('Y-m-d') . '.csv"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    
    // Create file pointer connected to the output stream
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, [
        'ID',
        'Full Name',
        'Email',
        'Faculty ID',
        'Registration Date',
        'Last Login'
    ]);
    
    // Add user data
    foreach ($users as $user) {
        fputcsv($output, [
            $user['id'],
            $user['full_name'],
            $user['email'],
            $user['faculty_id'],
            $user['created_at'],
            $user['last_login'] ?? 'Never'
        ]);
    }
    
    // Close the file pointer
    fclose($output);
    
} catch (PDOException $e) {
    http_response_code(500);
    die('Database error: ' . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    die('Export error: ' . $e->getMessage());
}
?>