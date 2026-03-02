<?php
session_start();

$isAdmin = ($_SESSION['role'] === 'admin');


include "../auth/config.php";      // ✅ FIRST
include "../model/samplings.php";

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
        s.fish_cage,
        fc.cage_name
    FROM schedules s
    JOIN fish_cages fc ON fc.id = s.fish_cage
    WHERE s.task = 'Sampling'
    $whereAssigned
     AND s.schedule_datetime >= NOW() - INTERVAL 30 DAY

")->fetch_all(MYSQLI_ASSOC);


$samplings = get_all_samplings($conn);

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
	<title>Sampling</title>
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
                <h4>Sampling</h4>
            </div>
            
            <div class="content-card">
                <div class="card-header">
                    <h3 class="title">Data Management</h3>
                    <?php if ($isAdmin): ?>
                    <button class="btn-add" onclick="openAddSamplingModal()">
                        <i class="fa-solid fa-plus"></i> New Record
                    </button>
                    <button class="btn-add" onclick="openDFRModal()">
                        <i class="fa-solid fa-calculator"></i> DFR Calculator
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
                <?php if (!empty($samplings)) { ?>

                <table class="main-table" id="dataTable">
                    <thead>
                        <tr>
                        <th scope="col">Schedule</th>
                        <th scope="col">Cage Name</th>
                        <th scope="col">Date of Sampling</th>
                        <th scope="col">Fish Type</th>
                        <th scope="col">Weight</th>
                        <th scope="col">Length</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($samplings as $row) { ?>
                    <tr>
                        <td><?= date('M d, Y', strtotime($row['schedule_datetime'])) ?><br>at <?= date('h:i A', strtotime($row['schedule_datetime'])) ?></td>
                        <td><?= htmlspecialchars($row['cage_name']) ?></td>
                        <td>
                            <?= $row['sampling_date']
                                ? date('M d, Y', strtotime($row['sampling_date']))
                                : '-' ?>
                        </td>

                        <td>
                            <?= !empty($row['fish_type'])
                                ? htmlspecialchars($row['fish_type'])
                                : '-' ?>
                        </td>

                        <td>
    <?= ($row['avg_weight'] > 0)
        ? $row['avg_weight'] . ' ' . $row['weight_unit']
        : '-' ?>
</td>
                        <td>
    <?= ($row['avg_length'] > 0)
        ? $row['avg_length'] . ' ' . $row['length_unit']
        : '-' ?>
</td>
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
                                data-sampling_date="<?= $row['sampling_date'] ?>"
                                data-fish_type="<?= $row['fish_type'] ?>"
                                data-avg_weight="<?= $row['avg_weight'] ?>"
                                data-weight_unit="<?= $row['weight_unit'] ?>"
                                data-avg_length="<?= $row['avg_length'] ?>"
                                data-length_unit="<?= $row['length_unit'] ?>"
                                data-status="<?= $row['status'] ?>"
                            >
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <?php if ($isAdmin): ?>
                            <a href="../model/delete_sampling.php?id=<?= $row['id'] ?>"class="delete-btn"><i class="fa-solid fa-trash"></i></a>
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
                        <th scope="col">Date of Sampling</th>
                        <th scope="col">Fish Type</th>
                        <th scope="col">Weight</th>
                        <th scope="col">Length</th>
                        <th scope="col">Status</th>
                        <?php if ($isAdmin): ?>
                        <th scope="col" class="col-actions">Actions</th>
                        <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="<?= $isAdmin ? 9 : 8 ?>" style="text-align:center;">
                				No Sampling Record
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
    <div id="addSamplingModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3>Add Record</h3>
                <button class="close-btn" onclick="closeAddSamplingModal()">&times;</button>
            </div>

            <form action="../model/create_sampling.php" method="POST">

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
                <button type="button" class="btn-cancel" onclick="closeAddSamplingModal()">Cancel</button>
            </div>
        </form>

        </div>
    </div>

    <div id="editSamplingModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3>Edit Record</h3>
                <button class="close-btn" onclick="closeEditSamplingModal()">&times;</button>
            </div>

            <form action="../model/update_sampling.php" method="POST">

                
                <input type="hidden" name="id" id="edit_id">
                <?php if ($isAdmin): ?>
<label>Assigned To</label>
<select name="assigned_to" id="edit_assigned_to">
    <option value="">-- Select Employee --</option>
    <?php foreach ($employees as $emp): ?>
        <option value="<?= $emp['id'] ?>">
            <?= htmlspecialchars($emp['name']) ?>
        </option>
    <?php endforeach; ?>
</select>
<?php endif; ?>
                    <!-- ADMIN CAN SEE EVERYTHING -->
                    <label>Date of Sampling</label>
                    <input type="date" name="sampling_date" id="edit_sampling_date" required>

                    <label>Fish Type</label>
                    <select name="fish_type" id="edit_fish_type" required>
                        <option value="">-- Select Fish Type --</option>
                        <option value="Tilapia">Tilapia</option>
                        <option value="Bighead">Bighead</option>
                        <option value="Bangus">Bangus</option>
                    </select>

                    <label>Average Weight</label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="avg_weight" id="edit_avg_weight" required>
                        <select name="weight_unit" id="edit_weight_unit">
                            <option value="g">g</option>
                            <option value="kg">kg</option>
                        </select>
                    </div>

                    <label>Average Length</label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="avg_length" id="edit_avg_length" required>
                        <select name="length_unit" id="edit_length_unit">
                            <option value="cm">cm</option>
                            <option value="mm">mm</option>
                        </select>
                    </div>

                <!-- BOTH ADMIN & EMPLOYEE CAN SEE STATUS -->
                <label>Status</label>
                <select name="status" id="edit_status">
                    <option value="Pending">Pending</option>
                    <option value="Ongoing">Ongoing</option>
                    <option value="Completed">Completed</option>
                </select>

                <div class="modal-actions">
                    <button type="submit">Update</button>
                    <button type="button" class="btn-cancel" onclick="closeEditSamplingModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>


    <!-- DFR Calculator Modal -->
    <div id="dfrModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3>DFR Calculator</h3>
                <button class="close-btn" onclick="closeDFRModal()">&times;</button>
            </div>

            <form onsubmit="return false;">

                

                <label>No. of Fish</label>
                <input type="number" id="dfr_qty" placeholder="100">

                <label>Average Weight (kg)</label>
                <input type="number" step="0.01" id="dfr_weight" placeholder="0.14">

                <label>Feeding Rate (%)</label>
                <input type="number" step="0.1" id="dfr_rate" value="3">

                <label>Feeding Result</label>
                <input type="text" id="dfr_result" readonly class="readonly-field">

                <button type="button" onclick="calculateDFR()">Calculate</button>

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
        function openAddSamplingModal() {
            document.getElementById("addSamplingModal").style.display = "flex";
        }

        function closeAddSamplingModal() {
            document.getElementById("addSamplingModal").style.display = "none";
        }

        function closePopup() {
            document.getElementById("systemPopup")?.remove();
        }

        function openEditSamplingModal(data) {
    document.getElementById('edit_id').value = data.id;

    // 🔹 SET ASSIGNED TO (ADMIN ONLY)
    const assignedSelect = document.getElementById('edit_assigned_to');
    if (assignedSelect) {
        Array.from(assignedSelect.options).forEach(opt => {
            opt.selected = (opt.value == data.assigned_to);
        });
    }

    // 🔹 SET OTHER FIELDS
    document.getElementById('edit_sampling_date').value = data.sampling_date || '';
    document.getElementById('edit_fish_type').value = data.fish_type || '';
    document.getElementById('edit_avg_weight').value = data.avg_weight || '';
    document.getElementById('edit_weight_unit').value = data.weight_unit || 'g';
    document.getElementById('edit_avg_length').value = data.avg_length || '';
    document.getElementById('edit_length_unit').value = data.length_unit || 'cm';
    document.getElementById('edit_status').value = data.status || 'Pending';

    document.getElementById('editSamplingModal').style.display = 'flex';
}

        function closeEditSamplingModal() {
            document.getElementById('editSamplingModal').style.display = 'none';
        }

        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                openEditSamplingModal(btn.dataset);
            });
        });


        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                if (!confirm("Are you sure you want to delete this record?")) {
                    e.preventDefault();
                }
            });
        });

        function openDFRModal() {
            document.getElementById("dfrModal").style.display = "flex";
        }

        function closeDFRModal() {
            document.getElementById("dfrModal").style.display = "none";
        }

        function calculateDFR() {

            let qty = parseFloat(document.getElementById("dfr_qty").value) || 0;
            let weight = parseFloat(document.getElementById("dfr_weight").value) || 0;
            let rate = parseFloat(document.getElementById("dfr_rate").value) || 0;

            let biomass = qty * weight;
            let feeds = biomass * (rate / 100);

            document.getElementById("dfr_result").value =
                feeds.toFixed(2) + " kg feeds everyday";
        }

                // Open Add Modal if URL contains ?add=true
        window.addEventListener('load', () => {
            const params = new URLSearchParams(window.location.search);
            if (params.get('add') === 'true' && <?= $isAdmin ? 'true' : 'false' ?>) {
                setTimeout(openAddSamplingModal, 150);
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