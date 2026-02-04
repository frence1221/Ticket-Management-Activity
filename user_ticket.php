<?php
session_start();
require_once 'config.php';


$user_id = $_SESSION['user_id'];
$username = $_SESSION['email'];

// Get user's tickets
$tickets = [];
$sql = "SELECT id, ticket_number, subject, type, status, created_at, last_updated, description FROM tickets 
        WHERE requestor_id = ? 
        ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tickets[] = $row;
    }
}

// Get ticket statistics for user
$ticket_stats = $conn->query("SELECT status, COUNT(*) as count FROM tickets WHERE requestor_id = $user_id GROUP BY status");
$stats = [];
while ($row = $ticket_stats->fetch_assoc()) {
    $stats[$row['status']] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets - Ticket Management</title>
    <link rel="stylesheet" href="CSS/userTicket.css">
</head>
<body>
    <div class="header">
        <div>
            <h1>📋 My Tickets</h1>
        </div>
        <div class="header-info">
            <p>👤 Welcome, <strong><?= htmlspecialchars($username) ?></strong></p>
           <div class="header-right">
                <a href="userDashboard.php" class="logout-btn" style="margin: 1800px; margin-bottom: 20px;">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="content">
            <!-- Sidebar - Create Ticket -->
            <div class="sidebar">
                <div class="sidebar-title">✍️ Create New Ticket</div>

                <form method="POST" action="process_create_ticket.php" class="ticket-form">
                    <div class="form-group">
                        <label for="type">Request Type</label>
                        <select id="type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="Maintenance">🔧 Maintenance</option>
                            <option value="Complaint">📢 Complaint</option>
                            <option value="Feature Request">⭐ Feature Request</option>
                            <option value="Technical">🛠️ Technical</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" placeholder="Brief subject..." required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" placeholder="Describe your request in detail..." required></textarea>
                    </div>

                    <button type="submit" class="submit-btn">🚀 Submit Ticket</button>
                </form>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <div class="page-title">My Support Tickets</div>
                <div class="page-subtitle">Track and manage your ticket requests</div>

                <!-- Quick Stats -->
                <div class="quick-stats">
                    <div class="quick-stat">
                        <span class="stat-icon">📊</span>
                        <div>
                            <p class="stat-label">Total Tickets</p>
                            <p class="stat-value"><?= count($tickets) ?></p>
                        </div>
                    </div>
                    <div class="quick-stat">
                        <span class="stat-icon">⏳</span>
                        <div>
                            <p class="stat-label">Pending</p>
                            <p class="stat-value"><?= isset($stats['Pending']) ? $stats['Pending'] : 0 ?></p>
                        </div>
                    </div>
                    <div class="quick-stat">
                        <span class="stat-icon">✅</span>
                        <div>
                            <p class="stat-label">Completed</p>
                            <p class="stat-value"><?= isset($stats['Completed']) ? $stats['Completed'] : 0 ?></p>
                        </div>
                    </div>
                    <div class="quick-stat">
                        <span class="stat-icon">❌</span>
                        <div>
                            <p class="stat-label">Failed</p>
                            <p class="stat-value"><?= isset($stats['Failed']) ? $stats['Failed'] : 0 ?></p>
                        </div>
                    </div>
                </div>

                <!-- Tickets Table -->
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Ticket ID</th>
                                <th>Subject</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Last Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tickets)): ?>
                                <?php foreach ($tickets as $ticket): ?>
                                    <tr>
                                        <td><strong>#<?= htmlspecialchars($ticket['id']) ?></strong></td>
                                        <td><?= htmlspecialchars($ticket['subject']) ?></td>
                                        <td><span class="type-badge"><?= htmlspecialchars($ticket['type']) ?></span></td>
                                        <td><span class="status-badge status-<?= strtolower($ticket['status']) ?>"><?= htmlspecialchars($ticket['status']) ?></span></td>
                                        <td><?= date('M d, Y', strtotime($ticket['created_at'])) ?></td>
                                        <td><?= date('M d, Y H:i', strtotime($ticket['last_updated'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="empty-state">📭 No tickets yet. Create one to get started!</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>