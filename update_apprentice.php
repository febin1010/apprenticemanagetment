<?php
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    $requiredFields = [
        'apprenticeId', 'name', 'phone', 'location', 'department', 'reportingOfficer', 
        'stream', 'email', 'start_date', 'end_date' // Include start_date and end_date as required fields
    ];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    $apprenticeID = $data['apprenticeId'];
    $name = $data['name'];
    $phone = $data['phone'];
    $location = $data['location'];
    $department = $data['department'];
    $reportingOfficer = $data['reportingOfficer'];
    $stream = $data['stream'];
    $email = $data['email'];
    $password = isset($data['password']) ? $data['password'] : null;
    $start_date = $data['start_date'];
    $end_date = $data['end_date'];

    $conn = new mysqli('localhost', 'root', 'KMRl@$#$2024#', 'apprentice');
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Get reporting officer ID
    $sql = $conn->prepare('SELECT id FROM roinfo WHERE Name = ?');
    if (!$sql) {
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }
    $sql->bind_param('s', $reportingOfficer);
    $sql->execute();
    $result = $sql->get_result();
    $row = $result->fetch_assoc();
    if (!$row) {
        throw new Exception('Reporting officer not found');
    }

    $reportingOfficerID = $row['id'];

    // Begin transaction
    $conn->begin_transaction();

    // Update info table
    $sql = $conn->prepare('UPDATE info SET NAME = ?, PHONE_NO = ?, location = ?, DEPARTMENT = ?, reporting_officer_id = ?, STREAM = ?, EMAIL = ?, start_date = ?, end_date = ? WHERE APPR_ID_NO = ?');
    if (!$sql) {
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }
    $sql->bind_param('ssssssssss', $name, $phone, $location, $department, $reportingOfficerID, $stream, $email, $start_date, $end_date, $apprenticeID);
    $sql->execute();
    if ($sql->errno) {
        throw new Exception('Execute failed: ' . $sql->error);
    }

    // Update login table if password is provided
    if ($password !== null && $password !== '') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = $conn->prepare('UPDATE login SET EMAIL = ?, PASSWORD = ? WHERE APPR_ID_NO = ?');
        if (!$sql) {
            throw new Exception('Prepare statement failed: ' . $conn->error);
        }
        $sql->bind_param('sss', $email, $hashedPassword, $apprenticeID);
    } else {
        $sql = $conn->prepare('UPDATE login SET EMAIL = ? WHERE APPR_ID_NO = ?');
        if (!$sql) {
            throw new Exception('Prepare statement failed: ' . $conn->error);
        }
        $sql->bind_param('ss', $email, $apprenticeID);
    }
    $sql->execute();
    if ($sql->errno) {
        throw new Exception('Execute failed: ' . $sql->error);
    }

    // Commit transaction
    $conn->commit();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
