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
    header("Location: ../nav/net_checking.php");
    exit();
}

$stmt = $conn->prepare("DELETE FROM net_checking WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['popup_success'] = "Net Checking record deleted.";
} else {
    $_SESSION['popup_error'] = "Delete failed.";
}

$stmt->close();
header("Location: ../nav/net_checking.php");
exit();
