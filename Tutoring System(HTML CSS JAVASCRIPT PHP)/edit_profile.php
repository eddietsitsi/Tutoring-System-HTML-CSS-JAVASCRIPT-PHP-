<?php
session_start();
include('config.php');

// Ensure the user is logged in as a tutor
if ($_SESSION['role'] != 'tutor') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if user_id is set
if (!isset($user_id)) {
    die("User ID not set in session.");
}

// Fetch user details
$sql = "SELECT id, name, surname, email, phone FROM users WHERE id = '$user_id'";
$result = $conn->query($sql);

// Check if the query was successful
if (!$result) {
    die("Error fetching user details: " . $conn->error);
}

// Check if any user details were returned
if ($result->num_rows === 0) {
    die("No user found with this ID.");
}

$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_details'])) {
        // Update user details
        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];

        $sql_update = "UPDATE users SET name = '$name', surname = '$surname', email = '$email', phone = '$phone' WHERE id = '$user_id'";
        if ($conn->query($sql_update) === TRUE) {
            $message = "Profile details updated successfully!";
        } else {
            $message = "Error: " . $conn->error;
        }
    }

    if (isset($_POST['change_password'])) {
        // Change password
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $sql_change_password = "UPDATE users SET password = '$new_password' WHERE id = '$user_id'";
        if ($conn->query($sql_change_password) === TRUE) {
            $message = "Password changed successfully!";
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutor Connect - Edit Profile</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link your CSS file here -->
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            background-image: url('background.jpg');
        }
        .container {
            width: 60%;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            width: 120px;
            height: auto;
        }
        .title {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        form table {
            width: 100%;
            border-collapse: collapse;
        }
        form table th, form table td {
            padding: 10px;
            text-align: left;
        }
        form input[type="text"], form input[type="email"], form input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        form button {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        form button:hover {
            background-color: #218838;
        }
        .message {
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
        }
        .back-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="logo-container">
        <img src="logo1.png" alt="Tutor Connect Logo" class="logo"> <!-- Add your logo here -->
    </div>
    <div class="title">Tutor Connect - Edit Profile</div>

    <?php if (isset($message)): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <table>
            <tr>
                <th>ID</th>
                <td><?php echo htmlspecialchars($user['id']); ?></td>
            </tr>
            <tr>
                <th>Name</th>
                <td><input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required></td>
            </tr>
            <tr>
                <th>Surname</th>
                <td><input type="text" name="surname" value="<?php echo htmlspecialchars($user['surname']); ?>" required></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required></td>
            </tr>
            <tr>
                <th>Phone</th>
                <td><input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required></td>
            </tr>
        </table>
        <button type="submit" name="update_details">Update Details</button>
    </form>

    <h2 style="margin-top: 30px;">Change Password</h2>
    <form method="POST">
        <table>
            <tr>
                <th>New Password</th>
                <td><input type="password" name="new_password" required></td>
            </tr>
        </table>
        <button type="submit" name="change_password">Change Password</button>
    </form>
      <!-- Back button to return to previous page -->
      <a href="tutor_dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</div>

</body>
</html>
