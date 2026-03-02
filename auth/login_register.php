<?php
session_start();
require_once 'config.php';

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $mobile_num = $_POST['mobile_num'] ?? ''; 

    // Add a quick check before the database insert
    if (empty($mobile_num)) {
        $_SESSION['register_error'] = "Contact number is required.";
        header("Location: ../index.php"); // Adjust path if necessary
        exit();
    }
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $fromAdmin = isset($_POST['from_admin']);
    $redirect = $fromAdmin ? "../nav/user_management.php" : "../index.php";
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $checkEmail = $stmt->get_result();

    if ($checkEmail->num_rows > 0) {
        $_SESSION['register_error'] = 'Email is already registered!';
        if ($fromAdmin) $_SESSION['popup_error'] = 'Email is already registered!';
    } else {
        // QUEUE LOGIC: If admin adds user, status is 'approved'. If guest registers, 'pending'.
        $status = $fromAdmin ? 'approved' : 'pending';
        
        $insert = $conn->prepare("INSERT INTO users (name, email, mobile_num, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->bind_param("ssssss", $name, $email, $mobile_num, $password, $role, $status);
        
        if ($insert->execute()) {
            if ($fromAdmin) {
                $_SESSION['popup_success'] = 'User added successfully!';
            } else {
                $_SESSION['login_error'] = 'Registration successful! Please wait for Admin approval.';
            }
            $insert->close();
        }
    }
    header("Location: $redirect");
    exit();
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            
            // BLOCKING LOGIC: Stop users who are not approved
            if ($user['status'] === 'pending') {
                $_SESSION['login_error'] = "Your account is awaiting admin approval.";
                header("Location: ../index.php");
                exit();
            }

            $_SESSION['id']    = $user['id'];
            $_SESSION['name']  = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role']  = $user['role'];

            header("Location: ../nav/dashboard.php");
            exit();
        }
    }
    $_SESSION['login_error'] = 'Incorrect email or password';
    header("Location: ../index.php");
    exit();
}
?>