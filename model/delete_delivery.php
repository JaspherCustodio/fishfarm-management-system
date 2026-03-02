<?php
session_start();
require "../auth/config.php";

// Only admin can delete
if (
    !isset($_SESSION['email']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['popup_error'] = "Invalid delivery ID.";
    header("Location: ../nav/delivering.php");
    exit();
}

$id = (int) $_GET['id'];

// Optional: check if record exists
$check = $conn->prepare("SELECT id FROM deliveries WHERE id = ?");
$check->bind_param("i", $id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    $_SESSION['popup_error'] = "Delivery record not found.";
    header("Location: ../nav/delivering.php");
    exit();
}

// Delete
$stmt = $conn->prepare("DELETE FROM deliveries WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['popup_success'] = "Delivery record deleted successfully.";
} else {
    $_SESSION['popup_error'] = "Failed to delete delivery record.";
}

$stmt->close();
$check->close();

header("Location: ../nav/delivering.php");
exit();
