<?php
session_start();
require_once "../auth/config.php";

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$schedule_id = intval($_POST['schedule_id']);
$assigned_to = intval($_POST['assigned_to']);

/* 🔹 GET CAGE ID FROM SCHEDULE */
$scheduleData = $conn->query("
    SELECT fish_cage, schedule_datetime
    FROM schedules
    WHERE id = $schedule_id
")->fetch_assoc();

if (!$scheduleData) {
    $_SESSION['popup_error'] = "Invalid schedule selected.";
    header("Location: ../nav/sampling.php");
    exit();
}

$cage_id = intval($scheduleData['fish_cage']);

/* 🔹 Default values */
$sampling_date = null;
$fish_type     = '';
$avg_weight    = 0;
$weight_unit   = 'g';
$avg_length    = 0;
$length_unit   = 'cm';
$status        = 'Pending';

/* 1️⃣ INSERT (NOW INCLUDING cage_id) */
$stmt = $conn->prepare("
    INSERT INTO samplings
    (schedule_id, cage_id, assigned_to, sampling_date, fish_type,
     avg_weight, weight_unit, avg_length, length_unit, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "iiissdssds",
    $schedule_id,
    $cage_id,
    $assigned_to,
    $sampling_date,
    $fish_type,
    $avg_weight,
    $weight_unit,
    $avg_length,
    $length_unit,
    $status
);

$stmt->execute();

/* 2️⃣ Get cage name for notification */
$cageInfo = $conn->query("
    SELECT fc.cage_name
    FROM fish_cages fc
    WHERE fc.id = $cage_id
")->fetch_assoc();

$datetime = date('M d Y h:i A', strtotime($scheduleData['schedule_datetime']));
$cageName = htmlspecialchars($cageInfo['cage_name']);

/* 3️⃣ Insert notification */
$title   = "New Sampling Task Assigned";
$message = "You have been assigned a Sampling task for cage \"$cageName\" scheduled on $datetime.";

$notifStmt = $conn->prepare("
    INSERT INTO notifications (user_id, title, message)
    VALUES (?, ?, ?)
");

$notifStmt->bind_param("iss", $assigned_to, $title, $message);
$notifStmt->execute();
$notifStmt->close();

/* 4️⃣ Success popup */
$_SESSION['popup_success'] = "Sampling record added successfully and notification sent.";
header("Location: ../nav/sampling.php");
exit();