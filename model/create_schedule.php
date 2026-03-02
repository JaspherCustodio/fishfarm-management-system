<?php
session_start();
require_once '../auth/config.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$fish_cage = $_POST['fish_cage'] ?? '';
$task      = $_POST['task'] ?? '';
$dateTime  = $_POST['schedule_datetime'] ?? '';
$admin_id  = $_SESSION['id'];

if (empty($fish_cage) || empty($task) || empty($dateTime)) {
    $_SESSION['popup_error'] = "All fields are required!";
    header("Location: ../nav/scheduler.php");
    exit();
}

/* 1️⃣ Create schedule */
$stmt = $conn->prepare(
    "INSERT INTO schedules (fish_cage, task, schedule_datetime, created_by)
     VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("isss", $fish_cage, $task, $dateTime, $admin_id);
$stmt->execute();

$schedule_id = $conn->insert_id;
$stmt->close();

/* 2️⃣ Task-specific inserts */
if ($task === 'Feeding') {

    $stmt = $conn->prepare(
        "INSERT INTO feedings (schedule_id, cage_id, feed_type, amount, unit, status)
         VALUES (?, ?, '', 0, 'kg', 'Pending')"
    );
    $stmt->bind_param("ii", $schedule_id, $fish_cage);
    $stmt->execute();
    $stmt->close();

} elseif ($task === 'Sampling') {

    $stmt = $conn->prepare(
        "INSERT INTO samplings (schedule_id, cage_id, status)
         VALUES (?, ?, 'Pending')"
    );
    $stmt->bind_param("ii", $schedule_id, $fish_cage);
    $stmt->execute();
    $stmt->close();

} else {

    $taskTableMap = [
        'Net Cleaning'           => 'net_cleaning',
        'Net Repairing'          => 'net_repairing',
        'Net Checking'           => 'net_checking',
        'Stocking'               => 'stocking',
        'Fish Cage Management'   => 'fish_cage_management'
    ];

    if (isset($taskTableMap[$task])) {
        $table = $taskTableMap[$task];

        // All task tables now just insert schedule_id and status
        $sql = "INSERT INTO $table (schedule_id, status) VALUES (?, 'Pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $schedule_id);
        $stmt->execute();
        $stmt->close();
    }
}

$_SESSION['popup_success'] = "Schedule created successfully!";
header("Location: ../nav/scheduler.php");
exit();