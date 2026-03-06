<?php
session_start();

// Check if user is logged in first
if (!isset($_SESSION['email'], $_SESSION['role'], $_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

$isAdmin = ($_SESSION['role'] === 'admin');

include "../auth/config.php";
include "../model/deliveries.php";

$deliveries = get_all_deliveries($conn);

$popupSuccess = $_SESSION['popup_success'] ?? '';
$popupError   = $_SESSION['popup_error'] ?? '';
unset($_SESSION['popup_success'], $_SESSION['popup_error']);

// Fetch cages and stockings
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

$employees = $conn->query("SELECT id, name FROM users WHERE role='user' AND status='approved'")->fetch_all(MYSQLI_ASSOC);

// Put this at the top of your PHP file to get the string automatically
$path = '../assets/img/dpa-logo.png';
$type = pathinfo($path, PATHINFO_EXTENSION);
$data = file_get_contents($path);
$base64Logo = 'data:image/' . $type . ';base64,' . base64_encode($data);

if (!$data) {
    die("Logo not found at $path");
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/img/dpa-logo.png">
    <title>Delivering</title>
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
        <h4>Delivering</h4>
        <button class="menu-toggle" onclick="openSidebar()">
                    <i class="fa-solid fa-bars"></i>
                </button>
    </div>
    
    <div class="content-card">
        <div class="card-header">
            <h3 class="title">Data Management</h3>
            <?php if ($isAdmin): ?>
            <button class="btn-add" onclick="openAddDeliveringModal()">
                <i class="fa-solid fa-plus"></i> New Record
            </button>
            <?php endif; ?>
        </div>

        <div class="table-tools">
                    <div class="left-buttons">
                        <button id="btnPrintDetail" class="btn btn-primary">Print</button>
                        <button id="btnExcel" class="btn btn-primary">Excel</button>
                        <button id="btnPdf" class="btn btn-primary">PDF</button>
                    </div>
                    <div class="right-search">
                        <label for="tableSearch">Search: 
                            <input type="text" id="tableSearch" >
                        </label>
                    </div>
                </div>

        <?php if (!empty($deliveries)) { ?>
        <table class="main-table" id="dataTable">
            <thead>
                <tr>
                    <th>Schedule</th>
                    <th>Cage</th>
                    <th>Fish Type</th>
                    <th>Delivery Date</th>
                    <th>Quantity Delivered</th>
                    <th>Buyer Name</th>
                    <th>Amount Received</th>
                    <th>Remarks</th>
                    <th>Status</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($deliveries as $row): ?>
            <tr>
                <td><?= date('M d, Y', strtotime($row['schedule_datetime'])) ?><br> at <?= date('h:i A', strtotime($row['schedule_datetime'])) ?></td>
                <td><?= htmlspecialchars($row['cage_name']) ?></td>
                <td><?= htmlspecialchars($row['fish_type']) ?></td>
                <td>
<?= !empty($row['delivery_date']) && $row['delivery_date'] != '0000-00-00'
    ? date('M d, Y', strtotime($row['delivery_date']))
    : '-' ?>
</td>
                <td>
<?= isset($row['quantity_delivered']) && $row['quantity_delivered'] > 0
    ? (int)$row['quantity_delivered']
    : '-' ?>
</td>
                <td title="<?= !empty($row['buyer_name']) && $row['buyer_name'] != 0 ? htmlspecialchars($row['buyer_name']) : '-' ?>"><?= !empty($row['buyer_name']) && $row['buyer_name'] != 0 ? htmlspecialchars($row['buyer_name']) : '-' ?></td>
                <td>
<?= isset($row['sale_amount']) && $row['sale_amount'] > 0
    ? '₱ ' . number_format($row['sale_amount'], 2)
    : '-' ?>
</td>
                <td title="<?= $row['remarks'] ?: '-' ?>"><?= $row['remarks'] ?: '-' ?></td>
                <td><?= htmlspecialchars($row['status'] ?? '-') ?></td>
                <td class="col-actions">
                    <button class="edit-btn"
    data-id="<?= htmlspecialchars($row['id'], ENT_QUOTES) ?>"
    data-assigned_to="<?= $row['assigned_to'] ?>"
    data-stocking_id="<?= $row['stocking_id'] ?>"
    data-fish_type="<?= htmlspecialchars($row['fish_type'], ENT_QUOTES) ?>"
    data-cage_name="<?= htmlspecialchars($row['cage_name'], ENT_QUOTES) ?>"
    data-stock_date="<?= date('M d Y', strtotime($row['schedule_datetime'])) ?>"
    data-delivery_date="<?= htmlspecialchars($row['delivery_date'], ENT_QUOTES) ?>"
    data-quantity_delivered="<?= htmlspecialchars($row['quantity_delivered'], ENT_QUOTES) ?>"
    data-buyer_name="<?= htmlspecialchars($row['buyer_name'] ?? '', ENT_QUOTES) ?>"
    data-sale_amount="<?= htmlspecialchars($row['sale_amount'] ?? 0, ENT_QUOTES) ?>"
    data-remarks="<?= htmlspecialchars($row['remarks'] ?? '', ENT_QUOTES) ?>"
    data-status="<?= $row['status'] ?>"
>
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <?php if ($isAdmin): ?>
                    <a href="../model/delete_delivery.php?id=<?= $row['id'] ?>" class="delete-btn">
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
                    <th>Schedule</th>
                    <th>Cage</th>
                    <th>Fish Type</th>
                    <th>Delivery Date</th>
                    <th>Quantity Delivered</th>
                    <th>Buyer Name</th>
                    <th>Amount Received</th>
                    <th>Remarks</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="10" style="text-align:center;">No Delivering Record</td></tr>
            </tbody>
        </table>
        <?php } ?>
    </div>
    <?php include "../inc/footer.php" ?>
</section>
</div>

<!-- Add Modal -->
<div id="addDeliveringModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>Add Record</h3>
            <button class="close-btn" onclick="closeAddDeliveringModal()">&times;</button>
        </div>

        <form action="../model/create_delivery.php" method="POST">
            <label>Assign Employee</label>
            <select name="assigned_to" required>
                <option value="">-- Select Employee --</option>
                <?php foreach($employees as $emp): ?>
                    <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Stocking Reference</label>
            <select name="stocking_id" id="stocking_id" required>
                <option value="">-- Select Stocking --</option>
                <?php foreach($stockings as $s): ?>
                <option value="<?= $s['id'] ?>" data-cage-id="<?= $s['cage_id'] ?>" data-quantity="<?= $s['current_quantity'] ?>">
                    <?= htmlspecialchars($s['fish_type']) ?> | <?= htmlspecialchars($s['cage_name']) ?> | <?= date('M d Y', strtotime($s['display_date'])) ?> | Qty: <?= $s['current_quantity'] ?>
                </option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="cage_id" id="cage_id">

            <label>Buyer Name | Place</label>
            <input type="text" name="buyer_name" placeholder="Optional">

            <label>Quantity Delivered</label>
            <input type="number" name="quantity_delivered" id="quantity_delivered" min="1" required placeholder="Enter quantity delivered">

            <div class="modal-actions">
                <button type="submit">Save</button>
                <button type="button" class="btn-cancel" onclick="closeAddDeliveringModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editDeliveringModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Record</h3>
            <button class="close-btn" onclick="closeEditDeliveringModal()">&times;</button>
        </div>

        <form action="../model/update_delivery.php" method="POST">
            <input type="hidden" name="id" id="edit_id">
            
            <?php if ($isAdmin): ?>
            <label>Assigned Employee</label>
            <select name="assigned_to" id="edit_assigned_to" required>
                <option value="">-- Select Employee --</option>
                <?php foreach($employees as $emp): ?>
                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label>Stocking Reference</label>
<input type="text" id="edit_stocking_display" readonly class="readonly-field">

<input type="hidden" name="stocking_id" id="edit_stocking_id">


            <?php endif; ?>

            <label>Delivery Date</label>
            <input type="date" name="delivery_date" id="edit_delivery_date" required>
            <label>Quantity Delivered</label>
            <input type="number" id="edit_quantity_delivered" min="1" disabled class="readonly-field">
<input type="hidden" name="quantity_delivered" id="edit_quantity_hidden">
            <label>Buyer Name | Place</label>
            <input type="text" name="buyer_name" id="edit_buyer_name" placeholder="Optional">
            <label>Amount Received (₱)</label>
            <input type="number" name="sale_amount" id="edit_sale_amount" step="0.01" min="0" placeholder="Enter actual received amount">
            <label>Remarks</label>
            <textarea name="remarks" id="edit_remarks" placeholder="Optional"></textarea>
            <label>Status</label>
            <select name="status" id="edit_status" required>
                <option value="Pending">Pending</option>
                <option value="Cancelled">Cancelled</option>
                <option value="Ongoing">Ongoing</option>
                <option value="Completed">Completed</option>
            </select>

            <div class="modal-actions">
                <button type="submit">Update</button>
                <button type="button" class="btn-cancel" onclick="closeEditDeliveringModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<?php if ($popupSuccess || $popupError): ?>
<div id="systemPopup" class="popup-overlay">
    <div class="popup-box <?= $popupSuccess ? 'success' : 'error' ?>">
        <div class="popup-icon"><?= $popupSuccess ? '✓' : '⚠' ?></div>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
// ===== ADD MODAL =====
const stockingSelect = document.getElementById('stocking_id');
const cageHidden = document.getElementById('cage_id');
const quantityInput = document.getElementById('quantity_delivered');

if (stockingSelect) {
    stockingSelect.addEventListener('change', function() {
        const selected = this.selectedOptions[0];
        cageHidden.value = selected?.dataset.cageId || '';
        quantityInput.max = selected?.dataset.quantity || '';
    });
}

function openAddDeliveringModal(){ document.getElementById("addDeliveringModal").style.display = "flex"; }
function closeAddDeliveringModal(){ document.getElementById("addDeliveringModal").style.display = "none"; }
function closePopup(){ document.getElementById("systemPopup")?.remove(); }

// ===== EDIT MODAL =====
function openEditDeliveringModal(data){
    document.getElementById('edit_id').value = data.id;

    // Admin: assigned employee
    const editAssignedTo = document.getElementById('edit_assigned_to');
    if(editAssignedTo) Array.from(editAssignedTo.options).forEach(opt => opt.selected = (opt.value == data.assigned_to));

    
    const stockingDisplay = document.getElementById('edit_stocking_display');
const stockingIdInput = document.getElementById('edit_stocking_id');

if (stockingDisplay) {
    stockingDisplay.value =
        data.fish_type + " | " + data.cage_name + " | " + data.stock_date;
}

if (stockingIdInput) {
    stockingIdInput.value = data.stocking_id;
}

    // Common fields
    document.getElementById('edit_delivery_date').value = data.delivery_date || '';
    document.getElementById('edit_quantity_delivered').value = data.quantity_delivered || '';
    document.getElementById('edit_buyer_name').value = (data.buyer_name && data.buyer_name !== '0') ? data.buyer_name : '';
    document.getElementById('edit_sale_amount').value = data.sale_amount || '';
    document.getElementById('edit_remarks').value = data.remarks || '';
    document.getElementById('edit_status').value = data.status || 'Pending';

    document.getElementById('edit_quantity_hidden').value = data.quantity_delivered || '';

    document.getElementById('editDeliveringModal').style.display = 'flex';
}
function closeEditDeliveringModal(){ document.getElementById('editDeliveringModal').style.display = 'none'; }

// Edit button click
document.querySelectorAll('.edit-btn').forEach(btn => btn.addEventListener('click', () => openEditDeliveringModal(btn.dataset)));

// Delete confirmation
document.querySelectorAll('.delete-btn').forEach(btn => btn.addEventListener('click', e => {
    if(!confirm("Are you sure you want to delete this record?")) e.preventDefault();
}));

// Auto-open Add Modal
window.addEventListener('load', () => {
    const params = new URLSearchParams(window.location.search);
    if(params.get('add') === 'true' && <?= $isAdmin ? 'true' : 'false' ?>) setTimeout(openAddDeliveringModal, 150);
});

var table; //variable to hold datatable object
var logoBase64 = "<?= $base64Logo ?>";

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
                title: 'Delivering Report', // Report title
            messageTop: 'DPA Fish Farm - Jala-Jala, Rizal \nDate: ' + 
                new Date().toLocaleDateString('en-US', { dateStyle: 'long' }),
            exportOptions: {
                columns: ':not(:last-child)' // exclude the Actions column
            },
            init: function(api, node) { $(node).hide(); }, // hide default button
            customize: function (xlsx) {

    var sSh = xlsx.xl['styles.xml'];
    var sheet = xlsx.xl.worksheets['sheet1.xml'];

    /* ===============================
       1️⃣ ADD BOLD FONT
    =============================== */
    var boldFont =
        '<font>' +
            '<sz val="12"/>' +       // font size for title
            '<name val="Calibri"/>' +
            '<b/>' +
        '</font>';

    $('fonts', sSh).append(boldFont);
    var boldFontIndex = $('fonts font', sSh).length - 1;


    /* ===============================
       2️⃣ ADD THIN BORDER
    =============================== */
    var thinBorder =
        '<border>' +
            '<left style="thin"><color auto="1"/></left>' +
            '<right style="thin"><color auto="1"/></right>' +
            '<top style="thin"><color auto="1"/></top>' +
            '<bottom style="thin"><color auto="1"/></bottom>' +
            '<diagonal/>' +
        '</border>';

    $('borders', sSh).append(thinBorder);
    var borderIndex = $('borders border', sSh).length - 1;


    /* ===============================
       3️⃣ ADD FILL (HEADER BACKGROUND COLOR #e1e1e1)
    =============================== */
    var headerFill =
        '<fill>' +
            '<patternFill patternType="solid">' +
                '<fgColor rgb="FFE1E1E1"/>' +  // Light gray fill
                '<bgColor indexed="64"/>' +
            '</patternFill>' +
        '</fill>';

    $('fills', sSh).append(headerFill);
    var headerFillIndex = $('fills fill', sSh).length - 1;


    /* ===============================
       4️⃣ STYLE: BOLD ONLY + CENTER (Title)
    =============================== */
    var boldCenter =
        '<xf numFmtId="0" fontId="' + boldFontIndex + '" fillId="0" borderId="0" applyFont="1" applyAlignment="1">' +
        '<alignment horizontal="center"/>' +
        '</xf>';

    $('cellXfs', sSh).append(boldCenter);
    var boldCenterIndex = $('cellXfs xf', sSh).length - 1;


    /* ===============================
       5️⃣ STYLE: HEADER BOLD + BORDER + BACKGROUND
    =============================== */
    var boldBorderFill =
        '<xf numFmtId="0" fontId="' + boldFontIndex + '" fillId="' + headerFillIndex + '" borderId="' + borderIndex + '" ' +
        'applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">' +
        '<alignment horizontal="center"/>' +
        '</xf>';

    $('cellXfs', sSh).append(boldBorderFill);
    var boldBorderFillIndex = $('cellXfs xf', sSh).length - 1;


    /* ===============================
       6️⃣ STYLE: BORDER ONLY (Body)
    =============================== */
    var borderOnly =
        '<xf numFmtId="0" fontId="0" fillId="0" borderId="' + borderIndex + '" applyBorder="1"/>' ;

    $('cellXfs', sSh).append(borderOnly);
    var borderOnlyIndex = $('cellXfs xf', sSh).length - 1;


    /* ===============================
       7️⃣ APPLY STYLES
    =============================== */

    // Row 1 → TITLE (bold + center)
    $('row:eq(0) c', sheet).attr('s', boldCenterIndex);

    // Row 3 → TABLE HEADER (bold + border + background)
    $('row:eq(2) c', sheet).attr('s', boldBorderFillIndex);

    // BODY → border only (everything below header)
    $('row:gt(2) c', sheet).attr('s', borderOnlyIndex);

    // 4. Add "Prepared By" at the bottom
        // We find the last row and append new rows for signatures
        var lastRow = $('row', sheet).last();
        var lastRowIdx = parseInt(lastRow.attr('r'));
        var footerRow = lastRowIdx + 3; // Leave some space

        var signatureRows = `
            <row r="${footerRow}">
                <c r="A${footerRow}" t="inlineStr"><is><t>Prepared By: ____________________</t></is></c>
                <c r="G${footerRow}" t="inlineStr"><is><t>Approved By: ____________________</t></is></c>
            </row>
        `;
        
        sheet.childNodes[0].childNodes[1].innerHTML += signatureRows;

}
    },
            // Print Export
            {
                extend: 'print',
                className: 'button-print',
                exportOptions: {
                    columns: ':not(:last-child)' // exclude last column (Actions)
                },
                init: function(api, node) { $(node).hide(); },
                customize: function (win) {
                    const root = $(win.document.body);

                    // 1. Setup Document Basics
                    root.css({
                        'font-family': '"Helvetica Neue", Helvetica, Arial, sans-serif',
                        'background-color': '#fff'
                    }).find('h1').hide(); // Hide default DataTables title

                    // 2. Professional Header (Logo Left, Title Right)
                    root.prepend(`
                        <div style="display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px;">
                            <div style="display: flex; align-items: center;">
                                <img src="../assets/img/dpa-logo.png" style="height: 70px; margin-right: 15px;">
                                <div>
                                    <h1 style="margin: 0; font-size: 26px; color: #1a202c; font-weight: bold;">DPA FISH FARM</h1>
                                    <p style="margin: 0; font-size: 12px; color: #4a5568;">JALA-JALA, RIZAL - REGION IV-A</p>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <h2 style="margin: 0; font-size: 18px; color: #2d3748; text-transform: uppercase;">Transferring Report</h2>
                                <p style="margin: 0; font-size: 11px; color: #718096;">Date: ${new Date().toLocaleDateString('en-US', { dateStyle: 'long' })}</p>
                            </div>
                        </div>
                    `);

                    // 3. Table Styling
                    const table = root.find('table');
                    table.addClass('compact').css({
                        'width': '100%',
                        'border-collapse': 'collapse'
                    });

                    // Header Row Styling
                    table.find('th').css({
                        'background-color': '#e1e1e1',
                        'color': '#333',
                        'border': '1px solid #000',
                        'padding': '12px',
                        'font-size': '12px',
                        'text-transform': 'uppercase'
                    });

                    // Body Cell Styling
                    table.find('td').css({
                        'border': '1px solid #000',
                        'padding': '10px',
                        'font-size': '12px',
                        'text-align': 'left'
                    });

                    // 4. Professional Footer with Signature Lines
                    root.append(`
                        <div style="margin-top: 100px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 40px;">
                                <div style="width: 250px; text-align: center;">
                                    <div style="border-top: 1px solid #000; margin-bottom: 5px;"></div>
                                    <p style="margin: 0; font-size: 12px; font-weight: bold;">Prepared By</p>
                                </div>
                                <div style="width: 250px; text-align: center;">
                                    <div style="border-top: 1px solid #000; margin-bottom: 5px;"></div>
                                    <p style="margin: 0; font-size: 12px; font-weight: bold;">Approved By</p>
                                </div>
                            </div>
                        </div>
                    `);

                    table.css('page-break-inside', 'auto');
                    table.find('tr').css('page-break-inside', 'avoid').css('page-break-after', 'auto');
                }
            },
            {
    extend: 'pdfHtml5',
    className: 'button-pdf',
    title: '', // we will add custom header
    pageSize: 'A4',
    orientation: 'portrait',
    exportOptions: {
        columns: ':not(:last-child)' // exclude Actions
    },
    init: function(api, node) { $(node).hide(); }, // hide default button
    customize: function (doc) {

        // 2️⃣ Header with logo + title + date
        doc['header'] = function() {
    return {
        columns: [
            {
                image: logoBase64,
                width: 35,          // slightly bigger logo
                margin: [0, 0, 10, 0] // right margin to separate from text
            },
            {
                stack: [
                    { text: 'DPA FISH FARM', style: 'header', alignment: 'left' },
                    { text: 'JALA-JALA, RIZAL - REGION IV-A', style: 'subheader', alignment: 'left', fontSize: 10, margin: [0, 0, 0, 0] }
                ],
                alignment: 'left'   // must align stack left
            },
            {
                stack: [
                    { text: 'Delivering Report', style: 'reportTitle', alignment: 'right' },
                    { text: 'Date: ' + new Date().toLocaleDateString('en-US', { dateStyle: 'long' }), alignment: 'right', fontSize: 10 }
                ],
                alignment: 'right' // must align stack right
            }
        ],
        widths: [50, '*', '*'],   // logo fixed, others expand
        margin: [35, 25, 40, 0] // [left, top, right, bottom]
    };
};

        // 3️⃣ Table styles
        doc.styles.tableHeader = {
            fillColor: '#e1e1e1',
            bold: true,
            fontSize: 10,
            color: '#000',
            alignment: 'center'
        };

        // Table body style
        doc.defaultStyle.fontSize = 8;
        doc.styles.tableHeader.fontSize = 10;

        // 4️⃣ Title style
        doc.styles.reportTitle = {
            bold: true,
            fontSize: 14
        };

        doc.styles.header = {
            bold: true,
            fontSize: 14
        };

        doc.styles.subheader = {
            italics: true,
            fontSize: 10
        };

        // 5️⃣ Footer with signatures
        doc['footer'] = function(currentPage, pageCount) {
            return {
                columns: [
                    {
                        stack: [
                            { text: '____________________', alignment: 'center' },
                            { text: 'Prepared By', alignment: 'center', bold: true, fontSize: 10 }
                        ],
                        width: '40%'
                    },
                    { text: '', width: '20%' }, // spacing
                    {
                        stack: [
                            { text: '____________________', alignment: 'center' },
                            { text: 'Approved By', alignment: 'center', bold: true, fontSize: 10 }
                        ],
                        width: '40%'
                    }
                ],
                margin: [20, 0]
            };
        };

        // 6️⃣ Make table not cut across pages
        doc.content[0].layout = {
            hLineWidth: function(i, node) { return 0.5; },
            vLineWidth: function(i, node) { return 0.5; },
            hLineColor: function(i, node) { return '#000'; },
            vLineColor: function(i, node) { return '#000'; },
            paddingLeft: function(i, node) { return 5; },
            paddingRight: function(i, node) { return 5; },
            paddingTop: function(i, node) { return 5; },
            paddingBottom: function(i, node) { return 5; }
        };

        // Ensure table does not stretch weirdly
    if (doc.content && doc.content.length) {
        // doc.content[0] is usually the table
        doc.content[0].table.widths = [
            '12%', // Schedule
            '10%', // Cage
            '12%', // Fish Type
            '12%', // Delivery Date
            '10%', // Quantity Delivered
            '12%', // Buyer Name
            '12%', // Amount Received
            '10%', // Remarks
            '10%'  // Status
        ];
    }
doc.pageMargins = [35, 70, 40, 50]; // [left, top, right, bottom]

    }
}
        ],
        columnDefs: [
            { width: "120px", targets: 0 }, // Schedule
            { width: "130px", targets: 1 }, // Cage
            { width: "140px", targets: 2 }, // Fish Type
            { width: "150px", targets: 3 }, // Delivery Date
            { width: "140px", targets: 4 }, // Quantity Delivered
            { width: "170px", targets: 5 }, // Buyer Name
            { width: "140px", targets: 6 }, // Amount Received
            { width: "180px", targets: 7 }, // Remarks
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

        $('#btnPdf').on('click', function(){
            //trigger datatable excel button click
            table.button('.button-pdf').trigger();
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