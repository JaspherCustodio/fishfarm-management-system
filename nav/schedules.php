<?php
session_start();
if (!isset($_SESSION['email']) || !isset($_SESSION['role']) || !isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

include "../auth/config.php";

$isAdmin = $_SESSION['role'] === 'admin';
$userId = (int)$_SESSION['id'];

$filter = $_GET['filter'] ?? '';

// Build WHERE clauses for filtering and non-admin
$whereSchedule = "WHERE 1=1";
$whereOther = "WHERE 1=1";

if ($filter === 'last30days') {
    $whereSchedule .= " AND s.schedule_datetime >= CURDATE() - INTERVAL 30 DAY";
    $whereOther .= " AND t.schedule_datetime >= CURDATE() - INTERVAL 30 DAY";
}

// Past due tasks filter
if ($filter === 'due_this_week') {
    $whereSchedule .= " AND s.schedule_datetime < CURDATE()";
    $whereOther .= " AND t.schedule_datetime < CURDATE()";
    $pageTitle = "Past Due Tasks";
}

// Non-admins only see their own tasks
if (!$isAdmin) {
    $whereSchedule .= " AND s.assigned_to = $userId";
    $whereOther .= " AND t.assigned_to = $userId";
}

// Default page title
if (!isset($pageTitle)) $pageTitle = "All Tasks";
if ($filter === 'last30days') $pageTitle = "Schedules (Last 30 Days)";

// UNION ALL query to fetch all tasks with status != 'completed'
$sql = "
SELECT 'Fish Cage Management' AS type, fcm.id, fcm.status, s.schedule_datetime, fc.cage_name, u.name AS employee_name
FROM fish_cage_management fcm
LEFT JOIN schedules s ON s.id = fcm.schedule_id
LEFT JOIN fish_cages fc ON fc.id = s.fish_cage
LEFT JOIN users u ON u.id = s.assigned_to
$whereSchedule AND fcm.status != 'completed'

UNION ALL

SELECT 'Stocking' AS type, st.id, st.status, s.schedule_datetime, fc.cage_name, u.name AS employee_name
FROM stocking st
LEFT JOIN schedules s ON s.id = st.schedule_id
LEFT JOIN fish_cages fc ON fc.id = s.fish_cage
LEFT JOIN users u ON u.id = s.assigned_to
$whereSchedule AND st.status != 'completed'

UNION ALL

SELECT 'Transferring' AS type, tr.id, tr.status, s.schedule_datetime, fc.cage_name, u.name AS employee_name
FROM transfers tr
LEFT JOIN schedules s ON s.id = tr.schedule_id
LEFT JOIN fish_cages fc ON fc.id = s.fish_cage
LEFT JOIN users u ON u.id = s.assigned_to
$whereSchedule AND tr.status != 'completed'

UNION ALL

SELECT 'Delivering' AS type, dl.id, dl.status, dl.delivery_date AS schedule_datetime, fc.cage_name, u.name AS employee_name
FROM deliveries dl
LEFT JOIN fish_cages fc ON fc.id = dl.cage_id
LEFT JOIN users u ON u.id = dl.assigned_to
WHERE dl.delivery_date < CURDATE() AND dl.status != 'completed'
";

if (!$isAdmin) $sql .= " AND dl.assigned_to = $userId";

$sql .= "

UNION ALL

SELECT 'Feeding' AS type, fd.id, fd.status, s.schedule_datetime, fc.cage_name, u.name AS employee_name
FROM feedings fd
LEFT JOIN schedules s ON s.id = fd.schedule_id
LEFT JOIN fish_cages fc ON fc.id = s.fish_cage
LEFT JOIN users u ON u.id = s.assigned_to
$whereSchedule AND fd.status != 'completed'

UNION ALL

SELECT 'Sampling' AS type, sm.id, sm.status, s.schedule_datetime, fc.cage_name, u.name AS employee_name
FROM samplings sm
LEFT JOIN schedules s ON s.id = sm.schedule_id
LEFT JOIN fish_cages fc ON fc.id = s.fish_cage
LEFT JOIN users u ON u.id = s.assigned_to
$whereSchedule AND sm.status != 'completed'

UNION ALL

SELECT 'Net Cleaning' AS type, nc.id, nc.status, s.schedule_datetime, fc.cage_name, u.name AS employee_name
FROM net_cleaning nc
LEFT JOIN schedules s ON s.id = nc.schedule_id
LEFT JOIN fish_cages fc ON fc.id = s.fish_cage
LEFT JOIN users u ON u.id = s.assigned_to
$whereSchedule AND nc.status != 'completed'

UNION ALL

SELECT 'Net Checking' AS type, nck.id, nck.status, s.schedule_datetime, fc.cage_name, u.name AS employee_name
FROM net_checking nck
LEFT JOIN schedules s ON s.id = nck.schedule_id
LEFT JOIN fish_cages fc ON fc.id = s.fish_cage
LEFT JOIN users u ON u.id = s.assigned_to
$whereSchedule AND nck.status != 'completed'

UNION ALL

SELECT 'Net Repairing' AS type, nr.id, nr.status, s.schedule_datetime, fc.cage_name, u.name AS employee_name
FROM net_repairing nr
LEFT JOIN schedules s ON s.id = nr.schedule_id
LEFT JOIN fish_cages fc ON fc.id = s.fish_cage
LEFT JOIN users u ON u.id = s.assigned_to
$whereSchedule AND nr.status != 'completed'

ORDER BY schedule_datetime DESC
";

$result = $conn->query($sql);
$tasks = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

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
    <title><?= $pageTitle ?></title>
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
            <h4>
  <a href="dashboard.php" style="color: inherit; text-decoration: none;">
    <i class="fa-solid fa-arrow-left"></i>
  </a>
  <?= $pageTitle ?>
</h4>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h3 class="title">Task List</h3>
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

            <table class="main-table" id="dataTable">
                <thead>
                    <tr>
                        <th>Scheduled Date</th>
                        <th>Type</th>
                        <th>Cage</th>
                        <th>Assigned To</th>  
                    </tr>
                </thead>
                <tbody>
                        <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td>
                                <?= date('M d, Y', strtotime($task['schedule_datetime'])) ?><br> at <?= date('h:i A', strtotime($task['schedule_datetime'])) ?>
                            </td>
                            <td><?= htmlspecialchars($task['type']) ?></td>
                            <td><?= htmlspecialchars($task['cage_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($task['employee_name'] ?? 'Unassigned') ?></td>
                        </tr>
                        <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php include "../inc/footer.php" ?>
    </section>
</div>

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
            document.addEventListener("DOMContentLoaded", function () {

        const currentPage = window.location.pathname.split("/").pop().split("?")[0];

        document.querySelectorAll(".side-bar a").forEach(link => {

            const linkPage = link.getAttribute("href")
                ?.split("/")
                .pop()
                .split("?")[0];

            // Exact match
            if (linkPage === currentPage) {
                link.classList.add("active-link");
            }

            // 🔵 Treat these pages as Dashboard
            if (
                (currentPage === "tasks.php" ||
                currentPage === "schedules.php" ||
                currentPage === "dashboard.php") &&
                linkPage === "dashboard.php"
            ) {
                link.classList.add("active-link");
            }
        });

    });
var table; //variable to hold datatable object
var logoBase64 = "<?= $base64Logo ?>";

        $(document).ready(function () {
        table = $('#dataTable').DataTable({
        paging: true,
        pageLength: 10,             // default rows per page
        lengthChange: false,        // allow user to change dropdown options
        pagingType: "simple",
        dom: 'Brtip', // show buttons
        buttons: [
            {
                extend: 'excelHtml5',
                className: 'button-excel',
                title: '<?= $pageTitle ?> Report', // Report title
            messageTop: 'DPA Fish Farm - Jala-Jala, Rizal \nDate: ' + 
                new Date().toLocaleDateString('en-US', { dateStyle: 'long' }),
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
                <c r="C${footerRow}" t="inlineStr"><is><t>Approved By: ____________________</t></is></c>
            </row>
        `;
        
        sheet.childNodes[0].childNodes[1].innerHTML += signatureRows;

}
    },
             // Print Export
            {
                extend: 'print',
                className: 'button-print',
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
                                <h2 style="margin: 0; font-size: 18px; color: #2d3748; text-transform: uppercase;"><?= $pageTitle ?> Report</h2>
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
                    { text: '<?= $pageTitle ?> Report', style: 'reportTitle', alignment: 'right' },
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
            '25%', // Schedule
            '25%',
            '25%',
            '25%'  // Status
        ];
    }
doc.pageMargins = [35, 70, 40, 50]; // [left, top, right, bottom]

    }
}
        ],
        columnDefs: [
            { width: "160px", targets: 0 }, // Scheduled Date
            { width: "160px", targets: 1 }, // Type
            { width: "120px", targets: 2 }, // Cage
            { width: "140px", targets: 3 }  // Assigned To
        ],
        scrollX: true,        // enable horizontal scroll if needed
        autoWidth: false,      // important: lets columnDefs widths take effect
        order: [[0, 'desc']], // sort by Created column descending
        language: {
            emptyTable: "No tasks found" // <-- put this here, no second initialization
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
</script>

</body>
</html>