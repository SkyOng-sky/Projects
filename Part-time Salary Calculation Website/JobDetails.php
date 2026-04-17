<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "part-time calculator");
if(mysqli_connect_errno()){
    echo "Failed to connect to MySQL:".mysqli_connect_error();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['save_shift'])) {
    $job_id = $_POST['job_id'];
    $shift_id = $_POST['shift_id']; 
    $work_date = $_POST['work_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $break_minutes = $_POST['break_minutes'];
    $meal_fee = $_POST['meal_fee'];

    if (!empty($shift_id)) {
        // UPDATE existing
        $sql = "UPDATE shifts SET start_time='$start_time', end_time='$end_time', break_minutes='$break_minutes', meal_fee='$meal_fee'
                WHERE shift_id='$shift_id'";
    } else {
        // INSERT new
        $sql = "INSERT INTO shifts (job_id, work_date, start_time, end_time, break_minutes, meal_fee) 
                VALUES ('$job_id', '$work_date', '$start_time', '$end_time', '$break_minutes', '$meal_fee')";
    }

    if (mysqli_query($conn, $sql)) {
        header("Location: JobDetails.php?id=" . $job_id);
        exit();
    }
}

// Logic for the Delete button inside modal
if (isset($_POST['delete_shift'])) {
    $shift_id = $_POST['shift_id'];
    $job_id = $_POST['job_id'];
    mysqli_query($conn, "DELETE FROM shifts WHERE shift_id='$shift_id'");
    header("Location: JobDetails.php?id=" . $job_id);
    exit();
}

$job_id_current = $_GET['id'];

// Get Job Details (Rate) for calculations
$job_query = mysqli_query($conn, "SELECT hour_rate FROM job_profiles WHERE job_id = '$job_id_current'");
$job_data = mysqli_fetch_assoc($job_query);
$current_rate = $job_data['hour_rate'];

// Fetch all shifts for this job
$shift_sql = "SELECT * FROM shifts WHERE job_id = '$job_id_current'";
$shift_result = mysqli_query($conn, $shift_sql);
$total_worked_seconds = 0;
$total_earnings = 0; 
$events = [];

while ($row = mysqli_fetch_assoc($shift_result)) {
    $start = strtotime($row['start_time']);
    $end = strtotime($row['end_time']);
    
    // Handle overnight shifts
    if ($end < $start) { $end += 86400; }

    $diff = $end - $start;
    $net_seconds = $diff - ($row['break_minutes'] * 60);
    
    $shift_hours = $net_seconds / 3600;
    $shift_pay = ($shift_hours * $current_rate) - $row['meal_fee'];
    
    $total_worked_seconds += $net_seconds;
    $total_earnings += $shift_pay; 

    // Formatting for the calendar display
    $eventTitle = date('H:i', $start) . " - " . date('H:i', $end);
    if ($row['meal_fee'] > 0) {
        $eventTitle .= " (-RM" . number_format($row['meal_fee'], 0) . ")";
    }

    $events[] = [
        'id'    => $row['shift_id'],
        'title' => $eventTitle, 
        'start' => $row['work_date'],
        'backgroundColor' => '#d1eaa9',
        'borderColor' => '#A3D18A',
        'textColor' => '#0b5b13',
        'extendedProps' => [
            'startTime' => $row['start_time'],
            'endTime'   => $row['end_time'],
            'breakMin'  => $row['break_minutes'],
            'lunchFee'  => $row['meal_fee']
        ]
    ];
}

$total_hours = $total_worked_seconds / 3600;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Calculator Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scrollbar-gutter: stable; /* Keeps space for scrollbar so page doesn't jump */
        }

        body {
            font-family: Arial, sans-serif;
            background-color: rgba(238, 238, 238, 0.7);
            display: flex; 
        }

        .sidebar {
            width: 260px;
            height: 100vh;
            background: #f8f9fa;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            position: fixed;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .sidebar-icon {
            height: auto;
            display:flex;
            justify-content:center;           
            margin-bottom: 15px;     
        }

        .sidebar-icon img {
            height: auto%; 
            width: 60px;
        }

        .menu {
            list-style: none;
            padding: 0;
            margin-left: 13px;
            width: 200px;
        }

        .menu li {
            border-radius: 50px;
        }

        .menu li a {
            text-decoration: none;
            color: black;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 10px;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .menu li i {
            color: rgb(134, 134, 134);
            width: 5px;
            padding-right: 18px;
        }

        .menu li.active {
            border-radius: 10px;
            color: #A3D18A;
        }

        .menu a:hover,
        .menu a.active {
            color: #A3D18A;
        }

        .menu li.active i,
        .menu li:hover i {
            color: green;
            background-color: #d1eaa9;
        }

        .menu li.active a,
        .menu li:hover a {
            color: rgb(11, 91, 19);
            background-color: #d1eaa9;
        }
        
        .content {
            margin-left: 260px; 
            padding: 40px 65px 40px 65px;
            width: 100%;
            min-height: 100vh;
        }

        .header {
            margin-bottom: 20px;
            font-size: 1.8rem;
            color: #2c3e50;
            font-weight: bold;
            display: inline-flex; 
            gap: 25px;
        }

        .upper-part {
            margin: 20px 25px 0 0px;
            border-radius: 12px;
            margin-top: 20px;
            display:flex;
            justify:column;
            gap:20px;
            width:auto;
        }

        .card{
            background:white;
            padding:23px;
            border-radius: 20px;
            margin-bottom: 30px;
            
        }
        
        .card small {
            color: #7f8c8d;
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: bold;
            letter-spacing: 1px;
            display: block;
            margin-bottom: 5px;
        }

        .card h2 {
            color: #2c3e50 ; 
            font-size: 2rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        } */

        .calender-card {
            margin: 25px 25px; 
            background: white;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #C5E1B5;
            width: auto; 
        }

        #calendar {
            max-height: 600px;
            font-size: 0.9rem;
        }

        .calender-calender{
            padding:20px;
        }

        .modal-overlay {
            display: none;
            position: fixed; 
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 35px;
            border-radius: 20px;
            width: 100%;
            max-width: 400px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .close { position: absolute; right: 20px; top: 20px; font-size: 24px; cursor: pointer; color: #bdc3c7; }
        .close:hover { color: #2c3e50; }

        /* FORM UI */
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #34495e; font-size: 0.9rem; }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #dfe6e9;
            border-radius: 10px;
            font-size: 1rem;
            outline: none;
        }

        .form-group input:focus {
            border-color: #A3D18A;
        }

        .savechanges {
            width: 100%;
            padding: 14px;
            background: #A3D18A;
            color: #0b5b13;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.2s;
        }

        .savechanges:hover {
            background: #92c478;
        }
        
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-icon">
            <img src="saving-icon.png">
        </div>
        <ul class="menu">
            <li><a href="Dashboard.php"><i class="fa-solid fa-house"></i>Dashboard</a></li>
            <li class="active"><a href="Part-time Job.php"><i class="fa-solid fa-briefcase"></i>Part-time Jobs</a></li>
        </ul>
    </div>

    <div class="content">
        <h1 class="header"><a href='Part-time Job.php' style='text-decoration: none; color: inherit;'><i class="fa-solid fa-arrow-left"></i></a>Job Details</h1>
        <hr style="width: 100%; border: 0; border-top: 1px solid #ddd;">
        
        <div class="upper-part">
            <div class="card">
                <small>Total Earnings</small>
                <h2 id="total-earnings">RM <?php echo number_format($total_earnings, 2); ?></h2>
            </div>
            <div class="card">
                <small>Total Hours</small>
                <h2 id="total-hours"><?php echo number_format($total_hours, 2); ?> hrs</h2>
            </div>
        </div>

        <div class="calender-card">
            <div id="calendar"></div>
        </div>

        <div id="shiftModal" class="modal-overlay">
            <div class="modal-content">
                <span class="close" onclick="closeShiftModal()">&times;</span>
                <h2 id="modalDateTitle">Log Shift</h2>
                <br>
                <form action="" method="POST">
                    <input type="hidden" name="shift_id" id="modalShiftId">
                    <input type="hidden" name="work_date" id="selectedDate">
                    <input type="hidden" name="job_id" value="<?php echo $_GET['id']; ?>">

                    <div class="form-group">
                        <label>Start Time</label>
                        <input type="time" name="start_time" id="modalStartTime" required>
                    </div>
                    <div class="form-group">
                        <label>End Time</label>
                        <input type="time" name="end_time" id="modalEndTime" required>
                    </div>
                    <div class="form-group">
                        <label>Break Minutes</label>
                        <input type="number" name="break_minutes" id="modalBreak" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-utensils"></i> Lunch Fee (RM)</label>
                        <input type="number" name="meal_fee" id="modalmeal">
                    </div>
                    
                    <button class="savechanges" type="submit" name="save_shift">Save Changes</button>
                    
                    <button type="submit" name="delete_shift" id="deleteBtn" style="display:none; width:100%; margin-top:10px; background:#f86c5f; color:white; border:none; padding:10px; border-radius:12px; cursor:pointer;" onclick="return confirm('Delete this shift?')">Delete Shift</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Convert our PHP array into a JS array
        const shiftEvents = <?php echo json_encode($events); ?>;

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 'auto',
                selectable: true,
                events: shiftEvents,
                
                // Handle clicking an existing event (EDIT)
                eventClick: function(info) {
                    const shift = info.event;
                    const props = shift.extendedProps;
                    
                    document.getElementById("modalShiftId").value = shift.id;
                    document.getElementById("selectedDate").value = shift.startStr;
                    document.getElementById("modalStartTime").value = props.startTime;
                    document.getElementById("modalEndTime").value = props.endTime;
                    document.getElementById("modalBreak").value = props.breakMin;
                    document.getElementById("modalmeal").value = props.lunchFee;
                    
                    document.getElementById("modalDateTitle").innerText = "Edit Shift: " + shift.startStr;
                    document.getElementById("deleteBtn").style.display = "block"; // Show delete button
                    document.getElementById("shiftModal").style.display = "flex";
                },

                // Handle clicking an empty date (ADD)
                dateClick: function(info) {
                    document.getElementById("modalShiftId").value = ""; // Clear ID
                    document.getElementById("modalBreak").value = "0";
                    document.getElementById("deleteBtn").style.display = "none"; // Hide delete button
                    document.getElementById("modalmeal").value = "0.00";
                    openShiftModal(info.dateStr);
                }
            });
            calendar.render();
        });

        function openShiftModal(date) {
            document.getElementById("selectedDate").value = date;
            document.getElementById("modalDateTitle").innerText = "Log Shift: " + date;
            document.getElementById("shiftModal").style.display = "flex";
            // document.body.style.overflow = "hidden"; // Commented out to prevent "zoom"
        }

        function closeShiftModal() {
            document.getElementById("shiftModal").style.display = "none";
        }
    </script>
    

</body>
</html>