<?php
session_start();
if (!isset($_SESSION['email']) || !isset($_SESSION['role']) || !isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

include "../auth/config.php";

$isAdmin = $_SESSION['role'] === 'admin';
$userId = (int)$_SESSION['id'];

// Get the status from the URL
$status = $_GET['status'] ?? '';
if (!$status) {
    echo "No status selected.";
    exit;
}

// Build SQL with proper result placeholders
$sql = "
SELECT * FROM (

    SELECT 'Fish Cage Management' AS type, fcm.id, fcm.result, fcm.status,
           fc.cage_name, s.schedule_datetime
    FROM fish_cage_management fcm
    JOIN schedules s ON s.id = fcm.schedule_id
    JOIN fish_cages fc ON fc.id = s.fish_cage
    WHERE fcm.status = '$status'
";

if (!$isAdmin) $sql .= " AND s.assigned_to = $userId";

$sql .= "

    UNION ALL

    SELECT 'Sampling', sm.id, '' AS result, sm.status,
           fc.cage_name, s.schedule_datetime
    FROM samplings sm
    JOIN schedules s ON s.id = sm.schedule_id
    JOIN fish_cages fc ON fc.id = s.fish_cage
    WHERE sm.status = '$status'
";

if (!$isAdmin) $sql .= " AND s.assigned_to = $userId";

$sql .= "

    UNION ALL

    SELECT 'Feeding', f.id, '' AS result, f.status,
           fc.cage_name, s.schedule_datetime
    FROM feedings f
    JOIN schedules s ON s.id = f.schedule_id
    JOIN fish_cages fc ON fc.id = s.fish_cage
    WHERE f.status = '$status'
";

if (!$isAdmin) $sql .= " AND s.assigned_to = $userId";

$sql .= "

    UNION ALL

    SELECT 'Stocking', st.id, '' AS result, st.status,
           fc.cage_name, s.schedule_datetime
    FROM stocking st
    JOIN schedules s ON s.id = st.schedule_id
    JOIN fish_cages fc ON fc.id = s.fish_cage
    WHERE st.status = '$status'
";

if (!$isAdmin) $sql .= " AND s.assigned_to = $userId";

$sql .= "

    UNION ALL

    SELECT 'Transferring', t.id, '' AS result, t.status,
           fc.cage_name, s.schedule_datetime
    FROM transfers t
    JOIN schedules s ON s.id = t.schedule_id
    JOIN fish_cages fc ON fc.id = s.fish_cage
    WHERE t.status = '$status'
";

if (!$isAdmin) $sql .= " AND s.assigned_to = $userId";

$sql .= "

    UNION ALL

    SELECT 'Delivering', d.id, '' AS result, d.status,
           fc.cage_name, s.schedule_datetime
    FROM deliveries d
    JOIN schedules s ON s.id = d.schedule_id
    JOIN fish_cages fc ON fc.id = s.fish_cage
    WHERE d.status = '$status'
";

if (!$isAdmin) $sql .= " AND d.assigned_to = $userId";

$sql .= "

    UNION ALL

    SELECT 'Net Cleaning', n.id, '' AS result, n.status,
           fc.cage_name, s.schedule_datetime
    FROM net_cleaning n
    JOIN schedules s ON s.id = n.schedule_id
    JOIN fish_cages fc ON fc.id = s.fish_cage
    WHERE n.status = '$status'
";

if (!$isAdmin) $sql .= " AND s.assigned_to = $userId";

$sql .= "

    UNION ALL

    SELECT 'Net Checking', nc.id, '' AS result, nc.status,
           fc.cage_name, s.schedule_datetime
    FROM net_checking nc
    JOIN schedules s ON s.id = nc.schedule_id
    JOIN fish_cages fc ON fc.id = s.fish_cage
    WHERE nc.status = '$status'
";

if (!$isAdmin) $sql .= " AND s.assigned_to = $userId";

$sql .= "

    UNION ALL

    SELECT 'Net Repairing', nr.id, '' AS result, nr.status,
           fc.cage_name, s.schedule_datetime
    FROM net_repairing nr
    JOIN schedules s ON s.id = nr.schedule_id
    JOIN fish_cages fc ON fc.id = s.fish_cage
    WHERE nr.status = '$status'
";

if (!$isAdmin) $sql .= " AND s.assigned_to = $userId";

$sql .= "

) AS all_tasks
ORDER BY schedule_datetime DESC
";


// Add more UNIONs for other task types (net_checking, stocking, feedings, samplings, transfers, deliveries)...

$tasks = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/img/dpa-logo.png">
    <title>Tasks - <?= htmlspecialchars($status) ?></title>
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
                Tasks - <?= htmlspecialchars($status) ?>
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

                            <th>Status</th>
                            
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
                                    <td><?= htmlspecialchars($task['cage_name']) ?></td>
                                    
                                    <td><span class="status <?= strtolower($task['status']) ?>"><?= $task['status'] ?></span></td>
                                    
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
        ],
        columnDefs: [
            { width: "150px", targets: 0 }, // Scheduled Date
            { width: "130px", targets: 1 }, // Type
            { width: "130px", targets: 2 }, // Cage
            { width: "120px", targets: 3 }  // Status
        ],
        scrollX: true,        // enable horizontal scroll if needed
        autoWidth: false      // important: lets columnDefs widths take effect
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
