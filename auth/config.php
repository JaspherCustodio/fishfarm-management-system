<?php

$host = "localhost";
$user = "root";
$password = "12345678";
$database = "users_db";

// $conn = new mysqli($host, $user, $password, $database);

$conn = new mysqli("localhost", "root", "", "users_db");

if ($conn->connect_error) {
    die("Connection failed: ". $conn->connect_error);
}

?>