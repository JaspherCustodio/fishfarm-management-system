<?php
session_start();
require_once '../auth/config.php';

if (!isset($_SESSION['id'])) {
    exit;
}

$userId = $_SESSION['id'];
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

$stmt = $conn->prepare("
    SELECT id, message, created_at, is_read
    FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 10 OFFSET ?
");

$stmt->bind_param("ii", $userId, $offset);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $unreadClass = $row['is_read'] == 0 ? 'notif-unread' : '';
    echo "
        <div class='notif-item $unreadClass' data-id='{$row['id']}'>
            <p>" . htmlspecialchars($row['message']) . "</p>
            <small>" . date('M d Y h:i A', strtotime($row['created_at'])) . "</small>
        </div>
    ";
}

$stmt->close();