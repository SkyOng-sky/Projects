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
            margin-left: 260px; 
            padding: 40px;
            width: 100%;
            min-height: 100vh;
        }

        .add-job {
            display: inline-flex; 
            align-items: center;
            gap: 10px;
            background-color: #A3D18A;
            color: #0b5b13;
            padding: 10px 12px;
            border-radius: 12px;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            box-shadow: 0 4px 10px rgba(163, 209, 138, 0.3);
        }

        .add-job h3 {
            font-size: 1rem;
            font-weight: 600;
        }

        .add-job i {
            font-size: 1.1rem;
        }

        .add-job:hover {
            background-color: #92c478;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .add-button {
            display:flex;
            justify-content:flex-end;
            margin: 20px 25px 20px 0px;
        }

        /* Update your card to handle the rows properly */
        .card {
            display: flex;
            flex-direction: column;
            margin: 20px 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #C5E1B5;
            overflow: hidden; 
        }

        /* Define the job-item row style */
        .job-item {
            display: flex;
            justify-content: space-between; /* Pushes name to left, sections to right */
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
        }

        .job-item:last-child {
            border-bottom: none;
        }

        .job-item:hover {
            background-color: #f9fdf9;
        }

        .job-name {
            font-weight: bold;
            color: #2c3e50;
            flex-grow: 1; /* Ensures name stays on the left */
        }

        .job-right-section {
            display: flex;
            align-items: center;
            gap: 15px; /* Space between badge and trash icon */
        }

        .rate-badge {
            background-color: #d1eaa9;
            color: #0b5b13;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 1rem;
            display: flex;
            align-items: baseline; /* Keeps the RM and /hr aligned on the bottom line */
            gap: 4px;
        }

        .per-hour {
            font-size: 0.75rem; /* Smaller than the price */
            font-weight: normal; /* Not bold */
            opacity: 0.8; /* Slightly faded */
        }

        .delete-btn {
            color: #f86c5f;
            cursor: pointer;
            transition: transform 0.2s, color 0.2s;
        }

        .delete-btn:hover {
            color: #e74c3c;
            transform: scale(1.1);
        }

        .header {
            margin-left: 25px;
            margin-bottom: 20px;
            font-size: 1.8rem;
            color: #2c3e50;
            font-weight: bold;
        }

        .modal-overlay {
            display: none; 
            position: fixed; 
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5); 
            z-index: 9999; 
            justify-content: center; 
            align-items: center;
            background-color: rgba(0, 0, 0, 0.57);
            backdrop-filter: blur(5px);    
        }

        .modal-content {
            background: white;
            padding: 30px 30px 25px 30px;
            border-radius: 15px;
            width: 400px;
            position: relative;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }

        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #888;
        }

        .close:hover{
            color:black;
        }

        /* Form spacing */
        .form-group {
            margin-top: 25px;
            text-align: left;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .form-group small {
            display: block;
            margin-top: 8px;
            color: #777;
            font-size: 0.8rem;
            font-style: italic;
        }

        .savechanges {
            width: 100%;
            margin-top: 25px;
            padding: 12px;
            background-color: #4bcf04;
            color:white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
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
        <h1 class="header">Part-time Jobs</h1>
        <hr style="width: 96%; margin-left:25px;">
        <div class="add-button">
            <div class="add-job" onclick="openAddModal()">
                <i class="fa-solid fa-plus"></i><h3>Add New Job</h3>
            </div>
        </div>

        <?php
            $con = mysqli_connect("localhost", "root", "", "part-time calculator");

            if (mysqli_connect_errno()) {
                echo "Failed to connect to MySQL: " . mysqli_connect_error();
            }

            $sql = "SELECT * FROM job_profiles";
            $result = mysqli_query($con, $sql);

            if (!$result) {
                echo "Error fetching data: " . mysqli_error($con);
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
                $name = mysqli_real_escape_string($con, $_POST['job_name']);
                $hour_rate = mysqli_real_escape_string($con, $_POST['hour_rate']);
                $ot_rate = mysqli_real_escape_string($con, $_POST['ot_rate']);

                if (empty($name) || empty($hour_rate)){
                    echo "<script>alert('Please fill in all required fields.');</script>";
                } else {
                    $sql = "INSERT INTO job_profiles (job_name, hour_rate, ot_rate, user_id) 
                            VALUES ('$name', '$hour_rate', '$ot_rate', '$user_id')";
                    
                    if ($con->query($sql)) {
                        echo "<script>alert('New Job added successfully.'); window.location.href='Part-time Job.php';</script>";
                    } else {
                        echo "<script>alert('Error adding job: " . mysqli_error($con) . "');</script>";
                    }
                }          
            }

            if (isset($_GET['delete_id'])) {
                $delete_id = $_GET['delete_id'];
                $current_user = $_SESSION['user_id'];
                $sql = "DELETE FROM job_profiles WHERE job_id = '$delete_id' AND user_id = '$current_user'";

                if ($con->query($sql)) {
                    echo "<script>window.location.href='Part-time Job.php';</script>";
                    exit();
                }
            }
        ?>
        <div class="card">
            <?php
            $sql = "SELECT job_id, job_name, hour_rate FROM job_profiles WHERE user_id = '$user_id'";
            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <div class="job-item" onclick="window.location.href='JobDetails.php?id=<?php echo $row['job_id']; ?>'">
                        <div class="job-name">
                            <?php echo htmlspecialchars($row['job_name']); ?>
                        </div>
                        
                        <div class="job-right-section">
                            <span class="rate-badge">
                                RM <?php echo number_format($row['hour_rate'], 2); ?>
                                <span class="per-hour">/hr</span>
                            </span>
                            <i class="fa-solid fa-trash delete-btn" onclick="event.stopPropagation(); if(confirm('Delete this job?')) window.location.href='Part-time Job.php?delete_id=<?php echo $row['job_id']; ?>'"></i>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p style='color: #888;'>No jobs added yet. Use the button above to start.</p>";
            }
            ?>
        </div>
    </div>

    <div id="jobModal" class="modal-overlay">
    <div class="modal-content">
        <form id="addnewjob" method="POST"  class="form-group">
            <div class="modal-header">
                <h2>Add New Job</h2>
                <span class="close" onclick="closeAddModal()">&times;</span>

                <div class="form-group">
                    <label>Job Name</label>
                    <input type="text" id="job-name" name="job_name" oninput="validateNameInput(event)" required>
                </div>
                <div class="form-group">
                    <label>Hourly Rate (RM)</label>
                    <input type="number" step="1" name="hour_rate" onkeypress="blockScientificNotation(event)" required>
                </div>
                <div class="form-group">
                    <label>Overtime Rate (RM)</label>
                    <input type="number" step="1" name="ot_rate" placeholder="Optional" pattern="[0-9\-]+" onkeypress="blockScientificNotation(event)">
                    <small>Leave 0 if no overtime</small>
                </div>

                <button class="savechanges" type="submit" name="submit">Save Job</button>
                
            </div>
        </form>

    <script>
        function openAddModal(){
            document.getElementById("jobModal").style.display = "flex";
            document.body.style.overflow = "hidden";
        }

        function closeAddModal() {
            document.getElementById("jobModal").style.display = "none";
            document.body.style.overflow = "auto";
        }

        window.onclick = function(event) {
            const modal = document.getElementById("jobModal");
            if (event.target == modal) {
                closeAddModal();
            }
        }

        function validateNameInput(event) {
            const input = event.target;
            const regex = /^[A-Za-z0-9\s'\-.,/]*$/; 

            if (!regex.test(input.value)) {
                input.value = input.value.slice(0, -1);
            }
        }

        function blockScientificNotation(event) {
            if (["e", "E", "+", "-"].includes(event.key)) {
                event.preventDefault();
            }
        }

        
    </script>

</body>
</html>