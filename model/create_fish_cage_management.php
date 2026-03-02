<?php
session_start();
require_once "../auth/config.php";

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$schedule_id = intval($_POST['schedule_id']);
$assigned_to = intval($_POST['assigned_to']);

// Set defaults for the rest
$date          = null; // today
$result        = '';
$optimum_level = '';
$remarks       = '';
$status        = 'Pending';

// 1️⃣ Insert into fish_cage_management
$stmt = $conn->prepare("
    INSERT INTO fish_cage_management
    (schedule_id, assigned_to, date, result, optimum_level, remarks, status)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param(
    "iisssss",
    $schedule_id,
    $assigned_to,
    $date,
    $result,
    $optimum_level,
    $remarks,
    $status
);
$stmt->execute();

// 2️⃣ Get schedule info to include in the notification
$scheduleInfo = $conn->query("
    SELECT s.schedule_datetime, fc.cage_name 
    FROM schedules s
    JOIN fish_cages fc ON fc.id = s.fish_cage
    WHERE s.id = $schedule_id
")->fetch_assoc();

$datetime = date('M d Y h:i A', strtotime($scheduleInfo['schedule_datetime']));
$cageName = htmlspecialchars($scheduleInfo['cage_name']);

// 3️⃣ Insert notification
$title   = "New Fish Cage Task Assigned";
$message = "You have been assigned a Fish Cage Management task for cage \"$cageName\" scheduled on $datetime.";
$notifStmt = $conn->prepare("
    INSERT INTO notifications (user_id, title, message)
    VALUES (?, ?, ?)
");
$notifStmt->bind_param("iss", $assigned_to, $title, $message);
$notifStmt->execute();
$notifStmt->close();

// 4️⃣ Success popup
$_SESSION['popup_success'] = "Fish Cage record added successfully and notification sent.";
header("Location: ../nav/fish_cage_management.php");
exit();