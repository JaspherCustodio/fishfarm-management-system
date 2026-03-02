<?php
session_start();
require_once '../auth/config.php';

$id = $_GET['id'];
$conn->query("DELETE FROM fish_cages WHERE id='$id'");

$_SESSION['popup_success'] = "Fish cage deleted";
header("Location: ../nav/fish_cage.php");
exit();
