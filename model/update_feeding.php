<?php
session_start();
require_once '../auth/config.php';

/* ==============================
   Validate Session
============================== */
if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    header("Location: ../index.php");
    exit();
}

/* ==============================
   Validate POST Request
============================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../nav/feeding.php");
    exit();
}

$id     = $_POST['id'] ?? '';
$status = $_POST['status'] ?? 'Pending';

if (empty($id)) {
    $_SESSION['popup_error'] = "Feeding ID missing.";
    header("Location: ../nav/feeding.php");
    exit();
}

/* ==============================
   Role-Based Update
============================== */
if ($_SESSION['role'] === 'admin') {

    // Admin can update everything
    $assigned_to   = $_POST['assigned_to'] ?? null;
    $feeding_date  = $_POST['feeding_date'] ?? null;
    $feed_type     = $_POST['feed_type'] ?? '';
    $amount        = $_POST['amount'] ?? 0;
    $unit          = $_POST['unit'] ?? '';
    $fed_time      = $_POST['fed_time'] ?? null;

    // Get current assigned_to for notification check
    $current = $conn->query("SELECT assigned_to FROM feedings WHERE id = $id")->fetch_assoc();
    $old_assigned = $current['assigned_to'];

    $stmt = $conn->prepare("
        UPDATE feedings
        SET assigned_to = ?, 
            feeding_date = ?,
            feed_type = ?,
            amount = ?,
            unit = ?,
            fed_time = ?,
            status = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "issdsssi",
        $assigned_to,
        $feeding_date,
        $feed_type,
        $amount,
        $unit,
        $fed_time,
        $status,
        $id
    );

} else {

    // Employee can update STATUS only
    $stmt = $conn->prepare("
        UPDATE feedings
        SET status = ?
        WHERE id = ?
    ");

    $stmt->bind_param("si", $status, $id);
}

/* ==============================
   Execute Update
============================== */
if ($stmt->execute()) {

    // 🔔 Notify employee if admin changed assigned_to
    if ($_SESSION['role'] === 'admin' && $assigned_to && $assigned_to != $old_assigned) {
        $message = "You have been assigned a new Feeding task.";
        $notifStmt = $conn->prepare("
            INSERT INTO notifications (user_id, title, message, is_read, created_at)
            VALUES (?, 'New Feeding Task Assigned', ?, 0, NOW())
        ");
        $notifStmt->bind_param("is", $assigned_to, $message);
        $notifStmt->execute();
        $notifStmt->close();
    }

    // 🔔 Notify admin if employee completed task
    if ($_SESSION['role'] !== 'admin' && $status === "Completed") {
        $adminQuery = $conn->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
        if ($admin = $adminQuery->fetch_assoc()) {
            $admin_id = $admin['id'];
            $message = $_SESSION['name'] . " completed a Feeding task";
            $notifStmt = $conn->prepare("
                INSERT INTO notifications (user_id, title, message, is_read, created_at)
                VALUES (?, 'Task Completed', ?, 0, NOW())
            ");
            $notifStmt->bind_param("is", $admin_id, $message);
            $notifStmt->execute();
            $notifStmt->close();
        }
    }

    $_SESSION['popup_success'] = "Feeding updated successfully.";

} else {
    $_SESSION['popup_error'] = "Failed to update feeding.";
}

$stmt->close();
header("Location: ../nav/feeding.php");
exit();