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

$total_earnings = 0;
$total_hours = 0;

$all_shifts = mysqli_query($conn, "SELECT shifts.*, job_profiles.hour_rate FROM shifts JOIN job_profiles ON shifts.job_id = job_profiles.job_id WHERE job_profiles.user_id = '$user_id'");

while ($row = mysqli_fetch_assoc($all_shifts)) {
    $start = strtotime($row['start_time']);
    $end = strtotime($row['end_time']);
    if ($end < $start) $end += 86400;

    $hours = ($end - $start - ($row['break_minutes'] * 60)) / 3600;
    $total_hours += $hours;
    $total_earnings += ($hours * $row['hour_rate']) - $row['meal_fee'];
}

// Count how many different jobs you have
$job_count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM job_profiles WHERE user_id = '$user_id'");
$job_count = mysqli_fetch_assoc($job_count_query)['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Calculator Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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
        

        /* MAIN CONTENT AREA */
        .content {
            margin-left: 260px; /* Must match sidebar width */
            padding: 40px;
            width: 100%;
            min-height: 100vh;
        }

        .card {
            margin-left:25px;
            margin-right:25px;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-top: 20px;
        }

        .header {
            margin-left: 25px;
            margin-bottom: 20px;
            font-size: 1.8rem;
            color: #2c3e50;
            font-weight: bold;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin: 20px 25px;
        }

        .summary-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s ease;
        }

        .summary-card:hover {
            transform: translateY(-5px);
        }

        /* Icon Styling */
        .icon-box {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .icon-earnings { background: #eafaea; color: #2ecc71; }
        .icon-hours { background: #eaf4ff; color: #3498db; }
        .icon-jobs { background: #fff7e6; color: #f39c12; }

        /* Text Styling */
        .summary-info h3 {
            color: #95a5a6;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .summary-info p {
            font-size: 1.6rem;
            font-weight: 800;
            color: #2c3e50;
        }

    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-icon">
            <img src="saving-icon.png">
        </div>
        <ul class="menu">
            <li class="active"><a href="Dashboard.php"><i class="fa-solid fa-house"></i>Dashboard</a></li>
            <li><a href="Part-time Job.php"><i class="fa-solid fa-briefcase"></i>Part-time Jobs</a></li>
        </ul>
    </div>

    <div class="content">
        <h1 class="header">Welcome back!</h1>
        <hr style="width: 96%; margin-left:25px; border: 0; border-top: 1px solid #eee; margin-bottom: 20px;">

        <div class="summary-grid">
            <div class="summary-card">
                <div class="icon-box icon-earnings">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <div class="summary-info">
                    <h3>Total Net Pay</h3>
                    <p>RM <?php echo number_format($total_earnings, 2); ?></p>
                </div>
            </div>

            <div class="summary-card">
                <div class="icon-box icon-hours">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="summary-info">
                    <h3>Total Work Hours</h3>
                    <p><?php echo number_format($total_hours, 2); ?> hrs</p>
                </div>
            </div>

            <div class="summary-card">
                <div class="icon-box icon-jobs">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                <div class="summary-info">
                    <h3>Active Jobs</h3>
                    <p><?php echo $job_count; ?></p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>