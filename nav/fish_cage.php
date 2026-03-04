<?php 
session_start();

$popupSuccess = $_SESSION['popup_success'] ?? '';
$popupError   = $_SESSION['popup_error'] ?? '';

unset($_SESSION['popup_success'], $_SESSION['popup_error']);

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === "admin";

if (
    !isset($_SESSION['email']) ||
    !isset($_SESSION['role']) ||
    !isset($_SESSION['id']) ||
    $_SESSION['role'] !== "admin"
) {
    header("Location: ../index.php");
    exit();
}

include "../auth/config.php";
include "../model/fish_cage.php";
$cages = get_all_fish_cages($conn);


?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/img/dpa-logo.png">
	<title>Fish Cages</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
	<link rel="stylesheet" href="../assets/style.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    

</head>
<body class="dashboard-page">

	<?php include "../inc/header.php" ?>

	<div class="body">
		
		<?php include "../inc/nav.php" ?>
        <div class="sidebar-overlay" onclick="closeSidebar()"></div>
		<section class="section-1">
            <div class="content-header">
                <h4>Fish Cages</h4>
                <button class="menu-toggle" onclick="openSidebar()">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
            
            <div class="content-card">
                <div class="card-header">
                    <h3 class="title">Data Management</h3>
                    <button class="btn-add" onclick="openAddUserModal()">
                        <i class="fa-solid fa-plus"></i> New Cage
                    </button>
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

                <!-- table here -->
                <?php if (!empty($cages)) { ?>
                <table class="main-table" id="dataTable">
                    <thead>
                        <tr>
                            <th scope="col">Cage Name</th>
                            <th scope="col">Date Added</th>
                            <th scope="col" class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cages as $cage) { ?>
                        <tr>
                            <td><?= htmlspecialchars($cage['cage_name']) ?></td>
                            <td><?= date("M d, Y", strtotime($cage['date_added'])) ?> at <?= date("h:i A", strtotime($cage['date_added'])) ?></td>
                            
                            <td class="col-actions">
                                <button class="edit-btn"
                                    onclick="openEditCageModal(
                                        '<?= $cage['id'] ?>',
                                        '<?= htmlspecialchars($cage['cage_name']) ?>'
                                    )">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                <a href="../model/delete_fish_cage.php?id=<?= $cage['id'] ?>"
                                class="delete-btn"
                                onclick="return confirm('Delete this cage?')">
                                <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php } else { ?>
                <table class="main-table" id="dataTable">
                    <thead>
                        <tr>
                            <th scope="col">Cage Name</th>
                            <th scope="col">Date Added</th>
                            <th scope="col" class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
							<td colspan="3" style="text-align:center;">
                				No Fish Cage Record
            				</td>
						</tr>	
                    </tbody>
                </table>   
                <?php } ?>
            </div>
            <?php include "../inc/footer.php" ?>
		</section>
	</div>
    
    <!-- Add Fish Cage Modal -->
    <div id="addUserModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3>Add Cage</h3>
                <button class="close-btn" onclick="closeAddUserModal()">&times;</button>
            </div>

            <form action="../model/create_fish_cage.php" method="POST">
                <input type="text" name="cage_name" placeholder="Cage Name" required>

                <div class="modal-actions">
                    <button type="submit">Save</button>
                    <button type="button" class="btn-cancel" onclick="closeAddUserModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>


    <!-- Edit Fish Cage Modal -->
    <div id="editUserModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3>Edit Cage</h3>
                <button class="close-btn" onclick="closeEditUserModal()">&times;</button>
            </div>

            <form action="../model/update_fish_cage.php" method="POST">
                <input type="hidden" name="id" id="edit_cage_id">
                <input type="text" name="cage_name" id="edit_cage_name" required>

                <div class="modal-actions">
                    <button type="submit">Update</button>
                    <button type="button" class="btn-cancel" onclick="closeEditUserModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>



    <?php if ($popupSuccess || $popupError): ?>
    <div id="systemPopup" class="popup-overlay">
        <div class="popup-box <?= $popupSuccess ? 'success' : 'error' ?>">
            <div class="popup-icon">
                <?= $popupSuccess ? '✓' : '⚠' ?>
            </div>

            <h3><?= $popupSuccess ? 'Success' : 'Warning' ?></h3>
            <p><?= $popupSuccess ?: $popupError ?></p>

            <button onclick="closePopup()">OK</button>
        </div>
    </div>
    <?php endif; ?>

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
        function openAddUserModal() {
            document.getElementById("addUserModal").style.display = "flex";
        }

        function closeAddUserModal() {
            document.getElementById("addUserModal").style.display = "none";
        }

        function closePopup() {
            document.getElementById("systemPopup").remove();
        }

        function openEditCageModal(id, name) {
            document.getElementById("edit_cage_id").value = id;
            document.getElementById("edit_cage_name").value = name;
            document.getElementById("editUserModal").style.display = "flex";
        }


        function closeEditUserModal() {
            document.getElementById("editUserModal").style.display = "none";
        }

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                if (!confirm("Are you sure you want to delete this cage?")) {
                    e.preventDefault();
                }
            });
        });

// Open Add Modal if URL contains ?add=true
window.addEventListener('load', () => {
    const params = new URLSearchParams(window.location.search);
    const isAdmin = <?php echo ($isAdmin ? 'true' : 'false'); ?>; // PHP outputs JS boolean
    if (params.get('add') === 'true' && isAdmin) {
        setTimeout(openAddUserModal, 150);
    }
});

        var table; //variable to hold datatable object

        $(document).ready(function () {
        table = $('#dataTable').DataTable({
        scrollX: true,
        paging: true,
        pageLength: 5,             // default rows per page
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
            { width: "190px", targets: 0 }, // Cage Name
            { width: "160px", targets: 1 }, // Date Added
            { width: "90px", targets: 2 }  // Actions
        ],
        scrollX: true,        // enable horizontal scroll if needed
        autoWidth: false,      // important: lets columnDefs widths take effect
        order: [[1, 'desc']] // sort by Created column descending
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

                function openSidebar() {
    document.querySelector('.side-bar').classList.add('active');
    document.querySelector('.sidebar-overlay').classList.add('active');
}

function closeSidebar() {
    document.querySelector('.side-bar').classList.remove('active');
    document.querySelector('.sidebar-overlay').classList.remove('active');
}

document.querySelectorAll('.side-bar a').forEach(link => {
    link.addEventListener('click', (e) => {

        // If it's a dropdown trigger, DO NOT close
        if (link.classList.contains('overlay-trigger')) {
            return;
        }

        if (window.innerWidth <= 900) {
            closeSidebar();
        }
    });
});
    </script>

</body>
</html>