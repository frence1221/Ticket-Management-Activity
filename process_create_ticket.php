<?php
session_start();
require_once 'config.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $subject = htmlspecialchars($_POST['subject']);
    $type = htmlspecialchars($_POST['type']);
    $description = htmlspecialchars($_POST['description']);

    if (!empty($subject) && !empty($type) && !empty($description)) {
        $ticket_number = 'TKT-' . time();
        
        $sql = "INSERT INTO tickets (ticket_number, requestor_id, subject, type, description, status) 
                VALUES (?, ?, ?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisss", $ticket_number, $user_id, $subject, $type, $description);

        if ($stmt->execute()) {
            header('Location: user_ticket.php?success=1');
        } else {
            header('Location: user_ticket.php?error=1');
        }
    } else {
        header('Location: user_ticket.php?error=1');
    }
} else {
    header('Location: user_ticket.php');
}
?>