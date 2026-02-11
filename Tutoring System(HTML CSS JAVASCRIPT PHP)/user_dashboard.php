<?php
session_start();
include('config.php');

// Ensure the user is logged in as a normal user
if ($_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle module search
$search_term = '';
if (isset($_POST['search_module'])) {
    $search_term = $_POST['module'];
}

// Fetch tutors matching the search term or all tutors if no search term
$sql = "SELECT u.id AS tutor_id, u.name, u.surname, t.profile_pic, t.modules, t.price, 
               t.weekdays, t.accepting_appointments, t.sessions, t.hours_per_session,
               (SELECT COUNT(*) FROM tutor_likes WHERE tutor_id = t.tutor_id) AS like_count,
               (SELECT COUNT(*) FROM tutor_likes WHERE tutor_id = t.tutor_id AND user_id = '$user_id') AS user_liked
        FROM users u 
        JOIN tutors_profile t ON u.id = t.tutor_id 
        WHERE t.accepting_appointments = 1";

if (!empty($search_term)) {
    $sql .= " AND t.modules LIKE '%$search_term%'";
}

$result = $conn->query($sql);

// Handle like/unlike actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['like_tutor'])) {
    $tutor_id = $_POST['tutor_id'];
    $like_check = "SELECT * FROM tutor_likes WHERE tutor_id = '$tutor_id' AND user_id = '$user_id'";
    $check_result = $conn->query($like_check);

    if ($check_result->num_rows == 0) {
        $conn->query("INSERT INTO tutor_likes (tutor_id, user_id) VALUES ('$tutor_id', '$user_id')");
    } else {
        $conn->query("DELETE FROM tutor_likes WHERE tutor_id = '$tutor_id' AND user_id = '$user_id'");
    }
    header("Location: user_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutor Connect - User Dashboard</title>
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
        h1 {
            text-align: center;
            color: #fff;
            font-size: 2.5rem;
            margin-top: 20px;
            letter-spacing: 2px;
        }
        .search-bar {
            text-align: center;
            margin: 30px 0;
        }
        .search-bar input {
            padding: 12px;
            border-radius: 20px;
            border: 1px solid #ccc;
            width: 280px;
            font-size: 1.2rem;
            margin-right: 10px;
        }
        .search-bar button {
            padding: 12px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 1.2rem;
            transition: background-color 0.3s ease;
        }
        .search-bar button:hover {
            background-color: #218838;
        }
        .tutor-card {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 20px;
            margin: 20px auto;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            width: 80%;
            max-width: 1000px;
            display: flex;
            align-items: center;
            transition: transform 0.2s ease;
            flex-direction: row;
        }
        .tutor-card:hover {
            transform: scale(1.03);
        }
        .tutor-card img {
            border-radius: 50%;
            margin-right: 20px;
            width: 150px;
            height: 150px;
            object-fit: cover;
        }
        .tutor-info {
            flex: 1;
        }
        .tutor-info p {
            font-size: 1.2rem;
            margin: 10px 0;
        }
        .like-button, .book-button {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            padding: 15px 20px;
            margin-top: 15px;
            font-size: 1rem;
        }
        .like-button.unliked {
            background-color: #dc3545; /* Red for unliking */
        }
        .book-button {
            background-color: #28a745;
        }
        .book-button:hover {
            background-color: #218838;
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

<h1>Available Tutors</h1>

<div class="search-bar">
    <form method="POST" action="">
        <input type="text" name="module" placeholder="Search Module" value="<?php echo htmlspecialchars($search_term); ?>">
        <button type="submit" name="search_module">Search</button>
    </form>
</div>

<?php if ($result->num_rows > 0): ?>
    <?php while($tutor = $result->fetch_assoc()): ?>
        <div class="tutor-card">
            <img src="profile_pics/<?php echo htmlspecialchars($tutor['profile_pic']); ?>" alt="Profile Picture">
            <div class="tutor-info">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($tutor['name'] . ' ' . $tutor['surname']); ?></p>
                <p><strong>Modules:</strong> <?php echo htmlspecialchars($tutor['modules']); ?></p>
                <p><strong>Price:</strong> R<?php echo number_format($tutor['price'], 2); ?></p>
                <p><strong>Weekdays Available:</strong> <?php echo htmlspecialchars($tutor['weekdays']); ?></p>
                <p><strong>Sessions:</strong> <?php echo htmlspecialchars($tutor['sessions']); ?></p>
                <p><strong>Hours per Session:</strong> <?php echo htmlspecialchars($tutor['hours_per_session']); ?> hours</p>
                <p><strong>Likes:</strong> <?php echo htmlspecialchars($tutor['like_count']); ?></p>
            </div>
            <form method="POST" style="margin-left: 15px;">
                <input type="hidden" name="tutor_id" value="<?php echo htmlspecialchars($tutor['tutor_id']); ?>">
                <button type="submit" name="like_tutor" class="like-button <?php echo $tutor['user_liked'] ? 'unliked' : ''; ?>">
                    <?php echo $tutor['user_liked'] ? 'Unlike' : 'Like'; ?>
                </button>
            </form>
            <?php if ($tutor['accepting_appointments']): ?>
                <form method="POST" action="payment.php" style="margin-left: 15px;">
                    <input type="hidden" name="tutor_id" value="<?php echo htmlspecialchars($tutor['tutor_id']); ?>">
                    <button type="submit" class="book-button">Book Now</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p style="text-align: center; color: white;">No tutors available for booking at the moment.</p>
<?php endif; ?>

</body>
</html>
