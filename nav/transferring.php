<?php
session_start();

$isAdmin = ($_SESSION['role'] === 'admin');
$isUser = ($_SESSION['role'] === 'user');

include "../auth/config.php";      // ✅ FIRST
include "../model/transfers.php";

$transfers = get_all_transfers($conn);


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

$employees = $conn->query("
    SELECT id, name 
    FROM users 
    WHERE role = 'user'
	AND status = 'approved'
")->fetch_all(MYSQLI_ASSOC);


/* ✅ NOW $conn EXISTS */
$cages = $conn->query("SELECT id, cage_name FROM fish_cages")->fetch_all(MYSQLI_ASSOC);

$stockings = $conn->query("
    SELECT 
        s.id,
        s.fish_type,
        s.number_of_fingerlings,
        s.current_quantity,
        fc.id AS cage_id,
        fc.cage_name,
        COALESCE(NULLIF(s.date_stocked, '0000-00-00'), sc.schedule_datetime, s.created_at) AS display_date
    FROM stocking s
    JOIN schedules sc ON sc.id = s.schedule_id
    JOIN fish_cages fc ON fc.id = sc.fish_cage
    WHERE s.current_quantity > 0
    AND s.status = 'Completed'
")->fetch_all(MYSQLI_ASSOC);

?>


<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" type="image/png" href="../assets/img/dpa-logo.png">
	<title>Transferring</title>
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
                <h4>Transferring</h4>
            </div>
            
            <div class="content-card">
                <div class="card-header">
                    <h3 class="title">Data Management</h3>
                    <?php if ($isAdmin): ?>
                    <button class="btn-add" onclick="openAddTransfferingModal()">
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
                <?php if (!empty($transfers)) { ?>
				<table class="main-table" id="dataTable">
					<thead>
						<tr>
							<th scope="col">Schedule</th>
							<th scope="col">From Cage</th>
							<th scopre="col">Fish Type</th>
							<th scope="col">To Cage</th>
							<th scope="col">Date Transferred</th>
							<th scope="col">Qty Before</th>
							<th scope="col">Qty After</th>
							<th scope="col">Remarks</th>
							<th scope="col">Status</th>
							<th class="col-actions">Actions</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($transfers as $row): ?>
					<tr>
						<td>
							<?= date('M d, Y', strtotime($row['schedule_datetime'])) ?><br>
							at <?= date('h:i A', strtotime($row['schedule_datetime'])) ?>
						</td>

						<td title="<?= htmlspecialchars($row['from_cage']) ?>"><?= htmlspecialchars($row['from_cage']) ?></td>
						<td><?= htmlspecialchars($row['fish_type']) ?></td>
						<td title="<?= htmlspecialchars($row['to_cage']) ?>"><?= htmlspecialchars($row['to_cage']) ?></td>
						<td>
    <?= !empty($row['date_transferred']) && $row['date_transferred'] != '0000-00-00'
        ? date('M d, Y', strtotime($row['date_transferred']))
        : '-' ?>
</td>
						<td><?= isset($row['quantity_before']) && $row['quantity_before'] != 0 ? (int)$row['quantity_before'] : '-' ?></td>
<td><?= isset($row['quantity_after']) && $row['quantity_after'] != 0 ? (int)$row['quantity_after'] : '-' ?></td>
						
						<td title="<?= $row['remarks'] ?: '-' ?>"><?= $row['remarks'] ?: '-' ?></td>
						<td><?= htmlspecialchars($row['status']) ?></td>


						<td class="col-actions">
							<button class="edit-btn"
    data-id="<?= $row['id'] ?>"
    data-assigned_to="<?= $row['assigned_to'] ?>"
    data-assigned_name="<?= htmlspecialchars($row['assigned_name']) ?>"
    data-from_cage="<?= $row['from_cage_id'] ?>"
    data-from_cage_name="<?= htmlspecialchars($row['from_cage']) ?>"
    data-to_cage="<?= $row['to_cage_id'] ?>"
    data-to_cage_name="<?= htmlspecialchars($row['to_cage']) ?>"
    data-date="<?= !empty($row['date_transferred']) && $row['date_transferred'] != '0000-00-00' ? date('Y-m-d', strtotime($row['date_transferred'])) : '' ?>"
    data-before="<?= $row['quantity_before'] ?>"
    data-after="<?= $row['quantity_after'] ?>"
    data-remarks="<?= htmlspecialchars($row['remarks']) ?>"
    data-status="<?= $row['status'] ?>"
>
    <i class="fa-solid fa-pen-to-square"></i>
</button>
						
							<?php if ($isAdmin): ?>
							<a href="../model/delete_transfer.php?id=<?= $row['id'] ?>" class="delete-btn">
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
							<th scope="col">From Cage</th>
							<th scopre="col">Fish Type</th>
							<th scope="col">To Cage</th>
							<th scope="col">Date Transferred</th>
							<th scope="col">Qty Before</th>
							<th scope="col">Qty After</th>
							
							<th scope="col">Remarks</th>
							<th scope="col">Status</th>
							<th class="col-actions">Actions</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="10" style="text-align:center;">
                				No Transferring Record
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
    <div id="addTransfferingModal" class="modal-overlay">
		<div class="modal">
			<div class="modal-header">
				<h3>Add Record</h3>
				<button class="close-btn" onclick="closeAddTransfferingModal()">&times;</button>
			</div>

			<form action="../model/create_transfer.php" method="POST" onsubmit="return validateTransfer()">
				
				
				<label>Assign Employee</label>
				<select name="assigned_to" id="assigned_to" required>
					<option value="">-- Select Employee --</option>
					<?php
					$employees = $conn->query("SELECT id, name FROM users WHERE role='user' AND status = 'approved'")->fetch_all(MYSQLI_ASSOC);
					foreach ($employees as $emp) {
						echo '<option value="'. $emp['id'] .'">'. htmlspecialchars($emp['name']) .'</option>';
					}
					?>
				</select>

				<label>Stocking Reference</label>
				<select name="stocking_id" id="stocking_id" required>
					<option value="">-- Select Stocking --</option>
					<?php foreach ($stockings as $s): ?>
						<option value="<?= $s['id'] ?>" data-cage-id="<?= $s['cage_id'] ?>"
						data-quantity="<?= $s['current_quantity'] ?>">
							<?= htmlspecialchars($s['fish_type']) ?> |
							<?= htmlspecialchars($s['cage_name']) ?> |
							<?= date('M d Y', strtotime($s['display_date'])) ?> |
							<?= htmlspecialchars($s['current_quantity']) ?>
						</option>
					<?php endforeach; ?>
				</select>

				<input type="hidden" name="from_cage" id="from_cage">

				<input type="hidden" name="quantity_before" id="qty_before">

				<label>To Cage</label>
				<select name="to_cage" id="to_cage" required>
					<option value="">-- Select Cage --</option>
					<?php foreach ($cages as $c): ?>
						<option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['cage_name']) ?></option>
					<?php endforeach; ?>
				</select>

				<label>Quantity After</label>
				<input type="number" name="quantity_after" id="qty_after" min="0" required placeholder="Enter quantity after">

				<div class="modal-actions">
					<button type="submit">Save</button>
					<button type="button" class="btn-cancel" onclick="closeAddTransfferingModal()">Cancel</button>
				</div>
			</form>
		</div>
	</div>

    <div id="editTransfferingModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Record</h3>
            <button class="close-btn" onclick="closeEditTransfferingModal()">&times;</button>
        </div>

        <form action="../model/update_transfer.php" method="POST" onsubmit="return validateEditTransfer()">
            <input type="hidden" name="id" id="edit_id">
			<?php if ($isAdmin): ?>
            <!-- Admin-only fields -->
            <label>Assign to Employee</label>
            <select name="assigned_to" id="edit_assigned_to" <?= $isAdmin ? '' : 'disabled' ?> >
                <?php foreach ($employees as $emp): ?>
                    <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name']) ?></option>
                <?php endforeach; ?>
            </select>
<?php endif; ?>
            <label>From Cage</label>
            <input type="text" id="edit_from_cage_display" readonly class="readonly-field">
            <input type="hidden" name="from_cage" id="edit_from_cage">

            <label>To Cage</label>
<!-- Read-only display input -->
<input type="text" id="edit_to_cage_display" readonly class="readonly-field">
<!-- Hidden input for form submission -->
<input type="hidden" name="to_cage" id="edit_to_cage">

            <label>Quantity Before</label>
            <input type="number" id="qty_before_display" readonly class="readonly-field">
            <input type="hidden" name="quantity_before" id="edit_qty_before">


            <!-- Fields editable by all users -->
            <label>Date Transferred</label>
            <input type="date" name="date_transferred" id="edit_date" required>

            <label>Quantity After</label>
            <input type="number" name="quantity_after" id="edit_after" min="0" required>

            <label>Remarks</label>
            <textarea name="remarks" id="edit_remarks" placeholder="Optional"></textarea>

            <label>Status</label>
            <select name="status" id="edit_status" required>
                <option value="Pending">Pending</option>
                <option value="Ongoing">Ongoing</option>
                <option value="Completed">Completed</option>
            </select>

            <div class="modal-actions">
                <button type="submit">Update</button>
                <button type="button" class="btn-cancel" onclick="closeEditTransfferingModal()">Cancel</button>
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
		const stockingSelect = document.getElementById('stocking_id');
		const fromCageHidden = document.getElementById('from_cage');
		const qtyBeforeInput = document.getElementById('qty_before');

		stockingSelect.addEventListener('change', function () {
			const selected = this.selectedOptions[0];

			if (selected) {
				fromCageHidden.value = selected.dataset.cageId || "";
				qtyBeforeInput.value = selected.dataset.quantity || "";
			}
		});



        /* ✅ ADD THESE */
        function openAddTransfferingModal() {
            document.getElementById("addTransfferingModal").style.display = "flex";
        }


        function closeAddTransfferingModal() {
            document.getElementById("addTransfferingModal").style.display = "none";
        }

        function closePopup() {
            document.getElementById("systemPopup")?.remove();
        }

		
const isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;

function openEditTransfferingModal(data) {
    document.getElementById('edit_id').value = data.id;

    // Admin-only field
    if (isAdmin) {
        document.getElementById('edit_assigned_to').value = data.assigned_to || '';
    }

    // FROM CAGE – all users see
    document.getElementById('edit_from_cage_display').value = data.from_cage_name || "Unknown";
    document.getElementById('edit_from_cage').value = data.from_cage || '';

    // QUANTITY BEFORE – all users see
    document.getElementById('qty_before_display').value = data.before || '';
    document.getElementById('edit_qty_before').value = data.before || '';

    // TO CAGE – read-only for everyone
    document.getElementById('edit_to_cage_display').value = data.to_cage_name || "Unknown";
    document.getElementById('edit_to_cage').value = data.to_cage || '';

    // Editable fields
    document.getElementById('edit_date').value = data.date || '';
    document.getElementById('edit_after').value = data.after || '';
    document.getElementById('edit_remarks').value = data.remarks || '';
    document.getElementById('edit_status').value = data.status || 'Pending';

    document.getElementById('editTransfferingModal').style.display = 'flex';
}

		const qtyBeforeDisplay = document.getElementById('qty_before_display');

		stockingSelect.addEventListener('change', function () {
			const selected = this.selectedOptions[0];

			if (selected) {
				fromCageHidden.value = selected.dataset.cageId || "";

				const qty = selected.dataset.quantity || "";

				qtyBeforeInput.value = qty;        // for database
				qtyBeforeDisplay.value = qty;      // for display
			}
		});


        function closeEditTransfferingModal() {
            document.getElementById('editTransfferingModal').style.display = 'none';
        }

        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                openEditTransfferingModal(btn.dataset);
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
                setTimeout(openAddTransfferingModal, 150);
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