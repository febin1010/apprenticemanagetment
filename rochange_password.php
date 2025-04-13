<?php
session_start();
header('Content-Type: application/json');
error_reporting(0); // Turn off error reporting

// Start output buffering
ob_start();

// Log entire session for debugging
error_log("Session Data: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    ob_end_clean(); // Clear the buffer
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

$currentPassword = $data['currentPassword'];
$newPassword = $data['newPassword'];

$user_info = $_SESSION['user_info'];
$email = null;

// Extract email from the session array
foreach ($user_info as $user) {
    if (isset($user['email'])) {
        $email = $user['email'];
        break; // Use the first email found
    }
}

// Log the email for debugging
error_log("User Email: $email");

if (empty($email)) {
    ob_end_clean(); // Clear the buffer
    echo json_encode(['success' => false, 'message' => 'User email not set in session.']);
    exit();
}

// Database connection
$dsn = 'mysql:host=localhost;dbname=apprentice;charset=utf8';
$username = 'root';
$password = 'KMRl@$#$2024#';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the current hashed password from the database
    $stmt = $pdo->prepare('SELECT password FROM rologin WHERE email = :email');
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$result) {
        ob_end_clean(); // Clear the buffer
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit();
    }

    // Verify the current password
    if (!password_verify($currentPassword, $result['password'])) {
        ob_end_clean(); // Clear the buffer
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
        exit();
    }

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update the password in the database for all entries with the same email
    $stmt = $pdo->prepare('UPDATE rologin SET password = :newPassword WHERE email = :email');
    $stmt->bindParam(':newPassword', $hashedPassword);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    ob_end_clean(); // Clear the buffer
    echo json_encode(['success' => true, 'message' => 'Password changed successfully for all accounts with this email.']);
} catch (PDOException $e) {
    ob_end_clean(); // Clear the buffer
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
