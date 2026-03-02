<?php

session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
	<link rel="icon" type="image/png" href="../assets/img/dpa-logo.png">
	<title>DPA</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

	<link rel="stylesheet" href="../assets/style.css">
</head>
<body class="dashboard-page">

	<?php include "../inc/header.php" ?>

	<div class="body">
		
		<?php include "../inc/nav.php" ?>

		<section class="section-1">
			<div class="box">
				<?php if (isset($_SESSION['role']) && $_SESSION['role'] === "admin") { ?>
				<h1>This is an <span>admin</span> page</h1>
				<?php }else{ ?>
				<h1>This is an <span>user</span> page</h1>
				<?php } ?>
				<button onclick="window.location.href='../auth/logout.php'">Logout</button>
			</div>
		</section>
	</div>

</body>
</html>