<?php
session_start();
require_once '../auth/config.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$id = $_GET['id'] ?? '';

if (empty($id)) {
    $_SESSION['popup_error'] = "Invalid transfer ID.";
    header("Location: ../nav/transferring.php");
    exit();
}

$stmt = $conn->prepare("DELETE FROM transfers WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['popup_success'] = "Transfer deleted successfully.";
} else {
    $_SESSION['popup_error'] = "Failed to delete transfer.";
}

$stmt->close();
header("Location: ../nav/transferring.php");
exit();
