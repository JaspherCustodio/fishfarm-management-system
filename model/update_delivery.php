<?php
session_start();
require_once "../auth/config.php";

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
    header("Location: ../nav/delivering.php");
    exit();
}

// ==============================
// Fetch existing delivery record
// ==============================
$stmt = $conn->prepare("
    SELECT d.stocking_id, d.cage_id, d.quantity_delivered, d.sale_amount, s.current_quantity, s.fish_type
    FROM deliveries d
    JOIN stocking s ON s.id = d.stocking_id
    WHERE d.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$existing) {
    $_SESSION['popup_error'] = "Delivery record not found.";
    header("Location: ../nav/delivering.php");
    exit();
}

// ==============================
// Common POST fields
// ==============================
$delivery_date      = $_POST['delivery_date'] ?? '';
$quantity_delivered = intval($_POST['quantity_delivered'] ?? 0);
$buyer_name         = $_POST['buyer_name'] ?? '';
$sale_amount = isset($_POST['sale_amount']) && $_POST['sale_amount'] !== ''
    ? (float)$_POST['sale_amount']
    : (float)$existing['sale_amount'];
$remarks            = $_POST['remarks'] ?? '';
$status             = $_POST['status'] ?? 'Pending';

$old_quantity = intval($existing['quantity_delivered']);
$stock_current = intval($existing['current_quantity']);

// ==============================
// Validate common fields
// ==============================
if (empty($delivery_date)) {
    $_SESSION['popup_error'] = "Delivery date is required.";
    header("Location: ../nav/delivering.php");
    exit();
}

if ($quantity_delivered < 1) {
    $_SESSION['popup_error'] = "Quantity delivered must be at least 1.";
    header("Location: ../nav/delivering.php");
    exit();
}

// ==============================
// Update delivery record (common fields)
// ==============================
$stmt = $conn->prepare("
    UPDATE deliveries
    SET delivery_date = ?, quantity_delivered = ?, buyer_name = ?, 
        sale_amount = ?, remarks = ?, status = ?
    WHERE id = ?
");
$stmt->bind_param(
    "sisdssi",
    $delivery_date,
    $quantity_delivered,
    $buyer_name,
    $sale_amount,
    $remarks,
    $status,
    $id
);

if (!$stmt->execute()) {
    $_SESSION['popup_error'] = "Failed to update: " . $stmt->error;
    $stmt->close();
    header("Location: ../nav/delivering.php");
    exit();
}
$stmt->close();

// ==============================
// Admin-specific fields
// ==============================
if ($role === 'admin') {

    $assigned_to = intval($_POST['assigned_to'] ?? 0);
    $stocking_id = intval($_POST['stocking_id'] ?? 0);

    if (!$assigned_to) {
        $_SESSION['popup_error'] = "Assigned employee is required.";
        header("Location: ../nav/delivering.php");
        exit();
    }

    // Update only assigned_to
    $stmt = $conn->prepare("
        UPDATE deliveries
        SET assigned_to = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ii", $assigned_to, $id);
    $stmt->execute();
    $stmt->close();

    // Notify employee
    $title = "Delivery Task Updated";
    $message = "A delivery task has been updated and assigned to you.";

    $notifStmt = $conn->prepare("
        INSERT INTO notifications (user_id, title, message)
        VALUES (?, ?, ?)
    ");
    $notifStmt->bind_param("iss", $assigned_to, $title, $message);
    $notifStmt->execute();
    $notifStmt->close();
}

// ==============================
// Adjust stocking quantity
// ==============================
$diff = $quantity_delivered - $old_quantity;

if ($diff != 0) {

    $new_stock_qty = $stock_current - $diff;

    if ($new_stock_qty < 0) {
        $new_stock_qty = 0;
    }

    $updateStock = $conn->prepare("
        UPDATE stocking SET current_quantity = ?
        WHERE id = ?
    ");
    $updateStock->bind_param("ii", $new_stock_qty, $existing['stocking_id']);
    $updateStock->execute();
    $updateStock->close();
}

// ==============================
// Notify admin if employee completed
// ==============================
if ($role !== 'admin' && $status === "Completed") {

    $adminQuery = $conn->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $admin = $adminQuery->fetch_assoc();

    if ($admin) {
        $admin_id = $admin['id'];
        $name = $_SESSION['name'] ?? $_SESSION['email'] ?? 'An employee';
        $message = "$name completed a Delivering task";

        $notifStmt = $conn->prepare("
            INSERT INTO notifications (user_id, message, is_read, created_at)
            VALUES (?, ?, 0, NOW())
        ");
        $notifStmt->bind_param("is", $admin_id, $message);
        $notifStmt->execute();
        $notifStmt->close();
    }
}

$_SESSION['popup_success'] = "Delivery updated successfully.";
header("Location: ../nav/delivering.php");
exit();