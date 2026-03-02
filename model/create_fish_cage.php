<?php
session_start();
require_once '../auth/config.php';

$cage_name = trim($_POST['cage_name']);

if ($cage_name === '') {
    $_SESSION['popup_error'] = "Cage name is required";
    header("Location: ../nav/fish_cage.php");
    exit();
}

$check = $conn->query("SELECT id FROM fish_cages WHERE cage_name='$cage_name'");
if ($check->num_rows > 0) {
    $_SESSION['popup_error'] = "Cage already exists";
    header("Location: ../nav/fish_cage.php");
    exit();
}

$conn->query("INSERT INTO fish_cages (cage_name) VALUES ('$cage_name')");

$_SESSION['popup_success'] = "Fish cage added successfully";
header("Location: ../nav/fish_cage.php");
exit();
