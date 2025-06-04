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
    
    // Create XML document
    $xml = new DOMDocument('1.0', 'UTF-8');
    $xml->formatOutput = true;
    
    // Root element
    $root = $xml->createElement('pup_users');
    $root->setAttribute('export_date', date('Y-m-d H:i:s'));
    $root->setAttribute('total_users', count($users));
    $xml->appendChild($root);
    
    // Add users
    foreach ($users as $user) {
        $userElement = $xml->createElement('user');
        $userElement->setAttribute('id', $user['id']);
        
        // Add user data as child elements
        $fullName = $xml->createElement('full_name');
        $fullName->appendChild($xml->createCDATASection($user['full_name']));
        $userElement->appendChild($fullName);
        
        $email = $xml->createElement('email');
        $email->appendChild($xml->createCDATASection($user['email']));
        $userElement->appendChild($email);
        
        $facultyId = $xml->createElement('faculty_id');
        $facultyId->appendChild($xml->createCDATASection($user['faculty_id']));
        $userElement->appendChild($facultyId);
        
        $createdAt = $xml->createElement('registration_date', $user['created_at']);
        $userElement->appendChild($createdAt);
        
        $lastLogin = $xml->createElement('last_login', $user['last_login'] ?? 'Never');
        $userElement->appendChild($lastLogin);
        
        $root->appendChild($userElement);
    }
    
    // Set headers for file download
    header('Content-Type: application/xml');
    header('Content-Disposition: attachment; filename="pup_users_' . date('Y-m-d') . '.xml"');
    header('Content-Length: ' . strlen($xml->saveXML()));
    
    // Output XML
    echo $xml->saveXML();
    
} catch (PDOException $e) {
    http_response_code(500);
    die('Database error: ' . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    die('Export error: ' . $e->getMessage());
}
?>