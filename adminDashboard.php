<?php

session_start();

$errors = [
    'login' => $_SESSION["login_error"] ?? '',
    'register' => $_SESSION["registration_errors"] ?? ''
];

$activeForm = $_SESSION["active_form"] ?? 'login';

session_unset();

function showError($error)
{
    return !empty($error) ? "<div class=\"error-message\">$error</div>" : '';
}

function isActive($formName, $activeForm)
{
    return $formName === $activeForm ? 'active' : '';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management</title>
    <link rel="stylesheet" href="CSS/adminDashboard.css">

<body>
    <div class="container">
        <div class="header" style="display: flex; justify-content: space-between; align-items: center;">
            <h1>Admin Management</h1>
            <button type="button"
                onclick="window.location.href='admin_ticket.php'"
                style="
            padding: 10px 24px;
            background: linear-gradient(135deg, #c92931, #c92931);
            color: #ffffff;
            border: none;
            border-radius: px;
            font-size: 15px;
            
            cursor: pointer;
            margin-left: 400px;
            
        ">
                🎫 Ticket Management
            </button>

            <button style="float:right;" onclick="window.location.href='index.php'">Logout</button>
        </div>
    </div>

    <div class="table-wrapper">
        <table id="userTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Birth Date</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php
                require_once 'config.php';
                $result = $conn->query("SELECT id, last_name, first_name, middle_name, birthdate, age, gender, phone_number, email, role FROM authentication");
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $fullName = $row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name'];
                        $phone = '+63' . $row['phone_number'];
                        echo '<tr>';
                        echo '<td>' . $row['id'] . '</td>';
                        echo '<td>' . htmlspecialchars($fullName) . '</td>';
                        echo '<td>' . htmlspecialchars($row['birthdate']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['age']) . '</td>';
                        echo '<td>' . htmlspecialchars(ucfirst($row['gender'])) . '</td>';
                        echo '<td>' . htmlspecialchars($phone) . '</td>';
                        echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                        echo '<td>';
                        echo htmlspecialchars(ucfirst($row['role']));
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="8">No users found.</td></tr>';
                }
                ?>




            </tbody>
        </table>
    </div>
</body>