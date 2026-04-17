<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "part-time calculator");
if(mysqli_connect_errno()){
    echo "Failed to connect to MySQL:".mysqli_connect_error();
}

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['loginUsername']);
    $password = mysqli_real_escape_string($conn, $_POST['loginPW']);

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['username'] = $row['username'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid Credentials!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Part-time Salary Calculator - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background-color: #f8faf9;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-main {
            width: 100%;
            max-width: 380px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 50px;
        }

        .middlebox {
            background-color:white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 380px;
            border: 3px solid #2ecc71;
        }

        .login-image {
            height: auto;
            display:flex;
            justify-content:center;           
            margin-bottom: 20px;     
        }

        .login-image img {
            height: auto%; 
            width: 250px;
        }
        
        .loginword {
            text-align: center;
            margin-bottom: 30px;
        }

        .loginword h1 {
            color: #27ae60; 
            font-size: 1.8rem;
            margin-bottom: 8px;
        }

        .loginword p {
            color: #666;
            font-size: 0.9rem;
        }

        .input-container {
            margin-bottom: 20px;
        }

        .username, .password {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #444;
            font-size: 0.85rem;
            font-weight:bold;
        }

        .inputFields {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
        }

        /* Green glow on click */
        .inputFields:focus {
            border-color: #2ecc71;
            background-color: #f9fffb;
        }

        /* Hide placeholder when user clicks/focuses */
        .inputFields:focus::placeholder {
            color: transparent;
        }

        .login-btn {
            width: 100%;
            padding: 13px;
            background-color: #2ecc71; 
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 10px;
        }

        .login-btn:hover {
            background-color: #27ae60; 
        }
    </style>
</head>
<body>
    <div class="login-main">
        <div class="login-image">
            <img src="LoginPageImage.png">
        </div>
        <div class="middlebox">       
            <div class="loginword">
                <h1>Login</h1>
                <p>Access your salary calculator</p>
            </div>

            <form id="login" action="" method="post">
                <div class="input-container">
                    <label for="user" class="username">Username</label>
                    <input type="text" id="user" name="loginUsername" placeholder="Enter your username" class="inputFields" required>
                </div>
                <div class="input-container" style="position: relative;">
                    <label for="pass" class="password">Password</label>
                    <input type="password" id="pass" name="loginPW" placeholder="Enter your password" class="inputFields" required>
                    
                    <span id="togglePassword" style="position: absolute; right: 15px; top: 38px; cursor: pointer;">
                        <i class="fa-solid fa-eye-slash" id="eyeIcon"></i>
                    </span>
                </div>
                <button type="submit" name="login" class="login-btn">Login</button>
            </form>
        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#pass');
        const eyeIcon = document.querySelector('#eyeIcon');

        togglePassword.addEventListener('click', function () {
            // Toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            if (type === 'password') {
                eyeIcon.classList.add('fa-eye-slash');
                eyeIcon.classList.remove('fa-eye');
            } else {
                eyeIcon.classList.add('fa-eye');
                eyeIcon.classList.remove('fa-eye-slash');
            }
        });
    </script>
</body>
</html>