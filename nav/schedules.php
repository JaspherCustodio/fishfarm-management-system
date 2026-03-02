<?php
session_start();
if (!isset($_SESSION['email']) || !isset($_SESSION['role']) || !isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

include "../auth/config.php";

$isAdmin = $_SESSION['role'] === 'admin';
$userId = (int)$_SESSION['id'];

$filter = $_GET['filter'] ?? '';

$whereClause = "WHERE 1=1"; // default (show all)

// Filters
if ($filter === 'last30days') {
    $whereClause .= " AND s.schedule_datetime >= CURDATE() - INTERVAL 30 DAY";
}

if ($filter === 'due_this_week') {
    $whereClause .= " AND s.schedule_datetime BETWEEN CURDATE() AND CURDATE() + INTERVAL 7 DAY";
}

// Non-admins see only their tasks
if (!$isAdmin) {
    $whereClause .= " AND s.assigned_to = $userId";
}

// Main query
$sql = "
SELECT 
    s.id,
    s.schedule_datetime,
    fc.cage_name,
    u.name AS employee_name,
    CASE
        WHEN fcm.schedule_id IS NOT NULL THEN 'Fish Cage Management'
        WHEN st.schedule_id IS NOT NULL THEN 'Stocking'
        WHEN tr.schedule_id IS NOT NULL THEN 'Transferring'
        WHEN sm.schedule_id IS NOT NULL THEN 'Sampling'
        WHEN fd.schedule_id IS NOT NULL THEN 'Feeding'
        WHEN dl.schedule_id IS NOT NULL THEN 'Delivering'
        WHEN nc.schedule_id IS NOT NULL THEN 'Net Cleaning'
        WHEN nck.schedule_id IS NOT NULL THEN 'Net Checking'
        WHEN nr.schedule_id IS NOT NULL THEN 'Net Repairing'
        ELSE '-'
    END AS type
FROM schedules s
LEFT JOIN fish_cages fc ON fc.id = s.fish_cage
LEFT JOIN users u ON u.id = s.assigned_to
LEFT JOIN fish_cage_management fcm ON fcm.schedule_id = s.id
LEFT JOIN stocking st ON st.schedule_id = s.id
LEFT JOIN transfers tr ON tr.schedule_id = s.id
LEFT JOIN samplings sm ON sm.schedule_id = s.id
LEFT JOIN feedings fd ON fd.schedule_id = s.id
LEFT JOIN deliveries dl ON dl.schedule_id = s.id
LEFT JOIN net_cleaning nc ON nc.schedule_id = s.id
LEFT JOIN net_checking nck ON nck.schedule_id = s.id
LEFT JOIN net_repairing nr ON nr.schedule_id = s.id
$whereClause
GROUP BY s.id
ORDER BY s.schedule_datetime DESC
";

$result = $conn->query($sql);
$tasks = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$pageTitle = "All Schedules";
if ($filter === 'last30days') $pageTitle = "Schedules (Last 30 Days)";
if ($filter === 'due_this_week') $pageTitle = "Schedules (Due This Week)";
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/img/dpa-logo.png">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    
</head>
<body class="dashboard-page">

<?php include "../inc/header.php" ?>
<div class="body">
    <?php include "../inc/nav.php" ?>

    <section class="section-1">
        <div class="content-header">
            <h4>
  <a href="dashboard.php" style="color: inherit; text-decoration: none;">
    <i class="fa-solid fa-arrow-left"></i>
  </a>
  <?= $pageTitle ?>
</h4>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h3 class="title">Task List</h3>
            </div>

            <div class="table-tools">
                    <div class="left-buttons">
                        <button id="btnPrintDetail" class="btn btn-primary">Print</button>
                        <button id="btnExcel" class="btn btn-primary">Excel</button>
                    </div>
                    <div class="right-search">
                        <label for="tableSearch">Search: 
                            <input type="text" id="tableSearch" >
                        </label>
                    </div>
                </div>

            <table class="main-table" id="dataTable">
                <thead>
                    <tr>
                        <th>Scheduled Date</th>
                        <th>Type</th>
                        <th>Cage</th>
                        <th>Assigned To</th>  
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tasks)): ?>
                        <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td>
                                <?= date('M d, Y', strtotime($task['schedule_datetime'])) ?>
                                at <?= date('h:i A', strtotime($task['schedule_datetime'])) ?>
                            </td>
                            <td><?= htmlspecialchars($task['type']) ?></td>
                            <td><?= htmlspecialchars($task['cage_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($task['employee_name'] ?? 'Unassigned') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
        <td colspan="4" style="text-align:center; font-weight:600;">No schedules found</td>
    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php include "../inc/footer.php" ?>
    </section>
</div>

 <!-- jQuery first -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables core -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<!-- Buttons extensions -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<!-- JSZip (for Excel export) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script>
var table; //variable to hold datatable object

        $(document).ready(function () {
        table = $('#dataTable').DataTable({
        paging: true,
        pageLength: 10,             // default rows per page
        lengthChange: false,        // allow user to change dropdown options
        pagingType: "simple",
        dom: 'Brtip', // show buttons
        buttons: [
            {
                extend: 'excelHtml5',
                className: 'button-excel',
                exportOptions: {
                    columns: ':not(:last-child)' // exclude last column (Actions)
                },
                init: function(api, node) { $(node).hide(); }
            },
            {
                extend: 'print',
                className: 'button-print',
                exportOptions: {
                    columns: ':not(:last-child)' // exclude last column (Actions)
                },
                init: function(api, node) { $(node).hide(); }
            }
        ]
    });
});

        $('#btnPrintDetail').on('click', function(){
            //trigger datatable print button click
            table.button('.button-print').trigger();
        });

        $('#btnExcel').on('click', function(){
            //trigger datatable excel button click
            table.button('.button-excel').trigger();
        });

        $('#tableSearch').on('keyup', function() {
            table.search(this.value).draw();
        });
</script>

</body>
</html>