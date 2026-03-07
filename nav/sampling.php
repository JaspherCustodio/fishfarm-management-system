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
        <div class="sidebar-overlay" onclick="closeSidebar()"></div>
		<section class="section-1">
            <div class="content-header">
                <h4>Sampling</h4>
                <button class="menu-toggle" onclick="openSidebar()">
                    <i class="fa-solid fa-bars"></i>
                </button>
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
                        <button id="btnPdf" class="btn btn-primary">PDF</button>
                    </div>
                    <div class="right-search">
                        <label for="tableSearch">Search: 
                            <input type="text" id="tableSearch" >
                        </label>
                    </div>
                </div>

                <!-- table here -->

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
                        <td><?= date('M d, Y', strtotime($row['schedule_datetime'])) ?><br> at <?= date('h:i A', strtotime($row['schedule_datetime'])) ?></td>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
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
                title: 'Sampling Report', // Report title
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
                <c r="E${footerRow}" t="inlineStr"><is><t>Approved By: ____________________</t></is></c>
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
                                <h2 style="margin: 0; font-size: 18px; color: #2d3748; text-transform: uppercase;">Sampling Report</h2>
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
                        'font-size': '14px',
                        'text-transform': 'uppercase'
                    });

                    // Body Cell Styling
                    table.find('td').css({
                        'border': '1px solid #000',
                        'padding': '10px',
                        'font-size': '14px',
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
                    { text: 'Sampling Report', style: 'reportTitle', alignment: 'right' },
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
            '14%', // Schedule
            '16%', // Cage Name
            '18%', // Checking Start Date
            '12%', // Time Started
            '18%', // Checking End Date
            '12%', // Time Ended
            '10%'  // Status
        ];
    }
doc.pageMargins = [35, 70, 40, 50]; // [left, top, right, bottom]

    }
}
        ],
        columnDefs: [
            { width: "120px", targets: 0 }, // Schedule
            { width: "130px", targets: 1 }, // Cage Name
            { width: "150px", targets: 2 }, // Date of Sampling
            { width: "140px", targets: 3 }, // Fish Type
            { width: "110px", targets: 4 }, // Weight
            { width: "110px", targets: 5 }, // Length
            { width: "110px", targets: 6 }, // Status
            { width: "100px", targets: 7 }  // Actions
        ],
        scrollX: true,        // enable horizontal scroll if needed
        autoWidth: false,      // important: lets columnDefs widths take effect
        order: [[0, 'desc']], // sort by Created column descending
        language: {
            emptyTable: "No sampling found" // <-- put this here, no second initialization
        }
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