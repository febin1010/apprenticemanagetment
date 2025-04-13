<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: signin.html");
    exit();
}

// Database connection
$host = 'localhost';
$username = 'root';
$password = 'KMRl@$#$2024#';
$database = 'apprentice';
$mysqli = new mysqli($host, $username, $password, $database);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Retrieve apprentice_id and period_id from GET parameters
$appr_id_no = $_GET['id'] ?? '';
$period_id = $_GET['period'] ?? '';

if (empty($appr_id_no) || empty($period_id)) {
    die("Missing APPR_ID_NO or period_id.");
}

// Retrieve wd_period using period_id
$period_query = $mysqli->prepare("SELECT wd_period FROM periods WHERE id = ?");
$period_query->bind_param("i", $period_id);
$period_query->execute();
$period_result = $period_query->get_result();
$period_data = $period_result->fetch_assoc();
$period_query->close();

if (!$period_data) {
    die("Invalid period_id.");
}

$wd_period = $period_data['wd_period'];

// Parse wd_period
$period_parts = explode(' - ', $wd_period);
if (count($period_parts) !== 2) {
    die("Invalid wd_period format. Expected format: 'Start Month Year - End Month Year'.");
}

$start_month_year = $period_parts[0];
$end_month_year = $period_parts[1];

try {
    $start_date = new DateTime("21 $start_month_year");
    $end_date = new DateTime("20 $end_month_year");
    $end_date->setDate($end_date->format('Y'), $end_date->format('m'), 20);
} catch (Exception $e) {
    die("Date parsing error: " . $e->getMessage());
}

$start_date_str = $start_date->format('Y-m-d');
$end_date_str = $end_date->format('Y-m-d');

// Retrieve apprentice information
$apprentice_query = $mysqli->prepare("SELECT * FROM info WHERE id = ?");
$apprentice_query->bind_param("s", $appr_id_no);
$apprentice_query->execute();
$apprentice_result = $apprentice_query->get_result();
$apprentice_data = $apprentice_result->fetch_assoc();
$apprentice_query->close();

if (!$apprentice_data) {
    die("Invalid APPR_ID_NO.");
}

// Retrieve work diary entries
$work_diary_query = $mysqli->prepare("
    SELECT * FROM work_diary_entries 
    WHERE user_id = ? AND is_fully_submitted = 1 AND wd_date BETWEEN ? AND ?
    ORDER BY wd_date ASC
");
$work_diary_query->bind_param("iss", $apprentice_data['id'], $start_date_str, $end_date_str);
$work_diary_query->execute();
$work_diary_result = $work_diary_query->get_result();
$work_diary_data = $work_diary_result->fetch_all(MYSQLI_ASSOC);
$work_diary_query->close();

// Retrieve marks
$marks_query = $mysqli->prepare("
    SELECT * FROM marks 
    WHERE apprentice_id = ? AND period_id = ?
");
$marks_query->bind_param("ii", $apprentice_data['id'], $period_id);
$marks_query->execute();
$marks_result = $marks_query->get_result();
$marks_data = $marks_result->fetch_assoc();
$marks_query->close();

$mysqli->close();

// Generate all dates in the period
$dates = [];
$current_date = $start_date;
while ($current_date <= $end_date) {
    $dates[] = $current_date->format('Y-m-d');
    $current_date->modify('+1 day');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="work_diary.css">
    <title>Edit Work Diary</title>
</head>
<body>
    <div class="form-container">
        <form id="work-diary-form" class="work-diary-form" method="POST" action="save_work_diary.php">
            <div class="header">
                <h2>WORK DIARY</h2>
            </div>
            <div class="row top-row">
                <div class="input-group">
                    <label for="month">DATE:</label>
                    <input type="text" id="month" name="month" required readonly value="<?php echo htmlspecialchars($wd_period); ?>">
                </div>
            </div>

            <?php if ($apprentice_data): ?>
            <div class="row">
                <div class="input-group">
                    <label for="name">NAME:</label>
                    <input type="text" id="name" name="name" required readonly value="<?php echo htmlspecialchars($apprentice_data['NAME']); ?>">
                </div>
                <div class="input-group">
                    <label for="appr_id">APPR ID NO:</label>
                    <input type="text" id="appr_id" name="appr_id" required readonly value="<?php echo htmlspecialchars($apprentice_data['APPR_ID_NO']); ?>">
                </div>
                <div class="input-group">
                    <label for="year_stream">STREAM:</label>
                    <input type="text" id="year_stream" name="year_stream" required readonly value="<?php echo htmlspecialchars($apprentice_data['STREAM']); ?>">
                </div>
            </div>
            <div class="row">
                <div class="input-group">
                    <label for="department">DEPARTMENT:</label>
                    <input type="text" id="department" name="department" required readonly value="<?php echo htmlspecialchars($apprentice_data['DEPARTMENT']); ?>">
                </div>
                <div class="input-group">
                    <label for="phone">PHONE NO:</label>
                    <input type="number" id="phone" name="phone" required readonly value="<?php echo htmlspecialchars($apprentice_data['PHONE_NO']); ?>">
                </div>
            </div>
            <?php else: ?>
            <div class="row">
                <p>No apprentice data found.</p>
            </div>
            <?php endif; ?>

            <div class="learnings-section">
                <table>
                    <thead>
                        <tr>
                            <th>DATE</th>
                            <th>LEARNINGS/ACTIVITIES DONE DURING THE DAY</th>
                        </tr>
                    </thead>
                    <tbody id="learnings-table-body">
                        <?php foreach ($dates as $date): ?>
                            <?php
                            $existing_entry = array_filter($work_diary_data, function($entry) use ($date) {
                                return $entry['wd_date'] === $date;
                            });
                            $existing_entry = array_shift($existing_entry);
                            ?>
                            <tr>
                                <td><input type="date" name="date<?php echo htmlspecialchars($date); ?>" value="<?php echo htmlspecialchars($date); ?>" required readonly></td>
                                <td><input type="text" name="learning<?php echo htmlspecialchars($date); ?>" class="long-textbox" value="<?php echo htmlspecialchars($existing_entry['wd_entry'] ?? ''); ?>" required></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="marks-section">
                <h3>SESSIONAL MARKS FOR ON THE JOB LEARNING</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Criteria</th>
                            <th>Marks Awarded</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Punctuality and Attendance</td>
                            <td>
                                <div class="marks-input">
                                    <input type="number" id="punctuality" name="punctuality" max="25" value="<?php echo htmlspecialchars($marks_data['punctuality_attendance'] ?? 0); ?>" readonly> / 25
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>General Discipline</td>
                            <td>
                                <div class="marks-input">
                                    <input type="number" id="discipline" name="discipline" max="25" value="<?php echo htmlspecialchars($marks_data['general_discipline'] ?? 0); ?>" readonly> / 25
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Performance of Work</td>
                            <td>
                                <div class="marks-input">
                                    <input type="number" id="performance" name="performance" max="50" value="<?php echo htmlspecialchars($marks_data['performance_of_work'] ?? 0); ?>" readonly> / 50
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

             <!-- Hidden fields for id and period -->
             <input type="hidden" id="appr_id_hidden" name="appr_id" value="<?php echo htmlspecialchars($appr_id_no); ?>">
            <input type="hidden" id="period_id_hidden" name="period_id" value="<?php echo htmlspecialchars($period_id); ?>">

            <div class="form-actions">
                <button type="button" id="confirm-save">Save Changes</button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('confirm-save').addEventListener('click', function() {
            const confirmSave = confirm('Are you sure you want to save changes?');
            if (confirmSave) {
                document.getElementById('work-diary-form').submit();
            }
        });
    </script>
</body>
</html>
