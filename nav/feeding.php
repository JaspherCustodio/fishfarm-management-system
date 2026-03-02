<?php
session_start();

$isAdmin = ($_SESSION['role'] === 'admin');


include "../auth/config.php";      // ✅ FIRST
include "../model/feedings.php";

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
    WHERE s.task = 'Feeding'
    $whereAssigned
      AND s.schedule_datetime >= NOW() - INTERVAL 30 DAY
")->fetch_all(MYSQLI_ASSOC);


$feedings = get_all_feedings($conn);

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
	<title>Feeding</title>
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
                <h4>Feeding</h4>
            </div>
            
            <div class="content-card">
                <div class="card-header">
                    <h3 class="title">Data Management</h3>
                    <?php if ($isAdmin): ?>
                    <button class="btn-add" onclick="openAddFeedingModal()">
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
                <?php if (!empty($feedings)) { ?>

                <table class="main-table" id="dataTable">
                    <thead>
                        <tr>
                        <th scope="col">Schedule</th>
                        <th scope="col">Cage Name</th>
                        <th scope="col">Date of Feeding</th>
                        <th scope="col">Types of Feed</th>
                        <th scope="col">Amount of Feed</th>
                        <th scope="col">Fed Time</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="col-actions">Actions</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedings as $row) { ?>
                    <tr>
                        <td><?= date('M d, Y', strtotime($row['schedule_datetime'])) ?><br>at <?= date('h:i A', strtotime($row['schedule_datetime'])) ?></td>
                        <td><?= htmlspecialchars($row['cage_name']) ?></td>
                        <td>
                            <?= $row['feeding_date']
                                ? date('M d, Y', strtotime($row['feeding_date']))
                                : '-' ?>
                        </td>
                        <td>
    <?= !empty($row['feed_type'])
        ? htmlspecialchars($row['feed_type'])
        : '-' ?>
</td>
                        <td>
    <?= (!empty($row['amount']) && $row['amount'] > 0)
        ? $row['amount'] . ' ' . $row['unit']
        : '-' ?>
</td>
                        <td><?= is_null($row['fed_time']) ? '-' : date('h:i A', strtotime($row['fed_time'])) ?></td>
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
                                    data-feeding_date="<?= $row['feeding_date'] ?>"
                                    data-feed_type="<?= $row['feed_type'] ?>"
                                    data-amount="<?= $row['amount'] ?>"
                                    data-unit="<?= $row['unit'] ?>"
                                    data-fed_time="<?= $row['fed_time'] ?>"
                                    data-status="<?= $row['status'] ?>"
                                >

                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <?php if ($isAdmin): ?>
                            <a href="../model/delete_feeding.php?id=<?= $row['id'] ?>"class="delete-btn"><i class="fa-solid fa-trash"></i></a>
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
                        <th scope="col">Types of Feed</th>
                        <th scope="col">Amount of Feed</th>
                        <th scope="col">Fed Time</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="9" style="text-align:center;">
                				No Feeding Record
            				</td>
                        </tr>
                    </tbody>
                </table>
            <?php } ?>
            </div>
            
            <?php include "../inc/footer.php" ?>
		</section>
	</div>
    

    <!-- Add Modal -->
    <div id="addFeedingModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3>Add Record</h3>
                <button class="close-btn" onclick="closeAddFeedingModal()">&times;</button>
            </div>

            <form action="../model/create_feeding.php" method="POST">

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
                    <button type="button" class="btn-cancel" onclick="closeAddFeedingModal()">Cancel</button>
                </div>
            </form>

        </div>
    </div>

    <div id="editFeedingModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3>Edit Record</h3>
                <button class="close-btn" onclick="closeEditFeedingModal()">&times;</button>
            </div>

           <form action="../model/update_feeding.php" method="POST">
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

                    <label>Date of Feeding</label>
                    <input type="date" name="feeding_date" id="edit_feeding_date" required>
                    <label>Feed Type</label>
                    <select name="feed_type" id="edit_feed_type" required>
                        <option value="">-- Select Feed Type --</option>
                        <option value="Floating Feed">Floating Feed</option>
                        <option value="Sinking Feed">Sinking Feed</option>
                        <option value="Powder Feed">Powder Feed</option>
                    </select>

                    <label>Amount</label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="amount" id="edit_amount" required>
                        <select name="unit" id="edit_unit">
                            <option value="kg">kg</option>
                            <option value="g">g</option>
                        </select>
                    </div>
                    

                    <label>Fed Time</label>
                    <input type="time" name="fed_time" id="edit_fed_time" required>
                
                <label>Status</label>
                <select name="status" id="edit_status">
                    <option value="Pending">Pending</option>
                    <option value="Ongoing">Ongoing</option>
                    <option value="Completed">Completed</option>
                </select>

                <div class="modal-actions">
                    <button type="submit">Update</button>
                    <button type="button" class="btn-cancel" onclick="closeEditFeedingModal()">Cancel</button>
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
        function openAddFeedingModal() {
    document.getElementById("addFeedingModal").style.display = "flex";
}
function closeAddFeedingModal() {
    document.getElementById("addFeedingModal").style.display = "none";
}
function closeEditFeedingModal() {
    document.getElementById("editFeedingModal").style.display = "none";
}
function closePopup() {
    document.getElementById("systemPopup")?.remove();
}

function openEditFeedingModal(data) {
    document.getElementById('edit_id').value = data.id;

    if (document.getElementById('edit_assigned_to')) {
        const assignedSelect = document.getElementById('edit_assigned_to');
        Array.from(assignedSelect.options).forEach(opt => {
            opt.selected = (opt.value == data.assigned_to);
        });
    }
    const feedTypeSelect = document.getElementById('edit_feed_type');

if (!data.feed_type || data.feed_type.trim() === "") {
    feedTypeSelect.value = "";
} else {
    feedTypeSelect.value = data.feed_type;
}
    document.getElementById('edit_feed_type').value = data.feed_type;
    document.getElementById('edit_amount').value = data.amount;
    document.getElementById('edit_unit').value = data.unit;
    document.getElementById('edit_fed_time').value = data.fed_time?.slice(0, 5);
    document.getElementById('edit_status').value = data.status;

    document.getElementById('editFeedingModal').style.display = 'flex';
}

document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        openEditFeedingModal(btn.dataset);
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
        setTimeout(openAddFeedingModal, 150);
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