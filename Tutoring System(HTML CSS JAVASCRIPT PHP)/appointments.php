<?php
session_start();
include('config.php');

// Ensure the user is logged in as a normal user
if ($_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all appointments for the logged-in user, ordered by created_at descending
$sql = "SELECT * 
        FROM appointments 
        WHERE user_id = '$user_id'
        ORDER BY created_at DESC"; // Newest first
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Appointments</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('background.jpg');
            background-size: cover;
            background-position: center;
            margin: 0;
            padding: 0;
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
        h1 {
            text-align: center;
            color: #333;
            margin: 20px 0;
        }
        .appointments {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            margin: 20px auto;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-width: 600px;
        }
        .appointment {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        .appointment:last-child {
            border-bottom: none;
        }
        .appointment p {
            margin: 5px 0;
        }
        .status-used {
            color: #dc3545; /* Red for used code */
            font-weight: bold;
        }
        .status-active {
            color: #28a745; /* Green for active code */
            font-weight: bold;
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
        <a href="user_dashboard.php">User Dashboard</a>
        <a href="profile.php">Profile</a>
        <a href="appointments.php">Appointments</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<h1>Your Appointments</h1>

<div class="appointments">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($appointment = $result->fetch_assoc()): ?>
            <div class="appointment">
                <p><strong>Tutor Name:</strong> <?php echo htmlspecialchars($appointment['tutor_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($appointment['tutor_email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($appointment['tutor_phone']); ?></p>
                <p><strong>Booking Code:</strong> 
                    <?php echo htmlspecialchars($appointment['code']); ?> 
                    <span class="<?php echo $appointment['status'] == 'used' ? 'status-used' : 'status-active'; ?>">
                        (<?php echo ucfirst($appointment['status']); ?>)
                    </span>
                </p>
                <p><strong>Amount:</strong> R<?php echo number_format($appointment['amount'], 2); ?></p>
                <p><strong>Date:</strong> <?php echo date('Y-m-d H:i:s', strtotime($appointment['created_at'])); ?></p>
                <p><strong>Modules:</strong> <?php echo htmlspecialchars($appointment['modules']); ?></p>
                <p><strong>Weekdays:</strong> <?php echo htmlspecialchars($appointment['weekdays']); ?></p>
                <p><strong>Sessions:</strong> <?php echo htmlspecialchars($appointment['sessions']); ?></p>
                <p><strong>Hours per Session:</strong> <?php echo htmlspecialchars($appointment['hours_per_session']); ?></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No appointments found.</p>
    <?php endif; ?>
</div>

</body>
</html>
