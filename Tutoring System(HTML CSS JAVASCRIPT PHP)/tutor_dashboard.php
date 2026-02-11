<?php
session_start();
include('config.php');

// Ensure the user is logged in as a tutor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'tutor') {
    header("Location: login.php");
    exit();
}

$tutor_id = $_SESSION['user_id'];

// Initialize variables for feedback messages
$profileMessage = '';
$redeemMessage = '';
$profilePicDir = 'profile_pics/';

// Handle profile creation, update, and code redemption
if ($_SERVER['REQUEST_METHOD'] == 'POST') {$hours_per_session = isset($_POST['hours_per_session']) ? $_POST['hours_per_session'] : null;
    if (isset($_POST['create_profile']) || isset($_POST['edit_profile'])) {
        // Initialize profile picture variable
        $profile_pic = null;

        // Handle image upload
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
            $target_file = $profilePicDir . basename($_FILES["profile_pic"]["name"]);
            $check = getimagesize($_FILES["profile_pic"]["tmp_name"]);
            if ($check !== false) {
                if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                    $profile_pic = basename($_FILES["profile_pic"]["name"]);
                } else {
                    $profileMessage = "Error uploading your profile picture.";
                }
            } else {
                $profileMessage = "File is not an image.";
            }
        }

        // Get form input
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $modules = $_POST['modules'];
        $weekdays = $_POST['weekdays'];
        $price = $_POST['price'];
        $sessions = $_POST['sessions'];
        $hours_per_session = $_POST['hours_per_session']; // New field
        $accepting_appointments = isset($_POST['accepting_appointments']) ? 1 : 0;

        if (isset($_POST['create_profile'])) {
            // Insert the new profile into the database
            $sql = "INSERT INTO tutors_profile (tutor_id, name, email, phone, modules, weekdays, price, sessions, hours_per_session, accepting_appointments, profile_pic)
                    VALUES ('$tutor_id', '$name', '$email', '$phone', '$modules', '$weekdays', '$price', '$sessions', '$hours_per_session', '$accepting_appointments', '$profile_pic')";
        } elseif (isset($_POST['edit_profile'])) {
            // Update the existing profile in the database
            $sql = "UPDATE tutors_profile SET name = '$name', email = '$email', phone = '$phone', modules = '$modules', weekdays = '$weekdays', 
                    price = '$price', sessions = '$sessions', hours_per_session = '$hours_per_session', accepting_appointments = '$accepting_appointments'";
            if ($profile_pic) {
                $sql .= ", profile_pic = '$profile_pic'";
            }
            $sql .= " WHERE tutor_id = '$tutor_id'";
        }

        if ($conn->query($sql)) {
            $profileMessage = isset($_POST['create_profile']) ? "Profile created successfully!" : "Profile updated successfully!";
        } else {
            $profileMessage = "Error: " . $conn->error;
        }
    } elseif (isset($_POST['redeem_code'])) {
        // Handle code redemption and update balance
        $code = $_POST['code'];

        // Check if the code exists and fetch the appointment details
        $sql = "SELECT amount FROM appointments WHERE code = '$code' AND tutor_id = '$tutor_id' AND status = 'pending'";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $appointment = $result->fetch_assoc();
            $amount = $appointment['amount'];

            // Update appointment status
            $update_sql = "UPDATE appointments SET status = 'completed' WHERE code = '$code' AND tutor_id = '$tutor_id'";
            if ($conn->query($update_sql)) {
                // Update balance in the earnings table
                $sql_balance = "INSERT INTO earnings (tutor_id, balance) VALUES ('$tutor_id', $amount) 
                                ON DUPLICATE KEY UPDATE balance = balance + $amount";
                if ($conn->query($sql_balance)) {
                    $redeemMessage = "Code redeemed successfully, balance updated!";
                } else {
                    $redeemMessage = "Error updating balance: " . $conn->error;
                }
            } else {
                $redeemMessage = "Error updating appointment status.";
            }
        } else {
            $redeemMessage = "Invalid or already redeemed code!";
        }
    }
}

// Fetch tutor profile
$sql = "SELECT * FROM tutors_profile WHERE tutor_id = '$tutor_id'";
$result = $conn->query($sql);
$profile = $result->fetch_assoc();

// Fetch the tutor's balance
$sql_balance = "SELECT balance FROM earnings WHERE tutor_id = '$tutor_id'";
$result_balance = $conn->query($sql_balance);
$balance = ($result_balance && $result_balance->num_rows > 0) ? $result_balance->fetch_assoc()['balance'] : 0;


// Fetch the tutor's appointments along with student details
$sql_appointments = "SELECT a.id, u.name, u.surname, u.email, u.phone, a.code, a.status, a.amount, a.modules, a.weekdays, a.hours_per_session,a.sessions
                     FROM appointments a
                     JOIN users u ON a.user_id = u.id
                     WHERE a.tutor_id = '$tutor_id'
                     ORDER BY a.created_at DESC";
$result_appointments = $conn->query($sql_appointments);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutor Dashboard</title>
    <style>
   body {
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 0;
    background-image: url('background.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    color: #333;
    line-height: 1.6;
}

h1, h2 {
    color: #222;
    text-align: center;
    margin: 20px 0;
    font-family: 'Helvetica Neue', sans-serif;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
}

h1 {
    font-size: 2.5em;
    letter-spacing: 1px;
}

h2 {
    font-size: 2em;
    font-weight: 400;
}

.container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 30px;
    background-color: rgba(255, 255, 255, 0.95);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    border-radius: 12px;
}

/* Logo styling */
.logo-container {
    text-align: center;
    margin-bottom: 20px;
}

.logo {
    width: 120px;
    height: auto;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.logo:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 30px 0;
    font-size: 1rem;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

th, td {
    padding: 15px;
    border: 1px solid #ddd;
    text-align: left;
    transition: background-color 0.3s ease;
}

th {
    background-color: #007bff;
    color: #fff;
    text-transform: uppercase;
}

td {
    background-color: #f9f9f9;
}

th:hover, td:hover {
    background-color: #f1f1f1;
}

.btn {
    display: inline-block;
    padding: 12px 28px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-family: 'Helvetica Neue', sans-serif;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.3s ease;
    text-align: center;
    text-decoration: none;
    margin-top: 10px;
}

.btn:hover {
    background-color: #0056b3;
    transform: scale(1.02);
}

.btn-danger {
    background-color: #dc3545;
}

.btn-danger:hover {
    background-color: #c82333;
    transform: scale(1.02);
}

.profile-img {
    max-width: 150px;
    border-radius: 50%;
    margin-right: 20px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
}

.profile-section {
    display: flex;
    align-items: center;
    margin-bottom: 40px;
    background-color: rgba(255, 255, 255, 0.8);
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.profile-section div {
    max-width: 600px;
}

.balance {
    font-weight: bold;
    font-size: 1.5em;
    color: #007bff;
}

a {
    color: #007bff;
    text-decoration: none;
    transition: color 0.3s ease;
}

a:hover {
    color: #0056b3;
    text-decoration: underline;
}

.message {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: bold;
    font-size: 1.1rem;
}

.success {
    background-color: #d4edda;
    color: #155724;
}

.error {
    background-color: #f8d7da;
    color: #721c24;
}

.edit-form {
    display: none; /* Hidden until edit is clicked */
    margin-top: 20px;
    background-color: rgba(255, 255, 255, 0.9);
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.appointments-table th, .appointments-table td {
    text-align: center;
}

.appointments-table th {
    background-color: #28a745;
}

.appointments-table tr:nth-child(even) {
    background-color: #f9f9f9;
}

.appointments-table td {
    font-weight: 400;
    background-color: #fff;
}

@media screen and (max-width: 768px) {
    body {
        padding: 10px;
    }

    .container {
        padding: 20px;
    }

    h1 {
        font-size: 2em;
    }

    h2 {
        font-size: 1.8em;
    }

    .profile-img {
        max-width: 120px;
    }

    .appointments-table th, .appointments-table td {
        padding: 10px;
    }

    .btn {
        font-size: 0.9rem;
        padding: 10px 20px;
    }
}.logo-container {
    text-align: center;
    margin-bottom: 20px;
}

.logo {
    width: 80px; /* Adjust size as needed */
    height: auto;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.logo-text {
    display: block; /* Make the text a block element */
    font-size: 1.5em; /* Adjust font size as needed */
    margin-top: 10px; /* Space between logo and text */
    color: #333; /* Adjust text color as needed */
    font-weight: bold; /* Make it bold for better visibility */
}


</style>


    <script>
        function toggleEditForm() {
            var form = document.getElementById('edit-form');
            form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
        }
    </script>
</head>
<body><div class="logo-container">
    <img src="logo1.png" alt="Logo" class="logo">
    <span class="logo-text">Tutor Connect</span>
</div>

    <div class="container">
        <h1>Welcome to Tutor Dashboard</h1>
        <a href="logout.php" class="btn btn-danger">Logout</a>
        <h2>Update Login Details</h2>
        <a href="edit_profile.php" class="btn"> Update Login Details</a>
        <!-- Profile Section -->
        <?php if (!$profile): ?>
            <h2>Create Profile</h2>
            <?php if ($profileMessage): ?>
                <div class="message <?php echo strpos($profileMessage
                , 'Error') === false ? 'success' : 'error'; ?>">
                    <?php echo $profileMessage; ?>
                </div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="create_profile">
                <label>Name: <input type="text" name="name" required></label><br>
                <label>Email: <input type="email" name="email" required></label><br>
                <label>Phone Number: <input type="text" name="phone" required></label><br>
                <label>Modules: <input type="text" name="modules" required></label><br>
                <label>Weekdays: <input type="text" name="weekdays" required></label><br>
                <label>Price : <input type="number" name="price" required></label><br>
                <label>Hours per Session: <input type="text" name="hours_per_session" required></label><br>

                <label>Hours per Session: <input type="number" name="hours_per_session" required></label><br>
                <label>Accepting Appointments: <input type="checkbox" name="accepting_appointments"></label><br>
                <label>Profile Picture: <input type="file" name="profile_pic"></label><br>
                <button type="submit" class="btn">Create Profile</button>
            </form>
        <?php else: ?>
            <h2>Your Profile</h2>
            <div class="profile-section">
                <?php if ($profile['profile_pic']): ?>
                    <img src="<?php echo $profilePicDir . $profile['profile_pic']; ?>" alt="Profile Picture" class="profile-img">
                <?php endif; ?>
                <div>
                    <p>Name: <?php echo $profile['name']; ?></p>
                    <p>Email: <?php echo $profile['email']; ?></p>
                    <p>Phone: <?php echo $profile['phone']; ?></p>
                    <p>Modules: <?php echo $profile['modules']; ?></p>
                    <p>Weekdays: <?php echo $profile['weekdays']; ?></p>
                    <p>Price: R<?php echo number_format($profile['price'], 2); ?></p>
                    <p>Sessions: <?php echo $profile['sessions']; ?></p>
                    <p>Hours per Session: <?php echo $profile['hours_per_session']; ?></p>
                    <p>Accepting Appointments: <?php echo $profile['accepting_appointments'] ? 'Yes' : 'No'; ?></p>
                    <p class="balance">Current Balance: R<?php echo number_format($balance, 2); ?></p>
                    <button class="btn" onclick="toggleEditForm()">Edit Profile</button>
                </div>
            </div>
            <div id="edit-form" class="edit-form">
                <h3>Edit Profile</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="edit_profile">
                    <label>Name: <input type="text" name="name" value="<?php echo $profile['name']; ?>" required></label><br>
                    <label>Email: <input type="email" name="email" value="<?php echo $profile['email']; ?>" required></label><br>
                    <label>Phone Number: <input type="text" name="phone" value="<?php echo $profile['phone']; ?>" required></label><br>
                    <label>Modules: <input type="text" name="modules" value="<?php echo $profile['modules']; ?>" required></label><br>
                    <label>Weekdays: <input type="text" name="weekdays" value="<?php echo $profile['weekdays']; ?>" required></label><br>
                    <label>Price : <input type="number" name="price" value="<?php echo $profile['price']; ?>" required></label><br>
                    <label>Number of Sessions: <input type="number" name="sessions" value="<?php echo $profile['sessions']; ?>" required></label><br>
                    <label>Hours per Session: <input type="text" name="hours_per_session" required></label><br>
                    <label>Accepting Appointments: <input type="checkbox" name="accepting_appointments" <?php echo $profile['accepting_appointments'] ? 'checked' : ''; ?>></label><br>
                    <label>Profile Picture: <input type="file" name="profile_pic"></label><br>
                    <button type="submit" class="btn">Update Profile</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Redeem Code Section -->
        <h2>Redeem Code</h2>
        <?php if ($redeemMessage): ?>
            <div class="message <?php echo strpos($redeemMessage, 'Error') === false ? 'success' : 'error'; ?>">
                <?php echo $redeemMessage; ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="redeem_code">
            <label>Code: <input type="text" name="code" required></label><br>
            <button type="submit" class="btn">Redeem Code</button>
        </form>

  <!-- Appointments Section -->
<h2>Your Appointments</h2>
<table class="appointments-table">
    <tr>
        <th>Appointment ID</th>
        <th>Student Name</th>
        <th>Email</th>
        <th>Phone Number</th>
        <th>Modules</th>
        <th>Weekdays</th>
        <th>Sessions</th>
        <th>Hours per Session</th>
        <th>Status</th>
        <th>Amount</th>
    </tr>
    <?php while ($appointment = $result_appointments->fetch_assoc()): ?>
        <tr>
            <td><?php echo $appointment['id']; ?></td> <!-- Display appointment ID -->
            <td><?php echo htmlspecialchars($appointment['name'] . ' ' . htmlspecialchars($appointment['surname'])); ?></td>
            <td><?php echo htmlspecialchars($appointment['email']); ?></td>
            <td><?php echo htmlspecialchars($appointment['phone']); ?></td>
            <td><?php echo htmlspecialchars($appointment['modules']); ?></td> <!-- Display modules -->
            <td><?php echo htmlspecialchars($appointment['weekdays']); ?></td>
            <td><?php echo htmlspecialchars($appointment['sessions']); ?></td> <!-- Display weekdays -->
            <td><?php echo htmlspecialchars($appointment['hours_per_session']); ?></td> <!-- Display hours per session -->
            <td><?php echo htmlspecialchars($appointment['status']); ?></td> <!-- Display appointment status -->
            <td>R<?php echo number_format($appointment['amount'], 2); ?></td> <!-- Display amount -->
        </tr>
    <?php endwhile; ?>
</table>

        <!-- Withdraw Button -->
        <h2>Withdraw Funds</h2>
        <a href="withdraw.php" class="btn">Withdraw</a>
    </div>
</body>
</html>
