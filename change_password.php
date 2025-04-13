<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

$currentPassword = $data['currentPassword'];
$newPassword = $data['newPassword'];

$user_info = $_SESSION['user_info'];
$apprenticeId = $user_info['APPR_ID_NO']; // Adjust this according to your database schema

// Database connection
$dsn = 'mysql:host=localhost;dbname=apprentice;charset=utf8';
$username = 'root';
$password = 'KMRl@$#$2024#';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the current hashed password from the database
    $stmt = $pdo->prepare('SELECT password FROM login WHERE APPR_ID_NO = :apprenticeId');
    $stmt->bindParam(':apprenticeId', $apprenticeId);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit();
    }

    // Verify the current password
    if (!password_verify($currentPassword, $result['password'])) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
        exit();
    }

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update the password in the database
    $stmt = $pdo->prepare('UPDATE login SET password = :newPassword WHERE APPR_ID_NO = :apprenticeId');
    $stmt->bindParam(':newPassword', $hashedPassword);
    $stmt->bindParam(':apprenticeId', $apprenticeId);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Password changed successfully.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
