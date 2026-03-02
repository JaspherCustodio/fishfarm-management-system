<?php
session_start();
require_once '../auth/config.php';

$id = $_POST['id'] ?? '';

if (empty($id)) {
    $_SESSION['popup_error'] = "Invalid request.";
    header("Location: ../nav/stocking.php");
    exit();
}

// Common fields from form
$date_stocked = $_POST['date_stocked'] ?: null; 
$source       = $_POST['source_of_fingerlings'] ?: '';
$fish_type    = $_POST['fish_type'] ?: '';
$standard     = $_POST['standard_fingerlings'] ?: '';
$quantity     = $_POST['number_of_fingerlings'] ?: 0;
$status       = $_POST['status'] ?: 'Pending';

// 🔹 Fetch current values from DB
$currentData = $conn->query("SELECT number_of_fingerlings, current_quantity, assigned_to FROM stocking WHERE id = $id")->fetch_assoc();
$old_quantity = (int)$currentData['number_of_fingerlings'];
$old_current  = (int)$currentData['current_quantity'];
$old_assigned = $currentData['assigned_to'] ?? null;

// 🔹 Compute current_quantity relative to change
$diff = $quantity - $old_quantity;
$current_quantity = max(0, $old_current + $diff); // never below 0

if ($_SESSION['role'] === 'admin') {
    // Admin can update assigned_to
    $assigned_to = isset($_POST['assigned_to']) && $_POST['assigned_to'] !== '' 
        ? intval($_POST['assigned_to']) 
        : null;

    $stmt = $conn->prepare("
        UPDATE stocking
        SET assigned_to=?, 
            date_stocked=?, 
            source_of_fingerlings=?, 
            fish_type=?, 
            standard_fingerlings=?, 
            number_of_fingerlings=?, 
            current_quantity=?, 
            status=?
        WHERE id=?
    ");

    $stmt->bind_param(
        "isssiiisi",
        $assigned_to,
        $date_stocked,
        $source,
        $fish_type,
        $standard,
        $quantity,
        $current_quantity,
        $status,
        $id
    );

} else {
    // Employee cannot change assigned_to
    $stmt = $conn->prepare("
        UPDATE stocking
        SET date_stocked=?, 
            source_of_fingerlings=?, 
            fish_type=?, 
            standard_fingerlings=?, 
            number_of_fingerlings=?, 
            current_quantity=?, 
            status=?
        WHERE id=?
    ");

    $stmt->bind_param(
        "sssiisii",
        $date_stocked,
        $source,
        $fish_type,
        $standard,
        $quantity,
        $current_quantity,
        $status,
        $id
    );
}

if ($stmt->execute()) {

    /* ==============================
       🔔 Notify employee if reassigned
    ============================== */
    if ($_SESSION['role'] === 'admin' && $assigned_to && $assigned_to != $old_assigned) {
        $message = "You have been assigned a new Stocking task.";

        $notifStmt = $conn->prepare("
            INSERT INTO notifications (user_id, title, message, is_read, created_at)
            VALUES (?, 'New Task Assigned', ?, 0, NOW())
        ");
        $notifStmt->bind_param("is", $assigned_to, $message);
        $notifStmt->execute();
        $notifStmt->close();
    }

    /* ==============================
       🔔 Notify admin if employee completed
    ============================== */
    if ($_SESSION['role'] !== 'admin' && $status === "Completed") {

        $adminQuery = $conn->query("SELECT id FROM users WHERE role='admin' LIMIT 1");
        $admin = $adminQuery->fetch_assoc();

        if ($admin) {
            $admin_id = $admin['id'];
            $message = $_SESSION['name'] . " completed a Stocking task.";

            $notifStmt = $conn->prepare("
                INSERT INTO notifications (user_id, title, message, is_read, created_at)
                VALUES (?, 'Task Completed', ?, 0, NOW())
            ");
            $notifStmt->bind_param("is", $admin_id, $message);
            $notifStmt->execute();
            $notifStmt->close();
        }
    }

    $_SESSION['popup_success'] = "Stocking record updated successfully.";

} else {
    $_SESSION['popup_error'] = "Failed to update.";
}

$stmt->close();
header("Location: ../nav/stocking.php");
exit();