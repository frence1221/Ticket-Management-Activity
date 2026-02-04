<?php
session_start();
require_once 'config.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ticket_id = intval($_POST['ticket_id']);
    $status = htmlspecialchars($_POST['status']);

    $valid_statuses = ['Pending', 'Approved', 'Ongoing', 'Completed', 'Cancelled', 'Declined', 'Rejected', 'Accepted', 'Failed'];

    if (in_array($status, $valid_statuses)) {
        $sql = "UPDATE tickets SET status = ?, updated_at = NOW(), last_updated = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $ticket_id);

        if ($stmt->execute()) {
            header('Location: admin_ticket.php?success=1');
        } else {
            header('Location: admin_ticket.php?error=1');
        }
    } else {
        header('Location: admin_ticket.php?error=1');
    }
} else {
    header('Location: admin_ticket.php');
}
?>