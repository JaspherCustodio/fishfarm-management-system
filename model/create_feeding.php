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

$schedule_id   = $_POST['schedule_id'] ?? null;
$feed_type     = $_POST['feed_type'] ?? '';
$amount        = $_POST['amount'] ?? 0;
$unit          = $_POST['unit'] ?? 'kg';
$feeding_date  = $_POST['feeding_date'] ?? null; // ✅ new
$fed_time      = $_POST['fed_time'] ?? null;
$status        = $_POST['status'] ?? 'Pending';
$assigned_to   = $_POST['assigned_to'] ?? null;  // Admin assigns employee

if (!$schedule_id || ($_SESSION['role'] === 'admin' && !$assigned_to)) {
    $_SESSION['popup_error'] = "Schedule or assigned employee missing.";
    header("Location: ../nav/feeding.php");
    exit();
}

/* ==============================
   Get cage_id from schedule
============================== */
$stmt = $conn->prepare("SELECT fish_cage FROM schedules WHERE id = ?");
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    $_SESSION['popup_error'] = "Schedule not found.";
    $stmt->close();
    header("Location: ../nav/feeding.php");
    exit();
}

$cage_id = $res->fetch_assoc()['fish_cage'];
$stmt->close();

/* ==============================
   Insert Feeding Record
============================== */
$stmt = $conn->prepare("
    INSERT INTO feedings 
        (schedule_id, cage_id, assigned_to, feeding_date, feed_type, amount, unit, fed_time, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "iiissdsss",
    $schedule_id,
    $cage_id,
    $assigned_to,
    $feeding_date,  // ✅ new
    $feed_type,
    $amount,
    $unit,
    $fed_time,
    $status
);

if ($stmt->execute()) {

    /* ==============================
       Notifications
    ============================= */

    // 🔔 Notify employee if admin assigned task
    if ($_SESSION['role'] === 'admin' && $assigned_to) {
        $message = "You have been assigned a new Feeding task for cage #$cage_id on $feeding_date.";

        $notifStmt = $conn->prepare("
            INSERT INTO notifications
            (user_id, title, message, is_read, created_at)
            VALUES (?, 'New Feeding Task Assigned', ?, 0, NOW())
        ");
        $notifStmt->bind_param("is", $assigned_to, $message);
        $notifStmt->execute();
        $notifStmt->close();
    }

    $_SESSION['popup_success'] = "Feeding record added successfully.";

} else {
    $_SESSION['popup_error'] = "Failed to add feeding record.";
}

$stmt->close();
header("Location: ../nav/feeding.php");
exit();