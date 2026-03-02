<?php
session_start();
require_once "../auth/config.php";

/* ==============================
   Admin-only check
============================= */
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

/* ==============================
   Get POST data
============================= */
$schedule_id = intval($_POST['schedule_id'] ?? 0);
$assigned_to = intval($_POST['assigned_to'] ?? 0);

/* ==============================
   Basic validation
============================= */
if (!$schedule_id || !$assigned_to) {
    $_SESSION['popup_error'] = "Schedule and Assigned Employee are required.";
    header("Location: ../nav/stocking.php?add=true");
    exit();
}

/* ==============================
   Set default values
============================= */
$fish_type        = null;  // empty string instead of null
$date_stocked     = null; // MySQL default for date (or use current date)
$source           = "";  // empty string instead of null
$standard         = 0;   // default numeric value
$number           = 0;   // default numeric value
$current_quantity = 0;   // default numeric value
$status           = 'Pending';

/* ==============================
   Insert into stocking
============================= */
$stmt = $conn->prepare("
    INSERT INTO stocking
    (schedule_id, assigned_to, fish_type, date_stocked, source_of_fingerlings, 
     standard_fingerlings, number_of_fingerlings, current_quantity, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "iisssiiss",
    $schedule_id,
    $assigned_to,
    $fish_type,
    $date_stocked,
    $source,
    $standard,
    $number,
    $current_quantity,
    $status
);

$stmt->execute();
$stmt->close();

/* ==============================
   Get schedule info for notification
============================= */
$scheduleInfo = $conn->query("
    SELECT s.schedule_datetime, fc.cage_name
    FROM schedules s
    JOIN fish_cages fc ON fc.id = s.fish_cage
    WHERE s.id = $schedule_id
")->fetch_assoc();

$datetime = date('M d Y h:i A', strtotime($scheduleInfo['schedule_datetime']));
$cageName = htmlspecialchars($scheduleInfo['cage_name']);

/* ==============================
   Send notification
============================= */
$title   = "New Stocking Task Assigned";
$message = "You have been assigned a Stocking task for cage \"$cageName\" scheduled on $datetime.";

$notifStmt = $conn->prepare("
    INSERT INTO notifications (user_id, title, message)
    VALUES (?, ?, ?)
");
$notifStmt->bind_param("iss", $assigned_to, $title, $message);
$notifStmt->execute();
$notifStmt->close();

/* ==============================
   Success popup
============================= */
$_SESSION['popup_success'] = "Stocking record added successfully and notification sent.";
header("Location: ../nav/stocking.php");
exit();