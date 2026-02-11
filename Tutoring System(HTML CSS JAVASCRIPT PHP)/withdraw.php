<?php
session_start();
include('config.php');

// Ensure the user is logged in as a tutor
if ($_SESSION['role'] != 'tutor') {
    header("Location: login.php");
    exit();
}

$tutor_id = $_SESSION['user_id'];

// Fetch tutor's balance
$sql_balance = "SELECT balance FROM earnings WHERE tutor_id='$tutor_id'";
$result_balance = $conn->query($sql_balance);

if ($result_balance && $result_balance->num_rows > 0) {
    $balance = $result_balance->fetch_assoc()['balance'];
} else {
    $balance = 0; // Set balance to 0 if no earnings found
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $withdraw_amount = $_POST['withdraw_amount'];
    $bank_name = $_POST['bank_name'];
    $account_number = $_POST['account_number'];
    $account_type = $_POST['account_type'];
    $branch_code = $_POST['branch_code'];

    if ($withdraw_amount <= $balance) {
        // Deduct the balance
        $sql_withdraw = "UPDATE earnings SET balance = balance - $withdraw_amount WHERE tutor_id='$tutor_id'";
        if ($conn->query($sql_withdraw)) {
            echo "Withdrawal request successful!";
        } else {
            echo "Error processing withdrawal!";
        }
    } else {
        echo "Insufficient balance!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutor Connect - Withdraw Funds</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-image: url('background.jpg');
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            background-color: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .logo-container {
            margin-bottom: 20px;
        }
        .logo {
            width: 100px;
            height: auto;
        }
        h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 20px;
        }
        p {
            font-size: 18px;
            color: #555;
        }
        form {
            margin-top: 20px;
        }
        label {
            display: block;
            text-align: left;
            font-size: 16px;
            color: #333;
            margin-bottom: 8px;
        }
        input[type="text"], input[type="number"] {
            width: calc(100% - 20px);
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            display: block;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 15px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .form-icon {
            margin-right: 10px;
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
            <img src="logo1.png" alt="Tutor Connect Logo" class="logo"> <!-- Replace with your actual logo -->
        </div>

        <h1><i class="fas fa-wallet"></i> Withdraw Funds</h1>
        <p>Current Balance: <strong>R<?php echo number_format($balance, 2); ?></strong></p>

        <form method="POST">
            <label for="withdraw_amount">
                <i class="fas fa-dollar-sign form-icon"></i>Amount to Withdraw:
            </label>
            <input type="number" name="withdraw_amount" placeholder="Enter amount" required>

            <label for="bank_name">
                <i class="fas fa-university form-icon"></i>Bank Name:
            </label>
            <input type="text" name="bank_name" placeholder="Enter bank name" required>

            <label for="account_number">
                <i class="fas fa-id-card form-icon"></i>Account Number:
            </label>
            <input type="text" name="account_number" placeholder="Enter account number" required>

            <label for="account_type">
                <i class="fas fa-info-circle form-icon"></i>Account Type:
            </label>
            <input type="text" name="account_type" placeholder="Enter account type" required>

            <label for="branch_code">
                <i class="fas fa-code-branch form-icon"></i>Branch Code:
            </label>
            <input type="text" name="branch_code" placeholder="Enter branch code" required>

            <button type="submit"><i class="fas fa-paper-plane"></i> Submit Withdrawal</button>
        </form>

        <!-- Back button to return to previous page -->
        <a href="tutor_dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</body>
</html>
