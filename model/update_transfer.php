<?php
session_start();
require_once '../auth/config.php';

// ==============================
// Session check
// ==============================
if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    header("Location: ../index.php");
    exit();
}

$id     = intval($_POST['id'] ?? 0);
$role   = $_SESSION['role'];
$userId = intval($_SESSION['id']);

if (!$id) {
    $_SESSION['popup_error'] = "Invalid request.";
    header("Location: ../nav/transferring.php");
    exit();
}

// ==============================
// Fetch existing transfer record
// ==============================
$stmt = $conn->prepare("
    SELECT t.stocking_id, t.from_cage, t.to_cage, t.quantity_before, t.quantity_after,
           s.current_quantity, s.fish_type, s.standard_fingerlings, s.schedule_id AS original_schedule
    FROM transfers t
    JOIN stocking s ON s.id = t.stocking_id
    WHERE t.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$existing) {
    $_SESSION['popup_error'] = "Transfer record not found.";
    header("Location: ../nav/transferring.php");
    exit();
}

// ==============================
// Common POST fields
// ==============================
$date_transferred = $_POST['date_transferred'] ?? '';
$qty_after        = intval($_POST['quantity_after'] ?? 0);
$remarks          = $_POST['remarks'] ?? '';
$status           = $_POST['status'] ?? 'Pending';

$quantity_before = intval($existing['quantity_before']);
$old_qty_after   = intval($existing['quantity_after'] ?? 0);
$mortality       = $quantity_before - $qty_after;

// ==============================
// Validate quantity_after
// ==============================
if ($qty_after > $quantity_before) {
    $_SESSION['popup_error'] = "Quantity after cannot exceed quantity before ({$quantity_before}).";
    header("Location: ../nav/transferring.php");
    exit();
}

// ==============================
// Calculate the difference
// ==============================
$transfer_diff = $qty_after - $old_qty_after;

// ==============================
// Update transfer record
// ==============================
$stmt = $conn->prepare("
    UPDATE transfers SET
        quantity_after = ?, mortality = ?, date_transferred = ?, remarks = ?, status = ?
    WHERE id = ?
");
$stmt->bind_param("iisssi", $qty_after, $mortality, $date_transferred, $remarks, $status, $id);
$stmt->execute();
$stmt->close();

// ==============================
// Admin-specific fields
// ==============================
if ($role === 'admin') {
    $to_cage     = intval($_POST['to_cage'] ?? 0);
    $assigned_to = intval($_POST['assigned_to'] ?? 0);

    if (!$to_cage || !$assigned_to) {
        $_SESSION['popup_error'] = "TO cage and assigned employee are required.";
        header("Location: ../nav/transferring.php");
        exit();
    }

    // Update transfer record with TO cage and assigned_to
    $stmt = $conn->prepare("
        UPDATE transfers SET to_cage = ?, assigned_to = ? WHERE id = ?
    ");
    $stmt->bind_param("iii", $to_cage, $assigned_to, $id);
    $stmt->execute();
    $stmt->close();
}

// ==============================
// Adjust FROM cage stock
// ==============================
$new_from_qty = intval($existing['current_quantity']) - $transfer_diff;
if ($new_from_qty < 0) $new_from_qty = 0;

$updateFrom = $conn->prepare("UPDATE stocking SET current_quantity = ? WHERE id = ?");
$updateFrom->bind_param("ii", $new_from_qty, $existing['stocking_id']);
$updateFrom->execute();
$updateFrom->close();

// ==============================
// If transfer_diff > 0, add to TO cage
// If transfer_diff < 0, return to FROM cage
// ==============================
if ($transfer_diff !== 0) {
    $task = "Stocking";
    $target_cage = $role === 'admin' ? $to_cage : $existing['to_cage'];
    $assigned_to = $role === 'admin' ? $assigned_to : $existing['assigned_to'];

    if ($transfer_diff > 0) {
        // New schedule for the additional stock
        $stockScheduleStmt = $conn->prepare("
            INSERT INTO schedules (fish_cage, task, schedule_datetime, assigned_to, created_by)
            VALUES (?, ?, NOW(), ?, ?)
        ");
        $stockScheduleStmt->bind_param("isii", $target_cage, $task, $assigned_to, $userId);
        $stockScheduleStmt->execute();
        $new_schedule_id = $conn->insert_id;
        $stockScheduleStmt->close();

        // Insert new stocking for the difference
        $source_transfer = "Stocking #{$existing['stocking_id']}";

$stockInsertStmt = $conn->prepare("
    INSERT INTO stocking
    (schedule_id, date_stocked, source_of_fingerlings, fish_type, standard_fingerlings,
     number_of_fingerlings, current_quantity, status)
    VALUES (?, CURDATE(), ?, ?, ?, ?, ?, 'Completed')
");

$stockInsertStmt->bind_param(
    "isssii",
    $new_schedule_id,
    $source_transfer,
    $existing['fish_type'],
    $existing['standard_fingerlings'],
    $transfer_diff,
    $transfer_diff
);
$stockInsertStmt->execute();
$stockInsertStmt->close();
}
}

// ==============================
// Notify assigned employee (if admin)
// ==============================
if ($role === 'admin' && $assigned_to > 0) {
    $title   = "New Transfer Task Assigned";
    $message = "You have been assigned a Transfer task.";
    $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
    $notifStmt->bind_param("iss", $assigned_to, $title, $message);
    $notifStmt->execute();
    $notifStmt->close();
}

// ==============================
// Notify admin if employee completed
// ==============================
if ($role !== 'admin' && $status === "Completed") {
    $adminQuery = $conn->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $admin = $adminQuery->fetch_assoc();
    if ($admin) {
        $admin_id = $admin['id'];
        $message  = $_SESSION['name'] . " completed a Transferring task";

        $notifStmt = $conn->prepare("
            INSERT INTO notifications (user_id, message, is_read, created_at)
            VALUES (?, ?, 0, NOW())
        ");
        $notifStmt->bind_param("is", $admin_id, $message);
        $notifStmt->execute();
        $notifStmt->close();
    }
}

$_SESSION['popup_success'] = "Transferring updated successfully.";
header("Location: ../nav/transferring.php");
exit();