<?php
session_start();

$isAdmin = ($_SESSION['role'] === 'admin');

include "../auth/config.php";      // ✅ FIRST
include "../model/stocking.php";
$stockings = get_all_stocking($conn);


$popupSuccess = $_SESSION['popup_success'] ?? '';
$popupError   = $_SESSION['popup_error'] ?? '';

unset($_SESSION['popup_success'], $_SESSION['popup_error']);

if (
    !isset($_SESSION['email']) ||
    !isset($_SESSION['role']) ||
    !isset($_SESSION['id'])
) {
    header("Location: ../index.php");
    exit();
}

$whereAssigned = "";

if ($_SESSION['role'] === 'user') {
    $whereAssigned = "AND s.assigned_to = " . intval($_SESSION['id']);
}

// Get all approved employees for dropdown
$employees = [];
if ($isAdmin) {
    $empQuery = $conn->query("SELECT id, name FROM users WHERE role = 'user' AND status = 'approved'");
    if ($empQuery) $employees = $empQuery->fetch_all(MYSQLI_ASSOC);
}


/* ✅ NOW $conn EXISTS */
$schedules = $conn->query("
    SELECT 
        s.id,
        s.schedule_datetime,
        fc.cage_name
    FROM schedules s
    JOIN fish_cages fc ON fc.id = s.fish_cage
    WHERE s.task = 'Stocking'
    $whereAssigned
      AND s.schedule_datetime >= NOW() - INTERVAL 30 DAY
")->fetch_all(MYSQLI_ASSOC);

?>


<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/img/dpa-logo.png">
	<title>Stocking</title>
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
                <h4>Stocking</h4>
                <button class="menu-toggle" onclick="openSidebar()">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
            
            <div class="content-card">
                <div class="card-header">
                    <h3 class="title">Data Management</h3>
                    <?php if ($isAdmin): ?>
                    <button class="btn-add" onclick="openAddStockingModal()">
                        <i class="fa-solid fa-plus"></i> New Record
                    </button>
                    <?php endif; ?>
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
                <?php if (!empty($stockings)) { ?>

                <table class="main-table" id="dataTable">
                    <thead>
                        <tr>
                        <th scope="col">Schedule</th>
                        <th scope="col">Cage Name</th>
                        <th scope="col">Date Stocked</th>
                        <th scope="col">Source of Fingerlings</th>
                        <th scope="col">Fish Type</th>
                        <th scope="col">Standard</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stockings as $row): ?>
                        <tr>
                            <td>
                                <?= date('M d, Y', strtotime($row['schedule_datetime'])) ?><br>
                                at <?= date('h:i A', strtotime($row['schedule_datetime'])) ?>
                            </td>

                            <td><?= htmlspecialchars($row['cage_name']) ?></td>
                            <td>
    <?= !empty($row['date_stocked'])
        ? date('M d, Y', strtotime($row['date_stocked']))
        : '-' ?>
</td>
                            <td title="<?= !empty($row['source_of_fingerlings']) ? htmlspecialchars($row['source_of_fingerlings']) : '-' ?>">
    <?= !empty($row['source_of_fingerlings'])
        ? htmlspecialchars($row['source_of_fingerlings'])
        : '-' ?>
</td>
                            <td>
    <?= !empty($row['fish_type'])
        ? htmlspecialchars($row['fish_type'])
        : '-' ?>
</td>
                            <td>
    <?= (!empty($row['standard_fingerlings']) && $row['standard_fingerlings'] > 0)
        ? $row['standard_fingerlings'] . ' g'
        : '-' ?>
</td>
                            <td>
    <?= (!empty($row['number_of_fingerlings']) && $row['number_of_fingerlings'] > 0)
        ? $row['number_of_fingerlings']
        : '-' ?>
</td>
                            <td>
                                <span class="status <?= strtolower($row['status']) ?>">
                                    <?= $row['status'] ?>
                                </span>
                            </td>
                            <td class="col-actions">
                                <button class="edit-btn"
    data-id="<?= $row['id'] ?? '' ?>"
    data-assigned_to="<?= $row['assigned_to'] ?? '' ?>"
    data-date_stocked="<?= $row['date_stocked'] ?? '' ?>"
    data-source="<?= htmlspecialchars($row['source_of_fingerlings'] ?? '') ?>"
    data-fish_type="<?= $row['fish_type'] ?? '' ?>"
    data-standard="<?= $row['standard_fingerlings'] ?? '' ?>"
    data-quantity="<?= $row['number_of_fingerlings'] ?? '' ?>"
    data-status="<?= $row['status'] ?? 'Pending' ?>"
>
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <?php if ($isAdmin): ?>
                                <a href="../model/delete_stocking.php?id=<?= $row['id'] ?>" class="delete-btn">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php } else { ?>
                <table class="main-table" id="dataTable">
                    <thead>
                        <tr>
                        <th scope="col">Schedule</th>
                        <th scope="col">Cage Name</th>
                        <th scope="col">Date Stocked</th>
                        <th scope="col">Source of Fingerlings</th>
                        <th scope="col">Fish Type</th>
                        <th scope="col">Standard</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
							<td colspan="<?= $isAdmin ? 9 : 8 ?>" style="text-align:center;">
                				No Stocking Record
            				</td>
						</tr>	
                    </tbody>
                </table>
            <?php } ?>
            </div>
            
            <?php include "../inc/footer.php" ?>
		</section>
	</div>
    

    <!-- Add User Modal -->
    <div id="addStockingModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3>Add Record</h3>
                <button class="close-btn" onclick="closeAddStockingModal()">&times;</button>
            </div>

            <form action="../model/create_stocking.php" method="POST">

           <label>Schedule</label>
            <select name="schedule_id" required>
                <option value="">-- Select Schedule --</option>
                <?php foreach ($schedules as $s): ?>
                    <option value="<?= $s['id'] ?>">
                        <?= date('M d Y', strtotime($s['schedule_datetime'])) ?> at
                        <?= date('h:i A', strtotime($s['schedule_datetime'])) ?>
                        — <?= $s['cage_name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Assign To</label>
<select name="assigned_to" required>
    <option value="">-- Select Employee --</option>
    <?php foreach ($employees as $emp): ?>
        <option value="<?= $emp['id'] ?>">
            <?= htmlspecialchars($emp['name']) ?>
        </option>
    <?php endforeach; ?>
</select>

            <div class="modal-actions">
                <button type="submit">Save</button>
                <button type="button" class="btn-cancel" onclick="closeAddStockingModal()">Cancel</button>
            </div>
            </form>

        </div>
    </div>

    <div id="editStockingModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3>Edit Record</h3>
                <button class="close-btn" onclick="closeEditStockingModal()">&times;</button>
            </div>

            <form action="../model/update_stocking.php" method="POST">
                <input type="hidden" name="id" id="edit_id">
                
                <?php if ($isAdmin): ?>
                    <label>Assigned Employee</label>
                    <select name="assigned_to" id="edit_assigned_to" required>
                        <option value="">-- Select Employee --</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
<?php endif; ?>

                    <label>Date Stocked</label>
                    <input type="date" name="date_stocked" id="edit_date_stocked" required>

                    <label>Source of Fingerlings</label>
                    <input type="text" name="source_of_fingerlings" id="edit_source" required placeholder="Enter source of fingerlings">

                    <label>Fish Type</label>
                    <select name="fish_type" id="edit_fish_type" required>
                        <option value="">-- Select Fish Type --</option>
                        <option value="Tilapia">Tilapia</option>
                        <option value="Bighead">Bighead</option>
                        <option value="Bangus">Bangus</option>
                    </select>

                    <label>Standard Fingerlings</label>
                    <input type="number" name="standard_fingerlings" id="edit_standard" required>

                    <label>No. of Fingerlings</label>
                    <input type="number" name="number_of_fingerlings" id="edit_quantity" required>
                

                <label>Status</label>
                <select name="status" id="edit_status">
                    <option value="Pending">Pending</option>
                    <option value="Ongoing">Ongoing</option>
                    <option value="Completed">Completed</option>
                </select>

                <div class="modal-actions">
                    <button type="submit">Update</button>
                    <button type="button" class="btn-cancel" onclick="closeEditStockingModal()">Cancel</button>
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
        /* ✅ ADD THESE */
        function openAddStockingModal() {
            document.getElementById("addStockingModal").style.display = "flex";
        }

        function closeAddStockingModal() {
            document.getElementById("addStockingModal").style.display = "none";
        }

        function closePopup() {
            document.getElementById("systemPopup")?.remove();
        }

        function openEditStockingModal(data) {

    document.getElementById('edit_id').value = data.id;

    const assignedSelect = document.getElementById('edit_assigned_to');
    if (assignedSelect) {
        Array.from(assignedSelect.options).forEach(opt => {
            opt.selected = (opt.value == data.assigned_to);
        });
    }

    const dateInput = document.getElementById('edit_date_stocked');
    if (dateInput) dateInput.value = data.date_stocked || '';

    const sourceInput = document.getElementById('edit_source');
    if (sourceInput) sourceInput.value = data.source || '';

    const fishInput = document.getElementById('edit_fish_type');
    if (fishInput) fishInput.value = data.fish_type || '';

    const standardInput = document.getElementById('edit_standard');
    if (standardInput) standardInput.value = data.standard || '';

    const quantityInput = document.getElementById('edit_quantity');
    if (quantityInput) quantityInput.value = data.quantity || '';

    document.getElementById('edit_status').value = data.status || 'Pending';

    document.getElementById('editStockingModal').style.display = 'flex';
}

        function closeEditStockingModal() {
            document.getElementById('editStockingModal').style.display = 'none';
        }

        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                openEditStockingModal(btn.dataset);
            });
        });

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                if (!confirm("Are you sure you want to delete this record?")) {
                    e.preventDefault();
                }
            });
        });

                // Open Add Modal if URL contains ?add=true
        window.addEventListener('load', () => {
            const params = new URLSearchParams(window.location.search);
            if (params.get('add') === 'true' && <?= $isAdmin ? 'true' : 'false' ?>) {
                setTimeout(openAddStockingModal, 150);
            }
        });

var table; //variable to hold datatable object

        $(document).ready(function () {
        table = $('#dataTable').DataTable({
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
            { width: "120px", targets: 0 }, // Schedule
            { width: "130px", targets: 1 }, // Cage Name
            { width: "130px", targets: 2 }, // Date Stocked
            { width: "190px", targets: 3 }, // Source of Fingerlings
            { width: "140px", targets: 4 }, // Fish Type
            { width: "120px", targets: 5 }, // Standard
            { width: "110px", targets: 6 }, // Quantity
            { width: "110px", targets: 7 }, // Status
            { width: "90px", targets: 8 }  // Actions
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