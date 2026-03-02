<?php
session_start();
require_once "../auth/config.php";

if (
    !isset($_SESSION['id']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['popup_error'] = "Invalid record selected.";
    header("Location: ../nav/fish_cage_management.php");
    exit();
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("
    DELETE FROM fish_cage_management
    WHERE id = ?
");

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['popup_success'] = "Fish Cage Management record deleted successfully.";
} else {
    $_SESSION['popup_error'] = "Failed to delete record.";
}

header("Location: ../nav/fish_cage_management.php");
exit();
