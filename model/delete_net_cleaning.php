<?php
session_start();
require_once '../auth/config.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$id = $_GET['id'] ?? '';

if (empty($id)) {
    $_SESSION['popup_error'] = "Invalid record.";
    header("Location: ../nav/net_cleaning.php");
    exit();
}

$stmt = $conn->prepare("DELETE FROM net_cleaning WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['popup_success'] = "Net Cleaning record deleted.";
} else {
    $_SESSION['popup_error'] = "Failed to delete record.";
}

$stmt->close();
header("Location: ../nav/net_cleaning.php");
exit();
