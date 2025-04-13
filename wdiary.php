<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: signin.html");
    exit();
}

// Retrieve user information from session
$user_info = $_SESSION['user_info'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="wdiary.css"> <!-- Link to external CSS file -->
    <title>Work Diary</title>
</head>
<style>
    .logo {
        display: flex;
        align-items: center;
        margin-bottom: 10px; /* Add margin between logo and text */
    }

    .logo img {
        height: 60px; /* Adjust the height as needed */
        width: auto; /* Maintain aspect ratio */
        margin-right: 10px; /* Add margin to separate logo from text */
    }

    .logo-text-container {
        flex: 1; /* Take up remaining space */
        display: flex;
        justify-content: center; /* Center text horizontally */
        align-items: center; /* Center text vertically */
        text-align: center;
    }

    .logo-text h1, .logo-text h2 {
        margin: 0; /* Remove default margins */
    }

    .logo-text h2 {
        font-family: Monospace;
        font-size: 25px;
        font-weight: 100;
    }

    .logo-text h1 {
        font-family: Monospace;
        font-size: 20px;
        font-weight: bold;
    }

    /* Media query for smaller screens */
    @media (max-width: 768px) {
        .header {
            flex-direction: column;
            align-items: center;
        }

        .logo-text-container {
            margin-top: 10px;
        }

        .logout-button,
        .change-password-button {
            margin-top: 10px;
        }
    }

    .header-buttons {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }

    .header-buttons form,
    .header-buttons button {
        margin-top: 10px;
        background-color: #4CAF50; /* Green background */
        color: white; /* White text */
        border: none; /* Remove border */
        cursor: pointer; /* Pointer cursor on hover */
    }


    .header-buttons form:hover,
    .header-buttons button:hover {
        background-color: #45a049; /* Darker green on hover */
    }


        /* Modal styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgb(0,0,0); 
            background-color: rgba(0,0,0,0.4); 
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto; 
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
        }

        .input-group input {
            width: calc(100% - 40px);
            padding: 10px;
            margin-right: 5px;
        }

        .input-group .toggle-password {
            position: absolute;
            margin-left: -30px;
            cursor: pointer;
        }

        .modal-content button {
            padding: 10px 20px;
            background-color: #4CAF50; /* Green background */
            color: white; /* White text */
            border: none; /* Remove border */
            cursor: pointer; /* Pointer cursor on hover */
        }

        .modal-content button:hover {
            background-color: #45a049; /* Darker green on hover */
        }
  
</style>
<body>
<div class="header">
    <div class="logo">
        <img src="./Koch_Metro_Logo.png" alt="KMRL Logo">
    </div>
    <div class="logo-text-container">
        <div class="logo-text">
            <h1>HUMAN RESOURCES</h1>
            <h2>TRAINING & DEVELOPMENT DIVISION</h2>
        </div>
    </div>
    <div class="header-buttons">
        <form action="logout.php" method="post" class="logout-button">
            <input type="submit" value="Logout">
        </form>
        <button class="change-password-button" id="changePasswordBtn">Change Password</button>
    </div>
</div>

<!-- The Modal -->
<div id="changePasswordModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Change Password</h2>
        <form id="changePasswordForm">
            <div class="input-group">
                <label for="current-password">Current Password:</label>
                <input type="password" id="current-password" name="current-password" required>
                <span class="toggle-password" onclick="togglePassword('current-password')">👁️</span>
            </div>
            <div class="input-group">
                <label for="new-password">New Password:</label>
                <input type="password" id="new-password" name="new-password" required>
                <span class="toggle-password" onclick="togglePassword('new-password')">👁️</span>
            </div>
            <div class="input-group">
                <label for="confirm-password">Confirm Password:</label>
                <input type="password" id="confirm-password" name="confirm-password" required>
                <span class="toggle-password" onclick="togglePassword('confirm-password')">👁️</span>
            </div>
            <button type="submit">Submit</button>
        </form>
    </div>
</div>

<div class="form-container">
    <form action="#" method="post" class="work-diary-form">
        <div class="form-header">
            <h2>WORK DIARY</h2>
        </div>
        <div class="row top-row">
            <div class="input-group">
                <label for="month">DATE:</label>
                <input type="text" id="month" name="month" required readonly>
            </div>
        </div>

        <!-- Personal Details Section -->
        <div class="personal-details-section">
            <h3>Personal Details section:</h3>
            <div class="row">
                <div class="input-group">
                    <label for="name">NAME:</label>
                    <input type="text" id="name" name="name" required readonly value="<?php echo htmlspecialchars($user_info['NAME']); ?>">
                </div>
                <div class="input-group">
                    <label for="appr_id">APPR ID NO:</label>
                    <input type="text" id="appr_id" name="appr_id" required readonly value="<?php echo htmlspecialchars($user_info['APPR_ID_NO']); ?>">
                </div>
                <div class="input-group">
                    <label for="year_stream">STREAM:</label>
                    <input type="text" id="year_stream" name="year_stream" required readonly value="<?php echo htmlspecialchars($user_info['STREAM']); ?>">
                </div>
            </div>
            <div class="row">
                <div class="input-group">
                    <label for="department">DEPARTMENT:</label>
                    <input type="text" id="department" name="department" required readonly value="<?php echo htmlspecialchars($user_info['DEPARTMENT']); ?>">
                </div>
                <div class="input-group">
                    <label for="phone">PHONE NO:</label>
                    <input type="number" id="phone" name="phone" required readonly value="<?php echo htmlspecialchars($user_info['PHONE_NO']); ?>">
                </div>
		<div class="input-group">
                    <label for="startdate">Period of Apprenticeship</label>
                    <input type="text" id="startdate" name="startdate" required readonly value="<?php echo $user_info['start_date'].' to '.$user_info['end_date']; ?>">
                </div>
            </div>
        </div>

        <!-- Add Note Section -->
        <div class="note-section">
            <h3>Enter Work Diary:</h3>
            <div class="row calendar-row">
                <div class="input-group">
                    <label for="calendar">Select Date:</label>
                    <input type="date" id="calendar" name="calendar">
                </div>
                <div class="input-group">
                    <label for="note">Add Note:</label>
                    <textarea id="note" name="note"></textarea>
                </div>
                <div class="input-group">
                    <button type="button" id="add-note-button">Add Note</button>
                </div>
            </div>
        </div>

        <!-- Learnings Section -->
        <div class="learnings-section">
            <h3>Diary section:</h3>
            <button type="button" id="prev-period-button">Previous Period</button>
            <button type="button" id="next-period-button">Next Period</button><br>
            <table>
                <thead>
                    <tr>
                        <th>DATE</th>
                        <th>LEARNINGS/ACTIVITIES DONE DURING THE DAY</th>
                    </tr>
                </thead>
                <tbody id="learnings-table-body">
                    <!-- Rows will be generated by JavaScript -->
                </tbody>
            </table>
        </div>

        <div class="row radio-row">
            <label>
                Once submitted, this form cannot be edited.
            </label>
        </div>
        <div class="row submit-row"> <!-- New div for submit button -->
            <input type="submit" value="Submit">
        </div>
    </form>
</div>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    const learningsTableBody = document.getElementById('learnings-table-body');
    const monthInput = document.getElementById('month');
    const addNoteButton = document.getElementById('add-note-button');
    const calendarInput = document.getElementById('calendar');
    const noteInput = document.getElementById('note');
    const prevPeriodButton = document.getElementById('prev-period-button');
    const nextPeriodButton = document.getElementById('next-period-button');
    const workDiaryForm = document.querySelector('.work-diary-form');
    const submitButton = document.querySelector('.submit-row input[type="submit"]');
    const modal = document.getElementById('changePasswordModal');
    const btn = document.getElementById('changePasswordBtn');
    const span = document.getElementsByClassName('close')[0];
    const form = document.getElementById('changePasswordForm');

    btn.onclick = function() {
        modal.style.display = 'block';
    }

    span.onclick = function() {
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    form.addEventListener('submit', function(event) {
    event.preventDefault();
    const currentPassword = document.getElementById('current-password').value;
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;

    if (newPassword !== confirmPassword) {
        alert('New password and confirm password do not match.');
        return;
    }

    // Perform AJAX request to change_password.php
    fetch('change_password.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            currentPassword: currentPassword,
            newPassword: newPassword
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Password changed successfully.');
            // Clear the input fields
            document.getElementById('current-password').value = '';
            document.getElementById('new-password').value = '';
            document.getElementById('confirm-password').value = '';
            modal.style.display = 'none';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});

    let now = new Date();
    let currentPlusOneDay = new Date(now);
    currentPlusOneDay.setDate(currentPlusOneDay.getDate() + 1);

    let startDate;
    let nextMonth = now.getMonth();
    let nextYear = now.getFullYear();
    let startPeriodDate;

    // Function to calculate the start date
    function calculateStartDate() {
        if (now.getDate() > 20) {
            nextMonth += 1;
            if (nextMonth > 11) {
                nextMonth = 0;
                nextYear += 1;
            }
        }
        return new Date(nextYear, nextMonth - 1, 22);
    }

    startPeriodDate = calculateStartDate();

    let startMonth = startPeriodDate.toLocaleString('default', { month: 'long' });
    let startYear = startPeriodDate.getFullYear();
    let endDate = new Date(nextYear, nextMonth, 20);
    let endMonth = endDate.toLocaleString('default', { month: 'long' });
    let endYear = endDate.getFullYear();

    monthInput.value = `${startPeriodDate.getDate()-1} ${startMonth} ${startYear} - ${endDate.getDate()} ${endMonth} ${endYear}`;

    let dates = [];
    let currentDate = new Date(startPeriodDate);

    while (!(currentDate.getDate() === 21 && currentDate.getMonth() !== startPeriodDate.getMonth())) {
        dates.push(new Date(currentDate));
        currentDate.setDate(currentDate.getDate() + 1);
    }

    dates.push(new Date(currentDate.setDate(currentDate.getDate())));

    function populateTable(data) {
        learningsTableBody.innerHTML = '';
        dates.forEach(date => {
            const note = '';

            const row = document.createElement('tr');
            const isPastOrToday = currentPlusOneDay >= date;

            row.innerHTML = `
                <td><input type="date" name="date${date.getTime()}" value="${date.toISOString().split('T')[0]}" required readonly></td>
                <td><input type="text" name="learning${date.getTime()}" class="long-textbox" value="${note}" required readonly></td>
            `;
            learningsTableBody.appendChild(row);
        });

    // Populate the table with the fetched data
    data.forEach(note => {
        const dateInput = learningsTableBody.querySelector(`input[type="date"][value="${note.wd_date}"]`);
        if (dateInput) {
            const learningInput = dateInput.closest('tr').querySelector('input[type="text"]');
            if (learningInput) {
                learningInput.value = note.wd_entry ? note.wd_entry : ''; // Populate note if entry exists
            }
            if (note.is_fully_submitted) {
                learningInput.readOnly = true;
            }
        }
    });
}


    function fetchAndUpdateTable() {
        fetch('fetch_notes.php')
            .then(response => response.json())
            .then(data => {
                console.log('Fetched data:', data); // Log the fetched data for debugging
                fetchedData = data; // Store fetched data in a variable
                populateTable(fetchedData); // Populate the table with the fetched data
            })
            .catch(error => {
                console.error('Error fetching notes:', error);
            });
    }

    fetchAndUpdateTable(); // Initial fetch to populate the table

    addNoteButton.addEventListener('click', function () {
        const selectedDate = calendarInput.value;
        const note = noteInput.value; 

        // Check if selectedDate is in the dates array
        const isValidDate = dates.some(date => date.toISOString().split('T')[0] === selectedDate);

        if (isValidDate && note) {
            const existingEntry = fetchedData.find(entry => entry.wd_date === selectedDate);

            const dataToSend = existingEntry 
                ? { entry_id: existingEntry.id, note: note } 
                : { date: selectedDate, note: note };

            console.log('Data to send:', dataToSend); // Debugging statement

            fetch('save_note.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(dataToSend)
            })
            .then(response => response.json())
            .then(data => {
                console.log('Response:', data); // Debugging statement
                if (data.success) {
                    alert('Note saved successfully');
                    fetchAndUpdateTable(); // Fetch and update the table after saving a note
                } else {
                    alert('Error saving note: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });

            // Clear noteInput value
            noteInput.value = '';
        } else {
            if (!isValidDate) {
                alert('You can only add notes for the displayed dates.');
            } else {
                alert('Please enter a note.');
            }
        }
    });

    calendarInput.addEventListener('change', function () {
    const selectedDate = new Date(calendarInput.value);

    noteInput.disabled = false;

    if (fetchedData) {
        const existingNote = fetchedData.find(entry => entry.wd_date === calendarInput.value);
        noteInput.value = existingNote ? existingNote.wd_entry : '';
        if (existingNote && existingNote.is_fully_submitted) {
            noteInput.readOnly = true;
        } else {
            noteInput.readOnly = false;
        }
    } else {
        noteInput.value = '';
    }
});

    function changePeriod(direction) {
        const currentPeriodStartDate = new Date(now.getFullYear(), now.getMonth() - 1, 22);
        const currentPeriodEndDate = new Date(now.getFullYear(), now.getMonth(), 20);

        if (direction === 'prev') {
            nextMonth -= 1;
            if (nextMonth < 0) {
                nextMonth = 11;
                nextYear -= 1;
            }
        } else if (direction === 'next') {
            nextMonth += 1;
            if (nextMonth > 11) {
                nextMonth = 0;
                nextYear += 1;
            }
        }

        startPeriodDate = new Date(nextYear, nextMonth - 1, 22);
        startMonth = startPeriodDate.toLocaleString('default', { month: 'long' });
        startYear = startPeriodDate.getFullYear();
        endDate = new Date(nextYear, nextMonth, 21);
        endMonth = endDate.toLocaleString('default', { month: 'long' });
        endYear = endDate.getFullYear();

        monthInput.value = `${startPeriodDate.getDate()-1} ${startMonth} ${startYear} - ${endDate.getDate()} ${endMonth} ${endYear}`;

        dates = [];
        currentDate = new Date(startPeriodDate);
        while (!(currentDate.getDate() === 21 && currentDate.getMonth() !== startPeriodDate.getMonth())) {
            dates.push(new Date(currentDate));
            currentDate.setDate(currentDate.getDate() + 1);
        }
        dates.push(new Date(currentDate.setDate(currentDate.getDate())));

        fetchAndUpdateTable(); // Fetch and update the table after changing period

        // Disable nextPeriodButton if the endDate is beyond the current period
        nextPeriodButton.disabled = endDate >= currentPeriodEndDate;
    }

    prevPeriodButton.addEventListener('click', function () {
        changePeriod('prev');
    });

    nextPeriodButton.addEventListener('click', function () {
        changePeriod('next');
    });

    // Initial check for disabling the next period button
    const currentPeriodEndDate = new Date(now.getFullYear(), now.getMonth(), 20);
    nextPeriodButton.disabled = endDate >= currentPeriodEndDate;

    workDiaryForm.addEventListener('submit', function(event) {
    // Prevent the default form submission
    event.preventDefault(); 

    // Validate all table entries
    const allEntriesFilled = Array.from(learningsTableBody.querySelectorAll('input[type="text"]'))
        .every(input => input.value.trim() !== '');

    if (!allEntriesFilled) {
        alert('Please fill in all the entries in the table before submitting.');
        return;
    }

    const formData = new FormData(workDiaryForm);
    const dataToSend = [];

    formData.forEach((value, key) => {
        if (key.startsWith('learning')) {
            const dateKey = key.replace('learning', 'date');
            const dateValue = formData.get(dateKey);
            dataToSend.push({ date: dateValue, note: value });
        }
    });

    // Send the combined data to a single PHP script
    fetch('update_database.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            entries: dataToSend,
            is_fully_submitted: 1,
            wd_period: `${startMonth} ${startYear} - ${endMonth} ${endYear}`,
            start_date: startPeriodDate.toISOString().split('T')[0],
            end_date: endDate.toISOString().split('T')[0]
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response:', data); // Debugging statement
        if (data.success) {
            alert('Entries and database updated successfully');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });

    
});

});

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}

</script>
</body>
</html>
