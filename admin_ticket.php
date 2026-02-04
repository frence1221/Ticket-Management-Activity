<?php
session_start();
require_once 'config.php';

$email = $_SESSION['email'] ?? '';


// Get statistics
$ticket_stats = $conn->query("SELECT status, COUNT(*) as count FROM tickets GROUP BY status");
$stats = [];
while ($row = $ticket_stats->fetch_assoc()) {
    $stats[$row['status']] = $row['count'];
}

// Get all tickets
$tickets = [];

$sql = "SELECT t.id, t.ticket_number, t.subject, t.type, t.description, t.status, t.last_updated, a.email 
        FROM tickets t 
        JOIN authentication a ON t.id = a.id 
        ORDER BY t.created_at DESC";
$result = $conn->query($sql);

$tickets = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tickets[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ticket Management</title>
    <link rel="stylesheet" href="CSS/adminTicket.css">

</head>

<body>
    <div class="header">
        <div>
            <h1>📋 Ticket Management</h1>
        </div>
        <div class="header-info">
            <p>👤 Admin: <strong><?= htmlspecialchars($email) ?></strong></p>
            <div class="header-right">
                <a href="adminDashboard.php" class="logout-btn" style="margin: 1800px;">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="page-title">Ticket Management Dashboard</div>
        <div class="page-subtitle">View and manage all support tickets</div>

        <!-- Statistics Cards -->
        <div class="stats-container">
            <?php
            $statuses = ['Pending' => '⏳', 'Approved' => '✅', 'Ongoing' => '🔄', 'Completed' => '🎉', 'Cancelled' => '❌', 'Failed' => '⚠️'];
            foreach ($statuses as $status => $emoji) {
                $count = isset($stats[$status]) ? $stats[$status] : 0;
                echo '<div class="stat-card">';
                echo '<div class="stat-emoji">' . $emoji . '</div>';
                echo '<div class="stat-info">';
                echo '<p class="stat-label">' . htmlspecialchars($status) . '</p>';
                echo '<p class="stat-number">' . $count . '</p>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>

        <!-- Filters -->
        <div class="controls">
            <input type="text" id="searchInput" placeholder="🔍 Search by ID, subject, requestor..." class="search-input">
            <select id="statusFilter" class="filter-select">
                <option value="">All Statuses</option>
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
                <option value="Ongoing">Ongoing</option>
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
                <option value="Declined">Declined</option>
                <option value="Rejected">Rejected</option>
                <option value="Accepted">Accepted</option>
                <option value="Failed">Failed</option>
            </select>
        </div>

        <!-- Tickets Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Requestor</th>
                        <th>Subject</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="ticketsBody">
                    <?php if (!empty($tickets)): ?>
                        <?php foreach ($tickets as $ticket):
                            $result = $conn->query($sql);
                        ?>
                            <tr class="ticket-row" data-status="<?= htmlspecialchars($ticket['status']) ?>">
                                <td><strong>#<?= htmlspecialchars($ticket['id']) ?></strong></td>
                                <td><?= htmlspecialchars($ticket['email']) ?></td>
                                <td><?= htmlspecialchars($ticket['subject']) ?></td>
                                <td><?= htmlspecialchars($ticket['type']) ?></td>
                                <td class="description-cell"><?= substr(htmlspecialchars($ticket['description']), 0, 50) ?>...</td>
                                <td><span class="status-badge status-<?= strtolower($ticket['status']) ?>"><?= htmlspecialchars($ticket['status']) ?></span></td>
                                <td><?= date('M d, Y H:i', strtotime($ticket['last_updated'])) ?></td>
                                <td>
                                    <form method="POST" action="process_update_ticket.php" class="action-form">
                                        <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                                        <select name="status" class="status-select" required>
                                            <option value="Pending" <?= $ticket['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="Approved" <?= $ticket['status'] == 'Approved' ? 'selected' : '' ?>>Approved</option>
                                            <option value="Ongoing" <?= $ticket['status'] == 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
                                            <option value="Completed" <?= $ticket['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                            <option value="Cancelled" <?= $ticket['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            <option value="Declined" <?= $ticket['status'] == 'Declined' ? 'selected' : '' ?>>Declined</option>
                                            <option value="Rejected" <?= $ticket['status'] == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                            <option value="Accepted" <?= $ticket['status'] == 'Accepted' ? 'selected' : '' ?>>Accepted</option>
                                            <option value="Failed" <?= $ticket['status'] == 'Failed' ? 'selected' : '' ?>>Failed</option>
                                        </select>
                                        <button type="submit" class="update-btn">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="empty-state">📭 No tickets found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const ticketsBody = document.getElementById('ticketsBody');
        const rows = document.querySelectorAll('.ticket-row');

        // Search functionality
        searchInput.addEventListener('keyup', filterTable);
        statusFilter.addEventListener('change', filterTable);

        function filterTable() {
            const searchTerm = searchInput.value.toLowerCase();
            const statusTerm = statusFilter.value;

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const status = row.getAttribute('data-status');

                const matchesSearch = searchTerm === '' || text.includes(searchTerm);
                const matchesStatus = statusTerm === '' || status === statusTerm;

                row.style.display = matchesSearch && matchesStatus ? '' : 'none';
            });
        }
    </script>
</body>

</html>