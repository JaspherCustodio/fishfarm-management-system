<?php 
session_start();
include "../auth/config.php";
include "../model/user.php";

// Security Check
if ($_SESSION['role'] !== "admin") {
    header("Location: ../index.php");
    exit();
}

// Fetch only users with 'pending' status
$pending_users = get_pending_users($conn); 
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Approvals</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="dashboard-page">
    <?php include "../inc/header.php" ?>
    <div class="body">
        <?php include "../inc/nav.php" ?>
        <section class="section-1">
            <div class="content-header">
                <h4><a href="user_management.php" style="color: inherit; text-decoration: none;"><i class="fa-solid fa-arrow-left"></i></a> Approval Queue</h4>
            </div>
            
            <div class="content-card">
                <h3 class="title">Waiting for Access</h3>
                
                <?php if ($pending_users != 0) { ?>
                <table class="main-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_users as $p) { ?>
                        <tr>
                            <td><?=$p['name']?></td>
                            <td><?=$p['email']?></td>
                            <td><?=$p['role']?></td>
                            <td class="col-actions">
                                <a href="../model/approve_user.php?id=<?=$p['id']?>&action=approve" class="edit-btn" style="background:#27ae60; color:white;"><i class="fa-solid fa-check"></i></a>
                                <a href="../model/approve_user.php?id=<?=$p['id']?>&action=reject" class="delete-btn" onclick="return confirm('Reject this user?')"><i class="fa-solid fa-xmark"></i></a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php } else { ?>
                    <p style="color: black; padding: 20px; text-align: center;">No users currently waiting for approval.</p>
                <?php } ?>
            </div>
        </section>
    </div>
</body>
</html>