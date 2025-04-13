<?php
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    error_log('Received data in update_officer.php: ' . print_r($data, true));

    if (!isset($data['id'], $data['name'], $data['department'], $data['email'], $data['location'])) { // Updated: Added location
        throw new Exception('Missing required data fields.');
    }

    $id = $data['id'];
    $name = $data['name'];
    $department = $data['department'];
    $email = $data['email'];
    $password = isset($data['password']) ? $data['password'] : null;
    $location = $data['location']; // New: Location field

    $conn = new mysqli('localhost', 'root', 'KMRl@$#$2024#', 'apprentice');
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    $currentEmailSql = $conn->prepare('SELECT email FROM roinfo WHERE id = ?');
    if (!$currentEmailSql) {
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }
    $currentEmailSql->bind_param('i', $id);
    $currentEmailSql->execute();
    $currentEmailSql->bind_result($currentEmail);
    $currentEmailSql->fetch();
    $currentEmailSql->close();

    error_log("Current Email: $currentEmail");
    error_log("New Email: $email");

    $conn->begin_transaction();

    if (strcasecmp($email, $currentEmail) !== 0) {
        $checkSql = $conn->prepare('SELECT id FROM roinfo WHERE LOWER(email) = LOWER(?) AND id != ?');
        if (!$checkSql) {
            throw new Exception('Prepare statement failed: ' . $conn->error);
        }
        $checkSql->bind_param('si', $email, $id);
        $checkSql->execute();
        $checkSql->store_result();
        error_log("Check SQL Rows: " . $checkSql->num_rows);
        if ($checkSql->num_rows > 0) {
            throw new Exception('Email already exists.');
        }
        $checkSql->close();
    }

    // Updated: Update SQL query to include Location
    $updateInfoSql = $conn->prepare('UPDATE roinfo SET Name = ?, Department = ?, email = ?, location = ? WHERE id = ?');
    if (!$updateInfoSql) {
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }
    $updateInfoSql->bind_param('ssssi', $name, $department, $email, $location, $id);
    $updateInfoSql->execute();
    if ($updateInfoSql->errno) {
        throw new Exception('Execute failed: ' . $updateInfoSql->error);
    }

    if (strcasecmp($email, $currentEmail) !== 0) {
        $updateLoginSql = $conn->prepare('UPDATE rologin SET EMAIL = ? WHERE officer_id = ?');
        if (!$updateLoginSql) {
            throw new Exception('Prepare statement failed: ' . $conn->error);
        }
        $updateLoginSql->bind_param('si', $email, $id);
        $updateLoginSql->execute();
        if ($updateLoginSql->errno) {
            throw new Exception('Execute failed: ' . $updateLoginSql->error);
        }
    }

    if ($password !== null && $password !== '') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updatePasswordSql = $conn->prepare('UPDATE rologin SET PASSWORD = ? WHERE officer_id = ?');
        if (!$updatePasswordSql) {
            throw new Exception('Prepare statement failed: ' . $conn->error);
        }
        $updatePasswordSql->bind_param('si', $hashedPassword, $id);
        $updatePasswordSql->execute();
        if ($updatePasswordSql->errno) {
            throw new Exception('Execute failed: ' . $updatePasswordSql->error);
        }
    }

    $conn->commit();

    error_log('Successfully updated officer in update_officer.php');
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    error_log('Error in update_officer.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
