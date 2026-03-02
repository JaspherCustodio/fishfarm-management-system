<?php
session_start();
require_once '../auth/config.php';

$id = $_POST['id'] ?? '';

if (empty($id)) {
    $_SESSION['popup_error'] = "Invalid record.";
    header("Location: ../nav/net_checking.php");
    exit();
}

// Common fields
$status      = $_POST['status'] ?: 'Pending';
$start_date  = $_POST['start_date'] ?: null;
$start_time  = $_POST['start_time'] ?: null;
$end_date    = $_POST['end_date'] ?: null;
$end_time    = $_POST['end_time'] ?: null;

if ($_SESSION['role'] === 'admin') {

    $assigned_to = isset($_POST['assigned_to']) && $_POST['assigned_to'] !== '' 
        ? intval($_POST['assigned_to']) 
        : null;

    // 🔹 Get old assigned_to
    $current = $conn->query("SELECT assigned_to FROM net_checking WHERE id = $id")->fetch_assoc();
    $old_assigned = $current['assigned_to'];

    // 🔹 Update net_checking (same style as net_cleaning)
    $stmt = $conn->prepare("
        UPDATE net_checking
        SET assigned_to=?, start_date=?, start_time=?, end_date=?, end_time=?, status=?
        WHERE id=?
    ");
    $stmt->bind_param(
        "isssssi",
        $assigned_to,
        $start_date,
        $start_time,
        $end_date,
        $end_time,
        $status,
        $id
    );

} else {

    // Employee can update everything except assignment
    $stmt = $conn->prepare("
        UPDATE net_checking
        SET start_date=?, start_time=?, end_date=?, end_time=?, status=?
        WHERE id=?
    ");
    $stmt->bind_param(
        "sssssi",
        $start_date,
        $start_time,
        $end_date,
        $end_time,
        $status,
        $id
    );
}

if ($stmt->execute()) {

    // 🔔 Notify employee if admin changed assigned_to
    if ($_SESSION['role'] === 'admin' && $assigned_to && $assigned_to != $old_assigned) {
        $message = "You have been assigned a new Net Cleaning task.";
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
            $message = $_SESSION['name'] . " completed a Net Checking task.";
            $notifStmt = $conn->prepare("
                INSERT INTO notifications (user_id, title, message, is_read, created_at)
                VALUES (?, 'Task Completed', ?, 0, NOW())
            ");
            $notifStmt->bind_param("is", $admin_id, $message);
            $notifStmt->execute();
            $notifStmt->close();
        }
    }

    $_SESSION['popup_success'] = "Net Checking updated successfully.";

} else {
    $_SESSION['popup_error'] = "Failed to update.";
}

$stmt->close();
header("Location: ../nav/net_checking.php");
exit();