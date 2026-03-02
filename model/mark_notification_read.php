<?php
session_start();
require_once "../auth/config.php";

$id = $_GET['id'] ?? '';

if (!empty($id) && isset($_SESSION['id'])) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $_SESSION['id']);
    $stmt->execute();
    $stmt->close();
}