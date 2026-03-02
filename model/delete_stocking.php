<?php
session_start();
require_once '../auth/config.php';

if (
    !isset($_SESSION['id']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: ../index.php");
    exit();
}

$id = $_GET['id'] ?? '';

if (empty($id)) {
    $_SESSION['popup_error'] = "Invalid stocking record.";
    header("Location: ../nav/stocking.php");
    exit();
}

$stmt = $conn->prepare("DELETE FROM stocking WHERE id = ?");
$stmt->bind_param("i", $id);

try {

    if ($stmt->execute()) {
        $_SESSION['popup_success'] = "Stocking record deleted successfully.";
    } else {
        $_SESSION['popup_error'] = "Failed to delete stocking record.";
    }

} catch (mysqli_sql_exception $e) {

    // FOREIGN KEY ERROR CODE = 1451
    if ($e->getCode() == 1451) {
        $_SESSION['popup_error'] = "Cannot delete this stocking record because it is used in transferring/delivering.";
    } else {
        $_SESSION['popup_error'] = "Database error occurred.";
    }
}

$stmt->close();
header("Location: ../nav/stocking.php");
exit();
