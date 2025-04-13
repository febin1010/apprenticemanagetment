<?php
$host = 'localhost';
$username = 'root';
$password = 'KMRl@$#$2024#';
$database = 'apprentice';

session_start();

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Enable detailed error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['email']) && !empty($_POST['password'])) {
        $email = $conn->real_escape_string($_POST['email']);
        $password = $conn->real_escape_string($_POST['password']);
        
        // Fetch hashed password from database
        $query = "SELECT PASSWORD FROM login WHERE EMAIL = '$email'";
        $result = $conn->query($query);

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $stored_hashed_password = $row['PASSWORD'];

            // Verify password
            if (password_verify($password, $stored_hashed_password)) {
                // Password is correct, fetch additional user info
                $info_query = "SELECT id, NAME, APPR_ID_NO, STREAM, DEPARTMENT, PHONE_NO,start_date,end_date FROM info WHERE EMAIL = '$email'";
                $info_result = $conn->query($info_query);
                if ($info_result->num_rows == 1) {
                    $info_row = $info_result->fetch_assoc();
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_info'] = $info_row;
                    header("Location: wdiary.php");
                    exit();
                } else {
                    echo "User information not found.";
                    header("refresh:2; url=signin.html");
                    exit();
                }
            } else {
                // Password is incorrect
                echo "Invalid password. Please try again.";
                header("refresh:2; url=signin.html");
                exit();
            }
        } else {
            // Email not found in database
            echo "ID not found. Please check your ID.";
            header("refresh:2; url=signin.html");
            exit();
        }
    } else {
        echo "ID or password is missing.";
        header("refresh:2; url=signin.html");
        exit();
    }
}

$conn->close();
?>
