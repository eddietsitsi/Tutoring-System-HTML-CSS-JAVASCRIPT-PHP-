<?php
session_start();
include('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if the email exists in the database
    $sql = "SELECT id FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Here you would typically send an email with a password reset link
        echo "<p style='color:green;'>Password reset link sent to your email!</p>";
    } else {
        echo "<p style='color:red;'>No user found with that email!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-image: url('background.jpg');
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
        .forgot-password-container {
            background: rgba(0, 0, 0, 0.7);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            max-width: 400px;
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
        input[type="email"] {
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

<div class="forgot-password-container">
    <h1>Forgot Password</h1>
    <form method="POST">
        <label for="email">Enter your Email:</label>
        <input type="email" name="email" required>
        
        <button type="submit">Send Reset Link</button>
        
        <div class="links">
            <p><a href="login.php">Back to Login</a></p>
        </div>
    </form>
</div>

</body>
</html>
