<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "user") {
    header("Location: index.php");
    exit();
}
require_once 'config.php';

$userId = $_SESSION["user_id"];
$query = "SELECT last_name, first_name, middle_name, birthdate, age, gender, phone_number, email, created_at FROM authentication WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="CSS/userDashboard.css">
</head>

<body>

    <div class="profile-card">
        <button style="float:right;" onclick="window.location.href='index.php'">Logout</button>
        <h2>User Profile</h2>
        <div class="avatar">
            <span style="font-size:2em;"><?php echo strtoupper(substr($user['first_name'], 0, 1)); ?></span>
        </div>
        <h3><?php echo strtoupper($user['first_name']); ?></h3>
        <p style="font-weight: bold;">Email Address<br><?php echo htmlspecialchars($user['email']); ?></p>
        <p style="font-weight: bold;">Full Name<br><?php echo htmlspecialchars($user['last_name'] . ', ' . $user['first_name'] . ' ' . $user['middle_name']); ?></p>
        <p style="font-weight: bold;">Birthdate<br><?php echo htmlspecialchars($user['birthdate']); ?></p>
        <p style="font-weight: bold;">Age<br><?php echo htmlspecialchars($user['age']); ?></p>
        <p style="font-weight: bold;">Gender<br><?php echo htmlspecialchars(ucfirst($user['gender'])); ?></p>
        <p style="font-weight: bold;">Phone<br>+63<?php echo htmlspecialchars($user['phone_number']); ?></p>
    </div>

    <button type="button" class="login-btn"
        onclick="window.location.href='user_ticket.php'">
        Ticket Management
    </button>

</body>

</html>