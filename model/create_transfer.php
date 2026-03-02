<?php
session_start();
require_once '../auth/config.php';

/* ==============================
   Admin-only check
============================== */
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

/* ==============================
   Get POST data
============================== */
$stocking_id   = intval($_POST['stocking_id'] ?? 0);
$from_cage     = intval($_POST['from_cage'] ?? 0);
$to_cage       = intval($_POST['to_cage'] ?? 0);
$assigned_to   = intval($_POST['assigned_to'] ?? 0);
$qty_transfer  = intval($_POST['quantity_after'] ?? 0);
$admin_id      = $_SESSION['id'];

/* ==============================
   Validation
============================== */
if (!$stocking_id || !$from_cage || !$to_cage || !$assigned_to) {
    $_SESSION['popup_error'] = "All required fields must be filled.";
    header("Location: ../nav/transferring.php");
    exit();
}

if ($from_cage == $to_cage) {
    $_SESSION['popup_error'] = "Cannot transfer to the same cage.";
    header("Location: ../nav/transferring.php");
    exit();
}

/* ==============================
   Get stocking info
============================== */
$stockStmt = $conn->prepare("
    SELECT current_quantity, fish_type, standard_fingerlings, schedule_id
    FROM stocking 
    WHERE id = ?
");
$stockStmt->bind_param("i", $stocking_id);
$stockStmt->execute();
$stockStmt->bind_result($current_qty, $fish_type, $standard, $original_schedule);
$stockStmt->fetch();
$stockStmt->close();

$current_qty = (int)$current_qty;

if ($current_qty <= 0) {
    $_SESSION['popup_error'] = "There is no stock left to transfer.";
    header("Location: ../nav/transferring.php");
    exit();
}

if ($qty_transfer <= 0) {
    $_SESSION['popup_error'] = "Transfer quantity must be greater than 0.";
    header("Location: ../nav/transferring.php");
    exit();
}

if ($qty_transfer > $current_qty) {
    $_SESSION['popup_error'] = "Only " . number_format($current_qty) . " available to transfer.";
    header("Location: ../nav/transferring.php");
    exit();
}

$new_from_qty = $current_qty - $qty_transfer;

/* ==============================
   1️⃣ Insert transfer record
============================== */
$date_transferred = null;
$status    = 'Pending';
$mortality = 0;
$remarks   = null;

$stmt = $conn->prepare("
    INSERT INTO transfers
    (schedule_id, stocking_id, from_cage, to_cage, date_transferred,
     quantity_before, quantity_after, mortality, remarks, status, assigned_to)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "iiiisiiissi",
    $original_schedule,  // keep original schedule
    $stocking_id,
    $from_cage,
    $to_cage,
    $date_transferred,
    $current_qty,
    $qty_transfer,
    $mortality,
    $remarks,
    $status,
    $assigned_to
);

$stmt->execute();
$stmt->close();

/* ==============================
   2️⃣ Update FROM cage stock
============================== */
$updateFrom = $conn->prepare("
    UPDATE stocking 
    SET current_quantity = ?
    WHERE id = ?
");
$updateFrom->bind_param("ii", $new_from_qty, $stocking_id);
$updateFrom->execute();
$updateFrom->close();

/* ==============================
   3️⃣ Create NEW schedule (TODAY)
============================== */
$task = "Stocking";

$stockScheduleStmt = $conn->prepare("
    INSERT INTO schedules 
    (fish_cage, task, schedule_datetime, assigned_to, created_by)
    VALUES (?, ?, NOW(), ?, ?)
");

$stockScheduleStmt->bind_param("isii", $to_cage, $task, $assigned_to, $admin_id);
$stockScheduleStmt->execute();

$new_stock_schedule_id = $conn->insert_id;
$stockScheduleStmt->close();

/* ==============================
   4️⃣ Insert NEW stocking record
============================== */
$source_transfer = "Transfer";

$stockInsertStmt = $conn->prepare("
    INSERT INTO stocking
    (schedule_id, date_stocked, source_of_fingerlings,
     fish_type, standard_fingerlings,
     number_of_fingerlings, current_quantity, status)
    VALUES (?, CURDATE(), ?, ?, ?, ?, ?, 'Completed')
");

$stockInsertStmt->bind_param(
    "isssii",
    $new_stock_schedule_id,   // NEW schedule ID (today)
    $source_transfer,
    $fish_type,
    $standard,
    $qty_transfer,
    $qty_transfer
);

$stockInsertStmt->execute();
$stockInsertStmt->close();

/* ==============================
   5️⃣ Notification
============================== */
$title   = "New Transfer Task Assigned";
$message = "You have been assigned a Transfer task.";

$notifStmt = $conn->prepare("
    INSERT INTO notifications (user_id, title, message)
    VALUES (?, ?, ?)
");
$notifStmt->bind_param("iss", $assigned_to, $title, $message);
$notifStmt->execute();
$notifStmt->close();

/* ==============================
   Success
============================== */
$_SESSION['popup_success'] = "Transfer recorded successfully.";
header("Location: ../nav/transferring.php");
exit();
?>