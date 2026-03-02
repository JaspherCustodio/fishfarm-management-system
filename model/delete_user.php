<?php
session_start();
require_once '../auth/config.php';

/* SECURITY CHECK */
if (
    !isset($_SESSION['email']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: ../index.php");
    exit();
}

/* VALIDATE ID */
if (!isset($_GET['id'])) {
    header("Location: ../nav/user_management.php");
    exit();
}

$id = $_GET['id'];

/* OPTIONAL: PREVENT SELF-DELETION */
if ($id == $_SESSION['id']) {
    $_SESSION['popup_error'] = "You cannot delete your own account.";
    header("Location: ../nav/user_management.php");
    exit();
}

/* DELETE USER */
$conn->query("DELETE FROM users WHERE id='$id'");

/* SUCCESS MESSAGE */
$_SESSION['popup_success'] = "User deleted successfully!";

header("Location: ../nav/user_management.php");
exit();
