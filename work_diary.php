<?php
session_start();

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: signin.html");
    exit();
}

$host = 'localhost';
$username = 'root';
$password = 'KMRl@$#$2024#';
$database = 'apprentice';

$mysqli = new mysqli($host, $username, $password, $database);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$apprentice_id = $_GET['apprentice_id'] ?? '';
$period_id = $_GET['period_id'] ?? '';
$start_date_str = $_GET['start_date'] ?? '';
$end_date_str = $_GET['end_date'] ?? '';

if ($apprentice_id === '' || $period_id === '') {
    die("Missing apprentice ID or period ID.");
}

if (!ctype_digit($apprentice_id) || !ctype_digit($period_id)) {
    die("Invalid apprentice ID or period ID.");
}

$apprentice_id = intval($apprentice_id);
$period_id = intval($period_id);

$period_query = $mysqli->prepare("SELECT wd_period FROM periods WHERE id = ?");
if (!$period_query) {
    die("Prepare failed: " . $mysqli->error);
}
$period_query->bind_param("i", $period_id);
$period_query->execute();
$period_result = $period_query->get_result();
$period_data = $period_result->fetch_assoc();
$period_query->close();

if (!$period_data) {
    die("Invalid period ID.");
}

$wd_period = $period_data['wd_period'];

$apprentice_query = $mysqli->prepare("SELECT * FROM info WHERE id = ?");
if (!$apprentice_query) {
    die("Prepare failed: " . $mysqli->error);
}
$apprentice_query->bind_param("i", $apprentice_id);
$apprentice_query->execute();
$apprentice_result = $apprentice_query->get_result();
$apprentice_data = $apprentice_result->fetch_assoc();
$apprentice_query->close();

$work_diary_query = $mysqli->prepare("
    SELECT * FROM work_diary_entries 
    WHERE user_id = ? AND is_fully_submitted = 1 AND wd_date BETWEEN ? AND ?
    ORDER BY wd_date ASC
");
if (!$work_diary_query) {
    die("Prepare failed: " . $mysqli->error);
}
$work_diary_query->bind_param("iss", $apprentice_id, $start_date_str, $end_date_str);
$work_diary_query->execute();
$work_diary_result = $work_diary_query->get_result();
$work_diary_data = $work_diary_result->fetch_all(MYSQLI_ASSOC);
$work_diary_query->close();

$marks_query = $mysqli->prepare("
    SELECT * FROM marks 
    WHERE apprentice_id = ? AND period_id = ?
");
if (!$marks_query) {
    die("Prepare failed: " . $mysqli->error);
}
$marks_query->bind_param("ii", $apprentice_id, $period_id);
$marks_query->execute();
$marks_result = $marks_query->get_result();
$marks_data = $marks_result->fetch_assoc();
$marks_query->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($marks_data && $marks_data['finalize'] == 1) {
        echo json_encode(['success' => false, 'message' => 'Marks already finalized. Cannot edit marks.']);
        exit();
    }

    $punctuality = $_POST['punctuality'] ?? 0;
    $discipline = $_POST['discipline'] ?? 0;
    $performance = $_POST['performance'] ?? 0;
    $total = $punctuality + $discipline + $performance;

    if ($marks_data) {
        $update_marks_query = $mysqli->prepare("
            UPDATE marks 
            SET punctuality_attendance = ?, general_discipline = ?, performance_of_work = ?, total_marks = ?
            WHERE apprentice_id = ? AND period_id = ?
        ");
        if (!$update_marks_query) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $mysqli->error]);
            exit();
        }
        $update_marks_query->bind_param("iiiisi", $punctuality, $discipline, $performance, $total, $apprentice_id, $period_id);
        if ($update_marks_query->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating marks: ' . $update_marks_query->error]);
        }
        $update_marks_query->close();
    } else {
        $insert_marks_query = $mysqli->prepare("
            INSERT INTO marks (apprentice_id, period_id, punctuality_attendance, general_discipline, performance_of_work, total_marks)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        if (!$insert_marks_query) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $mysqli->error]);
            exit();
        }
        $insert_marks_query->bind_param("iiiiii", $apprentice_id, $period_id, $punctuality, $discipline, $performance, $total);
        if ($insert_marks_query->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error inserting marks: ' . $insert_marks_query->error]);
        }
        $insert_marks_query->close();
    }

    exit();
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="work_diary.css">
    <title>Work Diary</title>
</head>
<body>
    <div class="form-container">
        <form id="work-diary-form" class="work-diary-form">
            <div class="header">
                <h2>WORK DIARY</h2>
            </div>
            <div class="row top-row">
                <div class="input-group">
                    <label for="month">DATE:</label>
                    <input type="text" id="month" name="month" required readonly value="<?php echo $wd_period; ?>">
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
                        <?php foreach ($work_diary_data as $entry): ?>
                            <tr>
                                <td><input type="date" name="date<?php echo $entry['id']; ?>" value="<?php echo htmlspecialchars($entry['wd_date']); ?>" required readonly></td>
                                <td><input type="text" name="learning<?php echo $entry['id']; ?>" class="long-textbox" value="<?php echo htmlspecialchars($entry['wd_entry']); ?>" readonly required></td>
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
                                    <input type="number" id="punctuality" name="punctuality" max="25" required value="<?php echo htmlspecialchars($marks_data['punctuality_attendance'] ?? 0); ?>"> / 25
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>General Discipline</td>
                            <td>
                                <div class="marks-input">
                                    <input type="number" id="discipline" name="discipline" max="25" required value="<?php echo htmlspecialchars($marks_data['general_discipline'] ?? 0); ?>"> / 25
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Performance of Work</td>
                            <td>
                                <div class="marks-input">
                                    <input type="number" id="performance" name="performance" max="50" required value="<?php echo htmlspecialchars($marks_data['performance_of_work'] ?? 0); ?>"> / 50
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td>
                                <div class="marks-input">
                                    <input type="text" id="total" name="total" readonly value="<?php echo htmlspecialchars($marks_data['total_marks'] ?? 0); ?>"> / 100
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="submit-section">
                <button type="submit">Submit</button>
            </div>
        </form>
        <div id="error-log" style="display: none; color: red;"></div>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('work-diary-form');
    const punctualityInput = document.getElementById('punctuality');
    const disciplineInput = document.getElementById('discipline');
    const performanceInput = document.getElementById('performance');
    const totalInput = document.getElementById('total');

    function calculateTotal() {
        const maxPunctuality = 25;
        const maxDiscipline = 25;
        const maxPerformance = 50;

        const punctuality = Math.min(parseInt(punctualityInput.value) || 0, maxPunctuality);
        const discipline = Math.min(parseInt(disciplineInput.value) || 0, maxDiscipline);
        const performance = Math.min(parseInt(performanceInput.value) || 0, maxPerformance);

        punctualityInput.value = punctuality;
        disciplineInput.value = discipline;
        performanceInput.value = performance;

        const total = punctuality + discipline + performance;
        totalInput.value = total;
    }

    punctualityInput.addEventListener('input', calculateTotal);
    disciplineInput.addEventListener('input', calculateTotal);
    performanceInput.addEventListener('input', calculateTotal);

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(form);
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Marks updated successfully!');
                // Optionally reload the page or update the DOM
                // location.reload();
            } else {
                alert('Error updating marks: ' + data.message);
                location.reload(); // Refresh the page after error message

            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating marks: ' + error.message);
            location.reload(); // Refresh the page after error message

        });
    });
});
</script>
</body>
</html>
