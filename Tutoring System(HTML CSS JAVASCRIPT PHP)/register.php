<?php
include('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $sql = "INSERT INTO users (name, surname, email, phone, password, role) 
            VALUES ('$name', '$surname', '$email', '$phone', '$password', '$role')";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green;'>Registration successful!</p>";
        header("Location: login.php");
        exit();
    } else {
        echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
        .register-container {
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
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: none;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        select {
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

<div class="register-container">
    <h1>Register</h1>
    <form method="POST">
        <label for="name">Name:</label>
        <input type="text" name="name" required>
        
        <label for="surname">Surname:</label>
        <input type="text" name="surname" required>
        
        <label for="email">Email:</label>
        <input type="email" name="email" required>
        
        <label for="phone">Phone:</label>
        <input type="text" name="phone" required>
        
        <label for="password">Password:</label>
        <input type="password" name="password" required>
        
        <label for="role">Role:</label>
        <select name="role" required>
            <option value="tutor">Tutor</option>
            <option value="user">User</option>
        </select>
        
        <button type="submit">Register</button>
        
        <div class="links">
            <p><a href="login.php">Already have an account? Login</a></p>
        </div>
    </form>
</div>

</body>
</html>
