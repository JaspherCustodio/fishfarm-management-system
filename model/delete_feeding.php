<?php
session_start();
require_once '../auth/config.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$id = $_GET['id'] ?? '';

if (!$id) {
    $_SESSION['popup_error'] = "Invalid feeding record!";
    header("Location: ../nav/feeding.php");
    exit();
}

$stmt = $conn->prepare("DELETE FROM feedings WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['popup_success'] = "Feeding record deleted successfully!";
} else {
    $_SESSION['popup_error'] = "Failed to delete feeding record.";
}

$stmt->close();
header("Location: ../nav/feeding.php");
exit();
