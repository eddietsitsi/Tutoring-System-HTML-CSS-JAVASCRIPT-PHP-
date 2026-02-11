<?php
session_start();
include('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user by email
    $sql = "SELECT id, password, role FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $row['password'])) {
            // Store user details in session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];

            // Redirect based on role
            if ($row['role'] == 'tutor') {
                header("Location: tutor_dashboard.php");
            } elseif ($row['role'] == 'user') {
                header("Location: user_dashboard.php");
            }
            exit();
        } else {
            echo "<p style='color:red;'>Invalid Password!</p>";
        }
    } else {
        echo "<p style='color:red;'>No user found!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-image: url('background.jpg'); /* Add your background image here */
            background-size: cover;
            background-position: center;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }
        .login-container {
            background: rgba(0, 0, 0, 0.7); /* Semi-transparent background */
            padding: 40px; /* Increased padding for a larger container */
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            max-width: 500px; /* Increased max width */
            width: 100%;
            text-align: center;
        }
        h1 {
            color: white;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #f5f5f5;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: none;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #218838;
        }
        .links {
            margin-top: 20px;
            color: #f5f5f5;
        }
        .links a {
            color: #f5f5f5;
            text-decoration: none;
            transition: text-decoration 0.3s;
        }
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h1>Login</h1>
    <form method="POST">
        <label for="email">Email:</label>
        <input type="email" name="email" required>
        
        <label for="password">Password:</label>
        <input type="password" name="password" required>
        
        <button type="submit">Login</button>
        
        <div class="links">
            <p><a href="forgot_password.php">Forgot Password?</a></p>
            <p><a href="register.php">Register</a></p>
        </div>
    </form>
</div>

</body>
</html>
