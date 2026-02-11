<?php
session_start();
include('config.php');

// Ensure the user is logged in as a normal user
if ($_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

// Check if the tutor ID is provided
if (!isset($_POST['tutor_id'])) {
    header("Location: user_dashboard.php");
    exit();
}

$tutor_id = $_POST['tutor_id'];

// Fetch tutor details
$sql = "SELECT u.name, u.surname, u.email, u.phone, t.price, t.modules, t.weekdays, t.sessions 
        FROM users u JOIN tutors_profile t ON u.id = t.tutor_id WHERE u.id = '$tutor_id'";
$result = $conn->query($sql);
$tutor = $result->fetch_assoc();

// Check for active booking codes
$user_id = $_SESSION['user_id'];
$sql_active = "SELECT COUNT(*) as active_count FROM appointments WHERE user_id = '$user_id' AND status = 'active'";
$result_active = $conn->query($sql_active);
$active = $result_active->fetch_assoc()['active_count'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pay_now'])) {
    // Confirm booking if there are active codes
    if ($active > 0) {
        echo "<script>
            if (confirm('You have active bookings. Are you sure you want to book another appointment?')) {
                document.getElementById('paymentForm').submit();
            } else {
                window.location.href = 'user_dashboard.php';
            }
        </script>";
    } else {
        // Generate a random booking code for the appointment
        $code = strtoupper(substr(md5(time()), 0, 8));
        $amount = $tutor['price'];

        // Create a new appointment
        $sql_appointment = "INSERT INTO appointments (tutor_id, user_id, code, amount, modules, weekdays, sessions, tutor_name, tutor_email, tutor_phone, hours_per_session) 
                            VALUES ('$tutor_id', '$user_id', '$code', '$amount', '{$tutor['modules']}', '{$tutor['weekdays']}', '{$tutor['sessions']}', '{$tutor['name']}', '{$tutor['email']}', '{$tutor['phone']}', '1')"; // Adjust hours_per_session as needed

        if ($conn->query($sql_appointment) === TRUE) {
            echo "<div class='confirmation-message'>Appointment booked successfully! Here is your booking code: <strong>$code</strong></div>";
        } else {
            echo "<div class='error-message'>Error: " . $conn->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('background.jpg');
            background-size: cover;
            margin: 0;
            padding: 0;
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
        h1 {
            text-align: center;
            margin-top: 20px;
        }
        .tutor-details {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            margin: 20px auto;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-width: 400px;
        }
        form {
            display: flex;
            flex-direction: column;
            max-width: 400px;
            margin: 20px auto;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        label {
            margin-top: 15px;
        }
        input {
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .input-container {
            position: relative;
        }
        .input-container i {
            position: absolute;
            left: 10px;
            top: 10px;
            color: #007bff;
            margin-right: 10px; /* Add space between icon and input */
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            padding: 10px;
            margin-top: 20px; /* Add margin above button */
        }
        button:hover {
            background-color: #0056b3;
        }
        .confirmation-message, .error-message {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }
        .error-message {
            color: red;
        }
    </style>
</head>
<body>

<header>
    <img src="logo1.png" alt="Logo">
    <nav>
        <a href="user_dashboard.php">Dashboard</a>
        <a href="profile.php">Profile</a>
        <a href="appointments.php">Appointments</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<h1>Payment for <?php echo htmlspecialchars($tutor['name'] . ' ' . $tutor['surname']); ?></h1>

<div class="tutor-details">
    <p><strong>Name:</strong> <?php echo htmlspecialchars($tutor['name'] . ' ' . $tutor['surname']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($tutor['email']); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($tutor['phone']); ?></p>
    <p><strong>Price:</strong> R<?php echo number_format($tutor['price'], 2); ?></p>
    <p><strong>Modules:</strong> <?php echo htmlspecialchars($tutor['modules']); ?></p>
    <p><strong>Weekdays:</strong> <?php echo htmlspecialchars($tutor['weekdays']); ?></p>
    <p><strong>Sessions:</strong> <?php echo htmlspecialchars($tutor['sessions']); ?></p>
</div>

<form id="paymentForm" method="POST">
    <input type="hidden" name="tutor_id" value="<?php echo htmlspecialchars($tutor_id); ?>">
    
    <div class="input-container">
        <i class="fas fa-credit-card"></i><br><br>
        <label>Card Number:</label>
        <input type="text" name="card_number" required placeholder="**** **** **** ****">
    </div>
    
    <div class="input-container">
        <i class="fas fa-calendar-alt"></i><br><br>
        <label>Expiry Date:</label>
        <input type="text" name="expiry_date" required placeholder="MM/YY">
    </div>
    
    <div class="input-container"><br><br>
        <i class="fas fa-lock"></i>
        <label>CVV:</label>
        <input type="text" name="cvv" required placeholder="123">
    </div>
    
    <button type="submit" name="pay_now">Pay Now</button>
</form>

<?php if ($active > 0): ?>
    <div class="confirmation-message">
        You have <strong><?php echo $active; ?></strong> active bookings. Are you sure you want to book another appointment?
    </div>
<?php endif; ?>

</body>
</html>
