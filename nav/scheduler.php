<?php 
session_start();

include "../auth/config.php";
include "../model/fish_cage.php";
$cages = get_all_fish_cages($conn);

$popupSuccess = $_SESSION['popup_success'] ?? '';
$popupError   = $_SESSION['popup_error'] ?? '';

unset($_SESSION['popup_success'], $_SESSION['popup_error']);

if (
    !isset($_SESSION['email']) ||
    !isset($_SESSION['role']) ||
    !isset($_SESSION['id']) ||
    $_SESSION['role'] !== "admin"
) {
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../assets/img/dpa-logo.png">
    <title>Scheduler</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="dashboard-page">

    <?php include "../inc/header.php" ?>

    <div class="body">
        <?php include "../inc/nav.php" ?>

        <section class="section-1">
            <div class="content-header">
                <h4>Create Schedule</h4>
            </div>
            
            <div class="content-form">
                <form action="../model/create_schedule.php" method="POST">

                    <label>CAGE NAME</label>
                    <select name="fish_cage" required>
                        <option value="">-- Select Cage --</option>
                        <?php foreach ($cages as $cage): ?>
                            <option value="<?= $cage['id'] ?>">
                                <?= htmlspecialchars($cage['cage_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>SELECT TASK</label>
                    <select name="task" required>
                        <option value="">-- Select Task --</option>

                        <optgroup label="Record Management">
                            <option value="Fish Cage Management">Fish Cage Management</option>
                            <option value="Sampling">Sampling</option>
                            <option value="Feeding">Feeding</option>
                            <option value="Stocking">Stocking</option>
                        </optgroup>

                        <optgroup label="Fish Net Maintenance">
                            <option value="Net Cleaning">Net Cleaning</option>
                            <option value="Net Repairing">Net Repairing</option>
                            <option value="Net Checking">Net Checking</option>
                        </optgroup>
                    </select>

                    <label>SET SCHEDULE (DATE & TIME)</label>
                    <input type="datetime-local" name="schedule_datetime" required>

                    <div class="modal-actions">
                        <button type="submit">Create Schedule</button>
                    </div>
                </form>
            </div>
            <?php include "../inc/footer.php" ?>
        </section>
    </div> 

    <?php if ($popupSuccess || $popupError): ?>
    <div id="systemPopup" class="popup-overlay">
        <div class="popup-box <?= $popupSuccess ? 'success' : 'error' ?>">
            <div class="popup-icon"><?= $popupSuccess ? '✓' : '⚠' ?></div>
            <h3><?= $popupSuccess ? 'Success' : 'Warning' ?></h3>
            <p><?= $popupSuccess ?: $popupError ?></p>
            <button onclick="closePopup()">OK</button>
        </div>
    </div>
    <?php endif; ?>

    <script>
        function closePopup() {
            document.getElementById("systemPopup").remove();
        }

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                if (!confirm("Are you sure you want to delete this user?")) {
                    e.preventDefault();
                }
            });
        });
    </script>

</body>
</html>