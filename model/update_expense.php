<?php
session_start();
require_once "../auth/config.php";

$id       = $_POST['id'];
$date     = $_POST['date'];
$amount   = $_POST['amount'];
$category = $_POST['category'];

$stmt = $conn->prepare("
    UPDATE expenses
    SET expense_date=?, amount=?, category=?
    WHERE id=?
");

$stmt->bind_param("sdsi", $date, $amount, $category, $id);

if ($stmt->execute()) {
    $_SESSION['popup_success'] = "Expense updated.";
} else {
    $_SESSION['popup_error'] = "Failed to update.";
}

$stmt->close();
header("Location: ../nav/expense.php");
exit();
