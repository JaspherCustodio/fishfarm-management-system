<?php

session_start();

$error =[
    'login' => $_SESSION['login_error'] ?? '',
    'register' => $_SESSION['register_error'] ?? ''
];
// Only clear these specific keys
unset($_SESSION['login_error'], $_SESSION['register_error'], $_SESSION['active_form']);
$activeForm = $_SESSION['active_form'] ?? 'login';

session_unset();

function showError($error) {
    return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}

function isActiveForm($formName, $activeForm) {
    return $formName === $activeForm ? 'active' : '';
}

?>

<!DOCTYPE html>
<html>
    
<head>
    <link rel="icon" type="image/png" href="../assets/img/dpa-logo.png">
    <title>DPA</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0 ">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>

<body class="auth-page">
    <div class="container">
        <div class="form-box <?= isActiveForm('login', $activeForm); ?>" id="login-form">
            <form action="auth/login_register.php" method="post">
                <div class="logo-circle">
                    <img src="assets/img/dpa-logo.png" alt="DPA Logo" class="logo-img">
                </div>
                <h2>Login</h2>
                <p class="auth-note">Authorized access only. Please contact your administrator for account issues.</p>
                <?= showError($error['login']); ?>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
                <p>Don't have an account? <a href="#" onclick="showForm('register-form')">Register</a></p>
            </form>
        </div>

        <div class="form-box <?= isActiveForm('register', $activeForm); ?>" id="register-form">
            <form action="auth/login_register.php" method="post">
                <div class="logo-circle">
                    <img src="assets/img/dpa-logo.png" alt="DPA Logo" class="logo-img">
                </div>
                <h2>Register</h2>
                <?= showError($error['register']); ?>
                <p class="auth-note">Registration is for Employee accounts only.</p>
                <input type="text" name="name" placeholder="Full name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="mobile_num" placeholder="Contact No" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="hidden" name="role" value="user">
                
                <button type="submit" name="register">Register</button>
                
                <p>Already have an account? <a href="#" onclick="showForm('login-form')">Login</a></p>
            </form>
        </div>
    </div>

    <script src="assets/script.js"></script>

</body>

</html>
