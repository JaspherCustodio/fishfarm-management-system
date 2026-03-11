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
    <link rel="icon" type="image/png" href="../assets/img/dpa-logo.png">
    <title>Pending Approvals</title>
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
                <h4><a href="user_management.php" style="color: inherit; text-decoration: none;"><i class="fa-solid fa-arrow-left"></i></a> Approval Queue</h4>
            </div>
            
            <div class="content-card">
                <div class="card-header">
                    <h4 class="title">Waiting for Access</h4>
                </div>

                <div class="table-tools" style="margin-bottom: 10px">
                    <div class="table-tools">

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
            </div>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
    var table; //variable to hold datatable object

        $(document).ready(function () {
        table = $('#dataTable').DataTable({
        paging: true,
        pageLength: 5,             // default rows per page
        lengthChange: false,        // allow user to change dropdown options
        pagingType: "simple",
        dom: 'rtip', // show buttons
columnDefs: [
            { width: "250px", targets: 0 }, // Email
            { width: "200px", targets: 1 }, // Full name
            { width: "110px", targets: 2 }, // Role
            { width: "90px", targets: 3 }  // Actions
        ],
        scrollX: true,        // enable horizontal scroll if needed
        autoWidth: false,      // important: lets columnDefs widths take effect
        order: [[2, 'asc']], // sort by Created column descending
        language: {
            emptyTable: "No users found" // <-- put this here, no second initialization
        }
    });
});


        $('#tableSearch').on('keyup', function() {
            table.search(this.value).draw();
        });
</script>
</body>
</html>