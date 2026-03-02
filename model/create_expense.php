<?php
session_start();
require_once "../auth/config.php";

$date     = $_POST['date'];
$amount   = $_POST['amount'];
$category = $_POST['category'];

$stmt = $conn->prepare("
    INSERT INTO expenses (expense_date, amount, category, created_by)
    VALUES (?, ?, ?, ?)
");

$stmt->bind_param(
    "sdsi",
    $date,
    $amount,
    $category,
    $_SESSION['id']
);

if ($stmt->execute()) {
    $_SESSION['popup_success'] = "Expense added successfully.";
} else {
    $_SESSION['popup_error'] = "Failed to add expense.";
}

$stmt->close();
header("Location: ../nav/expense.php");
exit();
