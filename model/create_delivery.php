<?php
session_start();
require_once '../auth/config.php';

// Admin-only check
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$stocking_id   = intval($_POST['stocking_id'] ?? 0);
$cage_id       = intval($_POST['cage_id'] ?? 0);
$assigned_to   = intval($_POST['assigned_to'] ?? 0);
$qty_delivered = intval($_POST['quantity_delivered'] ?? 0);
$admin_id      = $_SESSION['id']; // Who is creating the delivery

if (!$stocking_id || !$cage_id || !$assigned_to || $qty_delivered <= 0) {
    $_SESSION['popup_error'] = "All required fields must be filled and quantity must be greater than 0.";
    header("Location: ../nav/delivering.php");
    exit();
}

// 1️⃣ Fetch original stock info
$stmt = $conn->prepare("
    SELECT current_quantity, fish_type, standard_fingerlings, schedule_id
    FROM stocking 
    WHERE id = ?
");
$stmt->bind_param("i", $stocking_id);
$stmt->execute();
$stmt->bind_result($current_qty, $fish_type, $standard, $schedule_id);
$stmt->fetch();
$stmt->close();

if ($qty_delivered > $current_qty) {
    $_SESSION['popup_error'] = "Only $current_qty available to deliver.";
    header("Location: ../nav/delivering.php");
    exit();
}

$new_remaining_qty = $current_qty - $qty_delivered;

// 2️⃣ Insert delivery record
$stmt = $conn->prepare("
    INSERT INTO deliveries 
    (stocking_id, cage_id, delivery_date, quantity_delivered, assigned_to, status)
    VALUES (?, ?, NULL, ?, ?, 'Pending')
");
$stmt->bind_param("iiii", $stocking_id, $cage_id, $qty_delivered, $assigned_to);
$stmt->execute();
$stmt->close();

// 3️⃣ Update original stock (remaining quantity)
$updateStmt = $conn->prepare("
    UPDATE stocking SET current_quantity = ? WHERE id = ?
");
$updateStmt->bind_param("ii", $new_remaining_qty, $stocking_id);
$updateStmt->execute();
$updateStmt->close();

// 4️⃣ Notification for assigned user
$title   = "New Delivery Task Assigned";
$message = "You have been assigned a delivery task.";

$notifStmt = $conn->prepare("
    INSERT INTO notifications (user_id, title, message)
    VALUES (?, ?, ?)
");
$notifStmt->bind_param("iss", $assigned_to, $title, $message);
$notifStmt->execute();
$notifStmt->close();

$_SESSION['popup_success'] = "Delivery recorded successfully.";
header("Location: ../nav/delivering.php");
exit();
?>