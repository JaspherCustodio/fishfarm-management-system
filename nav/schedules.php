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

// Build WHERE clauses for filtering and non-admin
$whereSchedule = "WHERE 1=1";
$whereOther = "WHERE 1=1";

if ($filter === 'last30days') {
    $whereSchedule .= " AND s.schedule_datetime >= CURDATE() - INTERVAL 30 DAY";
    $whereOther .= " AND t.schedule_datetime >= CURDATE() - INTERVAL 30 DAY";
}

// Past due tasks filter
if ($filter === 'due_this_week') {
    $whereSchedule .= " AND s.schedule_datetime < CURDATE()";
    $whereOther .= " AND t.schedule_datetime < CURDATE()";
    $pageTitle = "Past Due Tasks";
}

// Non-admins only see their own tasks
if (!$isAdmin) {
    $whereSchedule .= " AND s.assigned_to = $userId";
    $whereOther .= " AND t.assigned_to = $userId";
}

// Default page title
if (!isset($pageTitle)) $pageTitle = "All Tasks";
if ($filter === 'last30days') $pageTitle = "Schedules (Last 30 Days)";

// UNION ALL query to fetch all tasks with status != 'completed'
$sql = "
SELECT 'Fish Cage Management' AS type, fcm.id, fcm.status, s.schedule_datetime, fc.cage_name, u.name AS employee_name
FROM fish_cage_management fcm
LEFT JOIN schedules s ON s.id = fcm.schedule_id
LEFT JOIN fish_cages fc ON fc.id = s.fish_cage
LEFT JOIN users u ON u.id = s.assigned_to
$whereSchedule AND fcm.status != 'completed'

UNION ALL

SELECT 'Stocking' AS type, st.id, st.status, s.schedule_datetime, fc.cage_name, u.name AS employee_name
FROM stocking st
LEFT JOIN schedules s ON s.id = st.schedule_id
LEFT JOIN fish_cages fc ON fc.id = s.fish_cage
LEFT JOIN users u ON u.id = s.assigned_to
$whereSchedule AND st.status != 'completed'

UNION ALL

SELECT 'Transferring' AS type, tr.id, tr.status, s.schedule_datetime, fc.cage_name, u.name AS employee_name
FROM transfers tr
LEFT JOIN schedules s ON s.id = tr.schedule_id
LEFT JOIN fish_cages fc ON fc.id = s.fish_cage
LEFT JOIN users u ON u.id = s.assigned_to
$whereSchedule AND tr.status != 'completed'

UNION ALL

SELECT 'Delivering' AS type, dl.id, dl.status, dl.delivery_date AS schedule_datetime, fc.cage_name, u.name AS employee_name
FROM deliveries dl
LEFT JOIN fish_cages fc ON fc.id = dl.cage_id
LEFT JOIN users u ON u.id = dl.assigned_to
WHERE dl.delivery_date < CURDATE() AND dl.status != 'completed'
";

if (!$isAdmin) $sql .= " AND dl.assigned_to = $userId";

$sql .= "

UNION ALL

SELECT 'Feeding' AS type, fd.id, fd.status, s.schedule_datetime, fc.cage_name, u.name AS employee_name
FROM feedings fd
LEFT JOIN schedules s ON s.id = fd.schedule_id
LEFT JOIN fish_cages fc ON fc.id = s.fish_cage
LEFT JOIN users u ON u.id = s.assigned_to
$whereSchedule AND fd.status != 'completed'

UNION ALL

SELECT 'Sampling' AS type, sm.id, sm.status, s.schedule_datetime, fc.cage_name, u.name AS employee_name
FROM samplings sm
LEFT JOIN schedules s ON s.id = sm.schedule_id
LEFT JOIN fish_cages fc ON fc.id = s.fish_cage
LEFT JOIN users u ON u.id = s.assigned_to
$whereSchedule AND sm.status != 'completed'

UNION ALL

SELECT 'Net Cleaning' AS type, nc.id, nc.status, s.schedule_datetime, fc.cage_name, u.name AS employee_name
FROM net_cleaning nc
LEFT JOIN schedules s ON s.id = nc.schedule_id
LEFT JOIN fish_cages fc ON fc.id = s.fish_cage
LEFT JOIN users u ON u.id = s.assigned_to
$whereSchedule AND nc.status != 'completed'

UNION ALL

SELECT 'Net Checking' AS type, nck.id, nck.status, s.schedule_datetime, fc.cage_name, u.name AS employee_name
FROM net_checking nck
LEFT JOIN schedules s ON s.id = nck.schedule_id
LEFT JOIN fish_cages fc ON fc.id = s.fish_cage
LEFT JOIN users u ON u.id = s.assigned_to
$whereSchedule AND nck.status != 'completed'

UNION ALL

SELECT 'Net Repairing' AS type, nr.id, nr.status, s.schedule_datetime, fc.cage_name, u.name AS employee_name
FROM net_repairing nr
LEFT JOIN schedules s ON s.id = nr.schedule_id
LEFT JOIN fish_cages fc ON fc.id = s.fish_cage
LEFT JOIN users u ON u.id = s.assigned_to
$whereSchedule AND nr.status != 'completed'

ORDER BY schedule_datetime DESC
";

$result = $conn->query($sql);
$tasks = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
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
            document.addEventListener("DOMContentLoaded", function () {

        const currentPage = window.location.pathname.split("/").pop().split("?")[0];

        document.querySelectorAll(".side-bar a").forEach(link => {

            const linkPage = link.getAttribute("href")
                ?.split("/")
                .pop()
                .split("?")[0];

            // Exact match
            if (linkPage === currentPage) {
                link.classList.add("active-link");
            }

            // 🔵 Treat these pages as Dashboard
            if (
                (currentPage === "tasks.php" ||
                currentPage === "schedules.php" ||
                currentPage === "dashboard.php") &&
                linkPage === "dashboard.php"
            ) {
                link.classList.add("active-link");
            }
        });

    });
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
                
                init: function(api, node) { $(node).hide(); }
            },
            {
                extend: 'print',
                className: 'button-print',
                
                init: function(api, node) { $(node).hide(); }
            }
        ],
        columnDefs: [
            { width: "160px", targets: 0 }, // Scheduled Date
            { width: "160px", targets: 1 }, // Type
            { width: "120px", targets: 2 }, // Cage
            { width: "140px", targets: 3 }  // Assigned To
        ],
        scrollX: true,        // enable horizontal scroll if needed
        autoWidth: false,      // important: lets columnDefs widths take effect
        order: [[0, 'desc']] // sort by Created column descending
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