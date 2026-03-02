<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth/config.php';

$notifCount = 0;
$notifications = [];

if (isset($_SESSION['id']) && isset($conn)) {
    // Count unread notifications
    $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['id']);
        $stmt->execute();
        $stmt->bind_result($notifCount);
        $stmt->fetch();
        $stmt->close();
    }

    // Get latest 10 notifications with read status
    $stmt = $conn->prepare("
        SELECT id, message, created_at, is_read 
        FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }
        }
        $stmt->close();
    }
}
?>

<header class="header">
    <div class="header-left">
        <img src="../assets/img/dpa-logo.png" class="header-logo">
        <h2 class="u-name">DPA DATA <b>MANAGEMENT SYSTEM</b></h2>
    </div>

    <div class="header-right">
        <div class="user-dropdown">
            <div class="header-welcome">
    <div class="welcome-text">Welcome,</div>
    <div class="user-row">
        <span class="user-name">
            <?= htmlspecialchars($_SESSION['name'] ?? '') ?>
            <i class="fa fa-caret-down"></i>
        </span>
        <div class="notif-wrapper">
            <i class="fa fa-bell" id="notifBell"></i>
            <?php if ($notifCount > 0): ?>
                <span class="notif-badge"><?= $notifCount ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>


            <div class="dropdown-menu">
                <a href="../auth/logout.php">
                    <i class="fa fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</header>

<div id="notifOverlay" class="notif-overlay">
    <div class="notif-panel">
        <div class="notif-header">
            <h3>Notifications</h3>
            <button onclick="closeNotif()">X</button>
        </div>

        <div class="notif-body">
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $row): ?>
                    <?php $unreadClass = $row['is_read'] == 0 ? ' notif-unread' : ''; ?>
                    <div class="notif-item<?= $unreadClass ?>" data-id="<?= $row['id'] ?>">
                        <p><?= htmlspecialchars($row['message']) ?></p>
                        <small><?= date('M d Y h:i A', strtotime($row['created_at'])) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center;">No notifications</p>
            <?php endif; ?>
        </div>
        <div class="notif-footer">
            <button id="loadMoreNotif">Load More</button>
        </div>
    </div>
</div>

<script>
const userName = document.querySelector('.user-name');
const dropdown = document.querySelector('.dropdown-menu');
const notifBell = document.getElementById('notifBell');
const notifOverlay = document.getElementById('notifOverlay');

// Dropdown toggle
userName.addEventListener('click', e => {
    e.stopPropagation();
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
});

// Close dropdown when clicking outside
document.addEventListener('click', () => {
    dropdown.style.display = 'none';
});

// Open notifications overlay
notifBell.addEventListener('click', e => {
    e.stopPropagation();
    notifOverlay.style.display = 'flex';
});

// Close overlay
function closeNotif() {
    notifOverlay.style.display = 'none';
}

// Clicking outside panel closes overlay
notifOverlay.addEventListener('click', e => {
    if (e.target === notifOverlay) closeNotif();
});

document.querySelector('.notif-body').addEventListener('click', function(e) {

    const item = e.target.closest('.notif-item');
    if (!item) return;

    if (item.classList.contains('notif-unread')) {

        // Remove highlight
        item.classList.remove('notif-unread');

        // Update badge
        let badge = document.querySelector('.notif-badge');
        if (badge) {
            let count = parseInt(badge.textContent);
            count = Math.max(0, count - 1);
            if (count === 0) badge.remove();
            else badge.textContent = count;
        }

        // Mark notification as read
        const notifId = item.dataset.id;
        if (notifId) {
            fetch(`../model/mark_notification_read.php?id=${notifId}`)
                .catch(err => console.error('Failed to mark read:', err));
        }
    }
});

let notifOffset = 10;

document.getElementById('loadMoreNotif').addEventListener('click', () => {

    fetch(`../model/load_more_notifications.php?offset=${notifOffset}`)
        .then(res => res.text())
        .then(data => {

            if (data.trim() === "") {
                document.getElementById('loadMoreNotif').innerText = "No More Notifications";
                document.getElementById('loadMoreNotif').disabled = true;
                return;
            }

            document.querySelector('.notif-body').insertAdjacentHTML('beforeend', data);
            notifOffset += 10;
        })
        .catch(err => console.error(err));
});
</script>