<?php
session_start();

$isAdmin = ($_SESSION['role'] === 'admin');

include "../auth/config.php";
include "../model/fish_cage_management.php";

$popupSuccess = $_SESSION['popup_success'] ?? '';
$popupError   = $_SESSION['popup_error'] ?? '';
unset($_SESSION['popup_success'], $_SESSION['popup_error']);

if (!isset($_SESSION['email'], $_SESSION['role'], $_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

$whereAssigned = "";
if ($_SESSION['role'] === 'user') {
    $whereAssigned = "AND s.assigned_to = " . intval($_SESSION['id']);
}

// Get schedules (last 30 days)
$schedules = $conn->query("
    SELECT s.id, s.schedule_datetime, fc.cage_name
    FROM schedules s
    JOIN fish_cages fc ON fc.id = s.fish_cage
    WHERE s.task = 'Fish Cage Management'
    $whereAssigned
    AND s.schedule_datetime >= NOW() - INTERVAL 30 DAY
")->fetch_all(MYSQLI_ASSOC);

// Get all records (newest first)
$records = get_fish_cage_management($conn);

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
    <title>Fish Cage Management</title>
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
            <h4>Fish Cage Management</h4>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h3 class="title">Data Management</h3>
                <?php if ($isAdmin): ?>
                <button class="btn-add" onclick="openAddCageManagementModal()">
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

            <?php if (!empty($records)): ?>
            <table class="main-table" id="dataTable">
                <thead>
                    <tr>
                        <th>Schedule</th>
                        <th>Cage Name</th>
                        <th>Management Date</th>
                        <th>Result</th>
                        <th>Optimum Level</th>
                        <th>Remarks</th>
                        <th>Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $row): ?>
                    <tr>
                        <td><?= date('M d, Y', strtotime($row['schedule_datetime'])) ?><br>at <?= date('h:i A', strtotime($row['schedule_datetime'])) ?></td>
                        <td><?= htmlspecialchars($row['cage_name']) ?></td>
                        <td>
    <?= ($row['date'] && $row['date'] !== '0000-00-00') 
        ? date('M d, Y', strtotime($row['date'])) 
        : '-' ?>
</td>
                        <td title="<?= htmlspecialchars($row['result'] ?: '-') ?>"><?= htmlspecialchars($row['result'] ?: '-') ?></td>
                        <td title="<?= htmlspecialchars($row['optimum_level'] ?: '-') ?>"><?= htmlspecialchars($row['optimum_level'] ?: '-') ?></td>
                        <td title="<?= htmlspecialchars($row['remarks'] ?: '-') ?>"><?= htmlspecialchars($row['remarks'] ?: '-') ?></td>
                        <td><span class="status <?= strtolower($row['status']) ?>"><?= $row['status'] ?></span></td>
                        <td class="col-actions">
                            <button class="edit-btn"
                                data-id="<?= $row['id'] ?>"
                                data-assigned_to="<?= $row['assigned_to'] ?>"
                                data-assigned_name="<?= htmlspecialchars($row['assigned_name']) ?>"
                                data-date="<?= $row['date'] ?>"
                                data-result="<?= htmlspecialchars($row['result'], ENT_QUOTES) ?>"
                                data-optimum="<?= htmlspecialchars($row['optimum_level'], ENT_QUOTES) ?>"
                                data-remarks="<?= htmlspecialchars($row['remarks'], ENT_QUOTES) ?>"
                                data-status="<?= $row['status'] ?>">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <?php if ($isAdmin): ?>
                            <a href="../model/delete_cage_management.php?id=<?= $row['id'] ?>" class="delete-btn">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <table class="main-table" id="dataTable">
                <tbody>
                    <tr>
                        <td colspan="<?= $isAdmin ? 9 : 8 ?>" style="text-align:center;">No Fish Cage Management Record</td>
                    </tr>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <?php include "../inc/footer.php" ?>
    </section>
</div>

<!-- Add Modal -->
<div id="addCageManagementModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>Add Record</h3>
            <button class="close-btn" onclick="closeAddCageManagementModal()">&times;</button>
        </div>
        <form action="../model/create_fish_cage_management.php" method="POST">
            <label>Schedule</label>
            <select name="schedule_id" required>
                <option value="">-- Select Schedule --</option>
                <?php foreach ($schedules as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= date('M d Y H:i', strtotime($s['schedule_datetime'])) ?> — <?= $s['cage_name'] ?></option>
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
                <button type="button" class="btn-cancel" onclick="closeAddCageManagementModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editCageManagementModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Record</h3>
            <button class="close-btn" onclick="closeEditCageManagementModal()">&times;</button>
        </div>
        <form action="../model/update_fish_cage_management.php" method="POST">
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

            <label>Management Date</label>
            <input type="date" name="date" id="edit_date" required>

            <label>Result</label>
            <input type="text" name="result" id="edit_result" placeholder="Optional">

            <label>Optimum Level</label>
            <input type="text" name="optimum_level" id="edit_optimum" placeholder="Optional">

            <label>Remarks</label>
            <textarea name="remarks" id="edit_remarks" placeholder="Optional"></textarea>

            <label>Status</label>
            <select name="status" id="edit_status">
                <option value="Pending">Pending</option>
                <option value="Ongoing">Ongoing</option>
                <option value="Completed">Completed</option>
            </select>

            <div class="modal-actions">
                <button type="submit">Update</button>
                <button type="button" class="btn-cancel" onclick="closeEditCageManagementModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<?php if ($popupSuccess || $popupError): ?>
<div id="systemPopup" class="popup-overlay">
    <div class="popup-box <?= $popupSuccess?'success':'error' ?>">
        <div class="popup-icon"><?= $popupSuccess?'✓':'⚠' ?></div>
        <h3><?= $popupSuccess?'Success':'Warning' ?></h3>
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
function openAddCageManagementModal() { document.getElementById("addCageManagementModal").style.display="flex"; }
function closeAddCageManagementModal() { document.getElementById("addCageManagementModal").style.display="none"; }
function closeEditCageManagementModal() { document.getElementById("editCageManagementModal").style.display="none"; }
function closePopup() { document.getElementById("systemPopup")?.remove(); }

function openEditCageManagementModal(data){
    document.getElementById('edit_id').value = data.id;

    if(document.getElementById('edit_assigned_to')){
        const assignedSelect = document.getElementById('edit_assigned_to');
        Array.from(assignedSelect.options).forEach(opt => {
            opt.selected = (opt.value == data.assigned_to);
        });
    }

    document.getElementById('edit_date').value = data.date || '';
    document.getElementById('edit_result').value = data.result || '';
    document.getElementById('edit_optimum').value = data.optimum || '';
    document.getElementById('edit_remarks').value = data.remarks || '';
    document.getElementById('edit_status').value = data.status || 'Pending';

    document.getElementById('editCageManagementModal').style.display='flex';
}

document.querySelectorAll('.edit-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>{ openEditCageManagementModal(btn.dataset); });
});

document.querySelectorAll('.delete-btn').forEach(btn=>{
    btn.addEventListener('click', e=>{
        if(!confirm("Are you sure you want to delete this record?")) e.preventDefault();
    });
});

// Open Add Modal if URL contains ?add=true
window.addEventListener('load', ()=>{
    const params = new URLSearchParams(window.location.search);
    if(params.get('add')==='true' && <?= $isAdmin?'true':'false' ?>) setTimeout(openAddCageManagementModal,150);
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