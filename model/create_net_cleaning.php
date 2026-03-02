<?php
session_start();
require_once "../auth/config.php";

// Only admins can create
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// 1️⃣ Get POST data
$schedule_id = intval($_POST['schedule_id'] ?? 0);
$assigned_to = intval($_POST['assigned_to'] ?? 0);

$start_date = $_POST['start_date'] ?: null;
$start_time = $_POST['start_time'] ?: null;
$end_date   = $_POST['end_date'] ?: null;
$end_time   = $_POST['end_time'] ?: null;
$status     = $_POST['status'] ?: 'Pending';

// Validate required fields
if (!$schedule_id || !$assigned_to) {
    $_SESSION['popup_error'] = "Schedule and Assigned Employee are required.";
    header("Location: ../nav/net_cleaning.php?add=true");
    exit();
}

// 2️⃣ Insert into net_cleaning table
$stmt = $conn->prepare("
    INSERT INTO net_cleaning
    (schedule_id, assigned_to, start_date, start_time, end_date, end_time, status)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param(
    "iisssss",
    $schedule_id,
    $assigned_to,
    $start_date,
    $start_time,
    $end_date,
    $end_time,
    $status
);
$stmt->execute();
$stmt->close();

// 3️⃣ Get schedule info for notification
$scheduleInfo = $conn->query("
    SELECT s.schedule_datetime, fc.cage_name 
    FROM schedules s
    JOIN fish_cages fc ON fc.id = s.fish_cage
    WHERE s.id = $schedule_id
")->fetch_assoc();

$datetime = date('M d Y h:i A', strtotime($scheduleInfo['schedule_datetime']));
$cageName = htmlspecialchars($scheduleInfo['cage_name']);

// 4️⃣ Insert notification
$title   = "New Net Cleaning Task Assigned";
$message = "You have been assigned a Net Cleaning task for cage \"$cageName\" scheduled on $datetime.";

$notifStmt = $conn->prepare("
    INSERT INTO notifications (user_id, title, message)
    VALUES (?, ?, ?)
");
$notifStmt->bind_param("iss", $assigned_to, $title, $message);
$notifStmt->execute();
$notifStmt->close();

// 5️⃣ Success popup
$_SESSION['popup_success'] = "Net Cleaning record added successfully and notification sent.";
header("Location: ../nav/net_cleaning.php");
exit();