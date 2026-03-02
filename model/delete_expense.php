<?php
session_start();
require_once "../auth/config.php";

/* ✅ Security Check */
if (
    !isset($_SESSION['email']) ||
    !isset($_SESSION['role']) ||
    !isset($_SESSION['id']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: ../index.php");
    exit();
}

/* ✅ Validate ID */
$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    $_SESSION['popup_error'] = "Invalid expense ID.";
    header("Location: ../nav/expense.php");
    exit();
}

/* ✅ Prepare Delete Statement */
$stmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['popup_success'] = "Expense deleted successfully.";
} else {
    $_SESSION['popup_error'] = "Failed to delete expense.";
}

$stmt->close();
header("Location: ../nav/expense.php");
exit();
