<?php
session_start();
include "../auth/config.php";

if ($_SESSION['role'] !== 'admin') exit("Unauthorized");

$id = $_GET['id'];
$action = $_GET['action'];

if ($action === 'approve') {
    $sql = "UPDATE users SET status = 'approved' WHERE id = ?";
    $_SESSION['popup_success'] = "User approved successfully!";
} else {
    $sql = "DELETE FROM users WHERE id = ?";
    $_SESSION['popup_error'] = "User request rejected.";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: ../nav/user_management.php");
exit();