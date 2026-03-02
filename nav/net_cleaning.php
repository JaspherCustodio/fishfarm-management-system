<?php
session_start();

$isAdmin = ($_SESSION['role'] === 'admin');


include "../auth/config.php";      // ✅ FIRST
include "../model/net_cleaning.php";

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


/* ✅ NOW $conn EXISTS */
$schedules = $conn->query("
    SELECT 
        s.id,
        s.schedule_datetime,
        fc.cage_name
    FROM schedules s
    JOIN fish_cages fc ON fc.id = s.fish_cage
    WHERE s.task = 'Net Cleaning'
    $whereAssigned
    AND s.schedule_datetime >= NOW() - INTERVAL 30 DAY
")->fetch_all(MYSQLI_ASSOC);



$cleanings = get_all_net_cleaning($conn);

// Get approved employees for dropdown
$employees = [];
if ($isAdmin) {
    $empQuery = $conn->query("SELECT id, name FROM users WHERE role = 'user' AND status = 'approved'");
    if ($empQuery) $employees = $empQuery->fetch_all(MYSQLI_ASSOC);
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/img/dpa-logo.png">
	<title>Net Cleaning</title>
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
                <h4>Net Cleaning</h4>
            </div>
            
            <div class="content-card">
                <div class="card-header">
                    <h3 class="title">Data Management</h3>
                    <?php if ($isAdmin): ?>
                    <button class="btn-add" onclick="openAddNetCleaningModal()">
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
                <?php if (!empty($cleanings)) { ?>

                <table class="main-table" id="dataTable">
                    <thead>
                        <tr>
                        <th scope="col">Schedule</th>
                        <th scope="col">Cage Name</th>
                        <th scope="col">Cleaning Start Date</th>
                        <th scope="col">Time Started</th>
                        <th scope="col">Cleaning End Date</th>
                        <th scope="col">Time Ended</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cleanings as $row) { ?>
                    <tr>
                        <td>
                            <?= date('M d, Y', strtotime($row['schedule_datetime'])) ?><br>
                            at <?= date('h:i A', strtotime($row['schedule_datetime'])) ?>
                        </td>
                        
                        <td><?= htmlspecialchars($row['cage_name']) ?></td>
                        <td>
                            <?= !empty($row['start_date']) && $row['start_date'] != '0000-00-00'
                                ? date('M d, Y', strtotime($row['start_date']))
                                : '-' ?>
                        </td>
                        <td><?= $row['start_time'] ? date('h:i A', strtotime($row['start_time'])) : '-' ?></td>
                        <td>
                            <?= !empty($row['end_date']) && $row['end_date'] != '0000-00-00'
                                ? date('M d, Y', strtotime($row['end_date']))
                                : '-' ?>
                        </td>
                        <td><?= $row['end_time'] ? date('h:i A', strtotime($row['end_time'])) : '-' ?></td>
                        <td>
                            <span class="status <?= strtolower($row['status']) ?>">
                                <?= $row['status'] ?>
                            </span>
                        </td>

                        <td class="col-actions">
                            <button class="edit-btn"
                                data-id="<?= $row['id'] ?>"
                                data-assigned_to="<?= $row['assigned_to'] ?>"
    data-assigned_name="<?= htmlspecialchars($row['assigned_name']) ?>"
                                data-start_date="<?= $row['start_date'] ?>"
                                data-start_time="<?= $row['start_time'] ?>"
                                data-end_date="<?= $row['end_date'] ?>"
                                data-end_time="<?= $row['end_time'] ?>"
                                data-status="<?= $row['status'] ?>"
                            >
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <?php if ($isAdmin): ?>
                            <a href="../model/delete_net_cleaning.php?id=<?= $row['id'] ?>"class="delete-btn"><i class="fa-solid fa-trash"></i></a>
                            <?php endif; ?>
                        </td>
                        
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php } else { ?>
                <table class="main-table" id="dataTable">
                    <thead>
                        <tr>
                        <th scope="col">Schedule</th>
                        <th scope="col">Cage Name</th>
                        <th scope="col">Cleaning Start Date</th>
                        <th scope="col">Time Started</th>
                        <th scope="col">Cleaning End Date</th>
                        <th scope="col">Time Ended</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="9" style="text-align:center;">
                				No Net Cleaning Record
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
    <div id="addNetCleaningModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3>Add Record</h3>
                <button class="close-btn" onclick="closeAddNetCleaningModal()">&times;</button>
            </div>

            <form action="../model/create_net_cleaning.php" method="POST">

                <label>Schedule</label>
                <select name="schedule_id" required>
                    <option value="">-- Select Schedule --</option>
                    <?php foreach ($schedules as $s): ?>
                        <option value="<?= $s['id'] ?>">
                            <?= date('Y-m-d H:i', strtotime($s['schedule_datetime'])) ?>
                            — <?= htmlspecialchars($s['cage_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Assign To</label>
            <select name="assigned_to" required>
                <option value="">-- Select Employee --</option>
                <?php foreach ($employees as $emp): ?>
                    <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name']) ?></option>
                <?php endforeach; ?>
            </select>
            

                <div class="modal-actions">
                    <button type="submit">Save</button>
                    <button type="button" class="btn-cancel" onclick="closeAddNetCleaningModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editNetCleaningModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3>Edit Record</h3>
                <button class="close-btn" onclick="closeEditNetCleaningModal()">&times;</button>
            </div>

            <form action="../model/update_net_cleaning.php" method="POST">
                <input type="hidden" name="id" id="edit_id">
                
                <?php if ($isAdmin): ?>
                    <label>Assigned To</label>
            <select name="assigned_to" id="edit_assigned_to">
                <option value="">-- Assigned Employee --</option>
                <?php foreach ($employees as $emp): ?>
                    <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>

                    <label>Cleaning Start Date</label>
                    <input type="date" name="start_date" id="edit_start_date">

                    <label>Time Started</label>
                    <input type="time" name="start_time" id="edit_start_time">

                    <label>Cleaning End Date</label>
                    <input type="date" name="end_date" id="edit_end_date">

                    <label>Time Ended</label>
                    <input type="time" name="end_time" id="edit_end_time">

                <label>Status</label>
                <select name="status" id="edit_status">
                    <option value="Pending">Pending</option>
                    <option value="Ongoing">Ongoing</option>
                    <option value="Completed">Completed</option>
                </select>

                <div class="modal-actions">
                    <button type="submit">Update</button>
                    <button type="button" class="btn-cancel" onclick="closeEditNetCleaningModal()">Cancel</button>
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
        function openAddNetCleaningModal() {
            document.getElementById("addNetCleaningModal").style.display = "flex";
        }

        function closeAddNetCleaningModal() {
            document.getElementById("addNetCleaningModal").style.display = "none";
        }

        function closePopup() {
            document.getElementById("systemPopup")?.remove();
        }

        function openEditNetCleaningModal(data) {
    document.getElementById('edit_id').value = data.id;

    // ✅ Assign employee (admin only)
    const assignedSelect = document.getElementById('edit_assigned_to');
    if (assignedSelect) {
        assignedSelect.value = data.assigned_to || '';
    }

    document.getElementById('edit_start_date').value = data.start_date || '';
    document.getElementById('edit_start_time').value = data.start_time || '';
    document.getElementById('edit_end_date').value = data.end_date || '';
    document.getElementById('edit_end_time').value = data.end_time || '';

    document.getElementById('edit_status').value = data.status;

    document.getElementById('editNetCleaningModal').style.display = 'flex';
}

        function closeEditNetCleaningModal() {
            document.getElementById('editNetCleaningModal').style.display = 'none';
        }

        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                openEditNetCleaningModal(btn.dataset);
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

                setTimeout(openAddNetCleaningModal, 150);
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