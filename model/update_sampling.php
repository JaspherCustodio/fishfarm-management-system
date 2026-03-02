<?php
session_start();
require_once "../auth/config.php";

/* ==============================
   Validate Session
============================== */
if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    header("Location: ../index.php");
    exit();
}

/* ==============================
   Validate Request
============================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../nav/sampling.php");
    exit();
}

$id = $_POST['id'] ?? '';

if (empty($id)) {
    $_SESSION['popup_error'] = "Invalid request.";
    header("Location: ../nav/sampling.php");
    exit();
}

/* ==============================
   Common Fields
============================== */
$status = $_POST['status'] ?? 'Pending';

if ($_SESSION['role'] === 'admin') {

    /* 🔹 Admin can update everything including assigned_to */
    $assigned_to   = isset($_POST['assigned_to']) && $_POST['assigned_to'] !== ''
        ? intval($_POST['assigned_to'])
        : null;

    $sampling_date = $_POST['sampling_date'] ?: null;
    $fish_type     = $_POST['fish_type'] ?: '';
    $avg_weight    = $_POST['avg_weight'] ?: 0;
    $weight_unit   = $_POST['weight_unit'] ?: 'g';
    $avg_length    = $_POST['avg_length'] ?: 0;
    $length_unit   = $_POST['length_unit'] ?: 'cm';

    /* 🔹 Get old assigned_to before update */
    $current = $conn->query("
        SELECT assigned_to 
        FROM samplings 
        WHERE id = $id
    ")->fetch_assoc();

    $old_assigned = $current['assigned_to'] ?? null;

    $stmt = $conn->prepare("
        UPDATE samplings SET
            assigned_to = ?,
            sampling_date = ?,
            fish_type = ?,
            avg_weight = ?,
            weight_unit = ?,
            avg_length = ?,
            length_unit = ?,
            status = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "issdssdsi",
        $assigned_to,
        $sampling_date,
        $fish_type,
        $avg_weight,
        $weight_unit,
        $avg_length,
        $length_unit,
        $status,
        $id
    );

} else {

    /* 🔹 Employee can update everything except assigned_to */
    $sampling_date = $_POST['sampling_date'] ?: null;
    $fish_type     = $_POST['fish_type'] ?: '';
    $avg_weight    = $_POST['avg_weight'] ?: 0;
    $weight_unit   = $_POST['weight_unit'] ?: 'g';
    $avg_length    = $_POST['avg_length'] ?: 0;
    $length_unit   = $_POST['length_unit'] ?: 'cm';

    $stmt = $conn->prepare("
        UPDATE samplings SET
            sampling_date = ?,
            fish_type = ?,
            avg_weight = ?,
            weight_unit = ?,
            avg_length = ?,
            length_unit = ?,
            status = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ssdssdsi",
        $sampling_date,
        $fish_type,
        $avg_weight,
        $weight_unit,
        $avg_length,
        $length_unit,
        $status,
        $id
    );
}

/* ==============================
   Execute Update
============================== */
if ($stmt->execute()) {

    /* 🔔 Notify if Admin changed assigned_to */
    if ($_SESSION['role'] === 'admin'
        && isset($assigned_to)
        && $assigned_to != $old_assigned
    ) {

        $message = "You have been assigned a new Sampling task.";

        $notifStmt = $conn->prepare("
            INSERT INTO notifications 
            (user_id, title, message, is_read, created_at)
            VALUES (?, 'New Sampling Task Assigned', ?, 0, NOW())
        ");

        $notifStmt->bind_param("is", $assigned_to, $message);
        $notifStmt->execute();
        $notifStmt->close();
    }

    /* 🔔 If employee completed task → notify admin */
    if ($_SESSION['role'] !== 'admin' && $status === "Completed") {

        $adminQuery = $conn->query("
            SELECT id 
            FROM users 
            WHERE role = 'admin' 
            LIMIT 1
        ");

        if ($admin = $adminQuery->fetch_assoc()) {

            $admin_id = $admin['id'];
            $message = $_SESSION['name'] . " completed a Sampling task.";

            $notifStmt = $conn->prepare("
                INSERT INTO notifications
                (user_id, title, message, is_read, created_at)
                VALUES (?, 'Sampling Task Completed', ?, 0, NOW())
            ");

            $notifStmt->bind_param("is", $admin_id, $message);
            $notifStmt->execute();
            $notifStmt->close();
        }
    }

    $_SESSION['popup_success'] = "Sampling updated successfully.";

} else {
    $_SESSION['popup_error'] = "Failed to update.";
}

$stmt->close();
header("Location: ../nav/sampling.php");
exit();