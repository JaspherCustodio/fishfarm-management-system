<?php
session_start();

$isAdmin = ($_SESSION['role'] === 'admin');


include "../auth/config.php";      // ✅ FIRST
include "../model/expenses.php";

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


$records = get_expenses($conn);


?>


<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/img/dpa-logo.png">
	<title>Manage Expense</title>
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
                <h4>Manage Expense</h4>
                <button class="menu-toggle" onclick="openSidebar()">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
            
            <div class="content-card">
                <div class="card-header">
                    <h3 class="title">Data Management</h3>
                    <?php if ($isAdmin): ?>
                    <button class="btn-add" onclick="openAddExpenseModal()">
                        <i class="fa-solid fa-plus"></i> New Expense
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
                <?php if (!empty($records)) { ?>

                <table class="main-table" id="dataTable">
                    <thead>
                        <tr>
                        <th scope="col">Expense Date</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Expense Category</th>
                        <th scope="col" class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $row): ?>
                        <tr>
                            <td><?= date("M d, Y", strtotime($row['expense_date'])) ?></td>
                            <td>₱ <?= number_format($row['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($row['category']) ?></td>

                            <td class="col-actions">
                                <button class="edit-btn"
                                    data-id="<?= $row['id'] ?>"
                                    data-date="<?= $row['expense_date'] ?>"
                                    data-amount="<?= $row['amount'] ?>"
                                    data-category="<?= $row['category'] ?>"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                <?php if ($isAdmin): ?>
                                <a href="../model/delete_expense.php?id=<?= $row['id'] ?>" 
                                class="delete-btn">
                                <i class="fa-solid fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>

            </table>
            <?php } else { ?>
                <table class="main-table id="dataTable"">
                    <thead>
                        <tr>
                        <th scope="col">Expense Date</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Expense Category</th>
                        <th scope="col" class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="4" style="text-align:center;">
                				No Expense
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
    <div id="addExpenseModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3>Add Expense</h3>
                <button class="close-btn" onclick="closeAddExpenseModal()">&times;</button>
            </div>

            <form action="../model/create_expense.php" method="POST">
                
                <label>Date</label>
                <input type="date" name="date" required>

                <label>Enter Amount(₱)</label>
                <input type="number" name="amount" required placeholder="0.00">

                <label>Category</label>
                <div class="radio-group">
                    <label><input type="radio" name="category" value="Equipment" required> Equipment</label>
                    <label><input type="radio" name="category" value="Utilities"> Utilities</label>
                    <label><input type="radio" name="category" value="Fish Feeds" required> Fish Feeds</label>
                    <label><input type="radio" name="category" value="Fish Stocks" required> Fish Stocks</label>
                    <label><input type="radio" name="category" value="Transportation" required> Transportation</label>
                    <label><input type="radio" name="category" value="Equipment Maintenance"> Equipment Maintenance</label>
                    <label><input type="radio" name="category" value="Labor"> Labor</label>
                    <label><input type="radio" name="category" value="Others"> Others</label>
                </div>

                <div class="modal-actions">
                    <button type="submit">Save</button>
                    <button type="button" class="btn-cancel" onclick="closeAddExpenseModal()">Cancel</button>
                </div>
            </form>

        </div>
    </div>

    <div id="editExpenseModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3>Edit Expense</h3>
                <button class="close-btn" onclick="closeEditExpenseModal()">&times;</button>
            </div>

            <form action="../model/update_expense.php" method="POST">
                <input type="hidden" name="id" id="edit_id">

                <label>Expense Date</label>
                <input type="date" name="date" id="edit_date" required>

                <label>Amount</label>
                <input type="number" step="0.01" name="amount" id="edit_amount" required>

                <label>Category</label>

                <div class="radio-group">
                    <label><input type="radio" name="category" value="Equipment" required> Equipment</label>
                    <label><input type="radio" name="category" value="Utilities"> Utilities</label>
                    <label><input type="radio" name="category" value="Fish Feeds" required> Fish Feeds</label>
                    <label><input type="radio" name="category" value="Fish Stocks" required> Fish Stocks</label>
                    <label><input type="radio" name="category" value="Transportation" required> Transportation</label>
                    <label><input type="radio" name="category" value="Equipment Maintenance"> Equipment Maintenance</label>
                    <label><input type="radio" name="category" value="Labor"> Labor</label>
                    <label><input type="radio" name="category" value="Others"> Others</label>
                </div>

                <div class="modal-actions">
                    <button type="submit">Update</button>
                    <button type="button" class="btn-cancel" onclick="closeEditExpenseModal()">Cancel</button>
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
        function openAddExpenseModal() {
            document.getElementById("addExpenseModal").style.display = "flex";
        }

        function closeAddExpenseModal() {
            document.getElementById("addExpenseModal").style.display = "none";
        }

        function closePopup() {
            document.getElementById("systemPopup")?.remove();
        }

        function openEditExpenseModal(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_date').value = data.date;
            document.getElementById('edit_amount').value = data.amount;

            // Select correct radio
            document.querySelectorAll('#editExpenseModal input[name="category"]').forEach(radio => {
                radio.checked = (radio.value === data.category);
            });

            document.getElementById('editExpenseModal').style.display = 'flex';
        }

        function closeEditExpenseModal() {
            document.getElementById('editExpenseModal').style.display = 'none';
        }

        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                openEditExpenseModal(btn.dataset);
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
                setTimeout(openAddExpenseModal, 150);
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
            { width: "150px", targets: 0 }, // Expense Date
            { width: "130px", targets: 1 }, // Amount
            { width: "190px", targets: 2 }, // Expense Category
            { width: "90px", targets: 3 }  // Actions
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