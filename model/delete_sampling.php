<?php
session_start();
include "../auth/config.php"; // database connection

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM samplings WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['popup_success'] = "Sampling record deleted successfully.";
    } else {
        $_SESSION['popup_error'] = "Failed to delete the sampling record.";
    }

    $stmt->close();
}

header("Location: ../nav/sampling.php");
exit();
