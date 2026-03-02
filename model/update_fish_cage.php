<?php
session_start();
require_once '../auth/config.php';

$id = $_POST['id'];
$cage_name = trim($_POST['cage_name']);

$conn->query("UPDATE fish_cages SET cage_name='$cage_name' WHERE id='$id'");

$_SESSION['popup_success'] = "Fish cage updated";
header("Location: ../nav/fish_cage.php");
exit();
