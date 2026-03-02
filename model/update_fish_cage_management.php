<?php
session_start();
require_once "../auth/config.php";

$id = $_POST['id'] ?? '';

if (empty($id)) {
    $_SESSION['popup_error'] = "Invalid request.";
    header("Location: ../nav/fish_cage_management.php");
    exit();
}

// Common fields
$date          = $_POST['date'] ?: null;           // allow null
$result        = $_POST['result'] ?: '';
$optimum_level = $_POST['optimum_level'] ?: '';
$remarks       = $_POST['remarks'] ?: '';
$status        = $_POST['status'] ?: 'Pending';

if ($_SESSION['role'] === 'admin') {
    // Admin can update everything including assigned_to
    $assigned_to = isset($_POST['assigned_to']) && $_POST['assigned_to'] !== '' ? intval($_POST['assigned_to']) : null;

    // 🔹 Get current assigned_to before updating
    $current = $conn->query("SELECT assigned_to FROM fish_cage_management WHERE id = $id")->fetch_assoc();
    $old_assigned = $current['assigned_to'];

    $stmt = $conn->prepare("
        UPDATE fish_cage_management
        SET assigned_to=?, date=?, result=?, optimum_level=?, remarks=?, status=?
        WHERE id=?
    ");
    $stmt->bind_param(
        "isssssi",
        $assigned_to,
        $date,
        $result,
        $optimum_level,
        $remarks,
        $status,
        $id
    );
} else {
    // Employee can update all except assigned_to
    $stmt = $conn->prepare("
        UPDATE fish_cage_management
        SET date=?, result=?, optimum_level=?, remarks=?, status=?
        WHERE id=?
    ");
    $stmt->bind_param(
        "sssssi",
        $date,
        $result,
        $optimum_level,
        $remarks,
        $status,
        $id
    );
}

if ($stmt->execute()) {

    // 🔔 Notify employee if admin changed assigned_to
    if ($_SESSION['role'] === 'admin' && $assigned_to && $assigned_to != $old_assigned) {
        $message = "You have been assigned a new Fish Cage Management task.";
        $notifStmt = $conn->prepare("
            INSERT INTO notifications (user_id, title, message, is_read, created_at)
            VALUES (?, 'New Task Assigned', ?, 0, NOW())
        ");
        $notifStmt->bind_param("is", $assigned_to, $message);
        $notifStmt->execute();
        $notifStmt->close();
    }

    // 🔔 If employee updated to Completed, notify admin
    if ($_SESSION['role'] !== 'admin' && $status === "Completed") {
        $adminQuery = $conn->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
        $admin = $adminQuery->fetch_assoc();
        if ($admin) {
            $admin_id = $admin['id'];
            $message = $_SESSION['name'] . " completed a Fish Cage Management task.";
            $notifStmt = $conn->prepare("
                INSERT INTO notifications (user_id, title, message, is_read, created_at)
                VALUES (?, 'Task Completed', ?, 0, NOW())
            ");
            $notifStmt->bind_param("is", $admin_id, $message);
            $notifStmt->execute();
            $notifStmt->close();
        }
    }

    $_SESSION['popup_success'] = "Fish Cage record updated successfully.";

} else {
    $_SESSION['popup_error'] = "Failed to update.";
}

$stmt->close();
header("Location: ../nav/fish_cage_management.php");
exit();