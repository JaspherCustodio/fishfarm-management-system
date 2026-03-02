<?php
session_start();
require_once '../auth/config.php';

if (!isset($_POST['id'])) {
    header("Location: ../nav/user_management.php");
    exit();
}

$id = $_POST['id'];
$name = $_POST['name'];
$email = $_POST['email'];
$mobile = $_POST['mobile_num'];
$password = $_POST['password'];
$role = $_POST['role'];

/* BASE QUERY */
$sql = "
    UPDATE users 
    SET name='$name',
        email='$email',
        mobile_num='$mobile',
        role='$role'
";

if (!empty($password)) {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $sql .= ", password='$hashed'";
}

$sql .= " WHERE id='$id'";

$conn->query($sql);

$_SESSION['popup_success'] = 'User updated successfully!';
header("Location: ../nav/user_management.php");
exit();

