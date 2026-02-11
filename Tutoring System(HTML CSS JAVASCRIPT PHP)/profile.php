<?php
session_start();
include('config.php');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user profile details
$sql = "SELECT name, surname, email, phone FROM users WHERE id = '$user_id'";
$result = $conn->query($sql);

// Check if the query was successful
if (!$result) {
    die("Error in SQL query: " . $conn->error);
}

$user = $result->fetch_assoc(); // Fetch user data

// Handle profile update
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $update_profile_sql = "UPDATE users SET name = '$name', surname = '$surname', email = '$email', phone = '$phone' WHERE id = '$user_id'";

    if ($conn->query($update_profile_sql)) {
        $success_message = "Profile updated successfully!";
        // Refresh user data
        $user['name'] = $name;
        $user['surname'] = $surname;
        $user['email'] = $email;
        $user['phone'] = $phone;
    } else {
        $error_message = "Error updating profile: " . $conn->error;
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if new passwords match
    if ($new_password === $confirm_password) {
        // Hash the new password and update it
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_password = "UPDATE users SET password = '$hashed_password' WHERE id = '$user_id'";
        if ($conn->query($update_password)) {
            $success_message = "Password successfully changed!";
        } else {
            $error_message = "Error updating password: " . $conn->error;
        }
    } else {
        $error_message = "New passwords do not match.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutor Connect - Profile</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-image: url('background.jpg');
            background-size: cover;
            background-position: center;
            color: #333;
        }
        header {
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            font-size: 2.5rem;
            font-weight: bold;
        }
        .logo-container {
            display: flex;
            align-items: center;
        }
        header img {
            width: 50px;
            margin-right: 15px;
        }
        nav {
            display: flex;
            align-items: center;
        }
        nav a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            font-size: 1.2rem;
        }
        .profile-container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 40px;
            margin: 50px auto;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            width: 80%;
            max-width: 600px;
            transition: all 0.3s ease;
        }
        h1 {
            text-align: center;
            color: #333;
            font-size: 2rem;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 1.2rem;
            margin-bottom: 5px;
            color: #555;
        }
        .form-group input {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            width: 100%;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            border-color: #007bff;
            outline: none;
        }
        button {
            padding: 12px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.2rem;
            width: 100%;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            color: green;
            font-size: 1.2rem;
            text-align: center;
        }
        .error-message {
            color: red;
            font-size: 1.2rem;
            text-align: center;
        }
    </style>
</head>
<body>

<header>
    <div class="logo-container">
        <img src="logo1.png" alt="Logo">
        <span>Tutor Connect</span>
    </div>
    <nav>
        <a href="user_dashboard.php">Dashboard</a>
        <a href="profile.php">Profile</a>
        <a href="appointments.php">Appointments</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="profile-container">
    <h1>User Profile</h1>
    <div class="profile-info">
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="surname">Surname:</label>
                <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($user['surname']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
            </div>
            <button type="submit" name="update_profile">Update Profile</button>
        </form>
    </div>

    <h2>Change Password</h2>
    <?php if (!empty($success_message)): ?>
        <p class="message"><?php echo $success_message; ?></p>
    <?php elseif (!empty($error_message)): ?>
        <p class="error-message"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <form class="form-section" method="POST" action="">
        <div class="form-group">
            <input type="password" name="new_password" placeholder="New Password" required>
        </div>
        <div class="form-group">
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
        </div>
        <button type="submit" name="change_password">Change Password</button>
    </form>
</div>

</body>
</html>
