<?php
session_start();
if (!isset($_SESSION['email']) || !isset($_SESSION['role']) || !isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

include "../auth/config.php";

$isAdmin = $_SESSION['role'] === 'admin';
$userId = (int)$_SESSION['id'];

// Get the status from the URL
$status = $_GET['status'] ?? '';
if (!$status) {
    echo "No status selected.";
    exit;
}

// Build SQL
$sql = "
SELECT * FROM (

    -- Fish Cage Management
    SELECT 'Fish Cage Management' AS type, fcm.id, fcm.result, fcm.status,
           fc.cage_name, s.schedule_datetime
    FROM fish_cage_management fcm
    JOIN schedules s ON s.id = fcm.schedule_id
    JOIN fish_cages fc ON fc.id = s.fish_cage
    WHERE fcm.status = '$status'
    " . (!$isAdmin ? "AND s.assigned_to = $userId" : "") . "

    UNION ALL

    -- Sampling
    SELECT 'Sampling', sm.id, '' AS result, sm.status,
           fc.cage_name, s.schedule_datetime
    FROM samplings sm
    JOIN schedules s ON s.id = sm.schedule_id
    JOIN fish_cages fc ON fc.id = s.fish_cage
    WHERE sm.status = '$status'
    " . (!$isAdmin ? "AND s.assigned_to = $userId" : "") . "

    UNION ALL

    -- Feeding
    SELECT 'Feeding', f.id, '' AS result, f.status,
           fc.cage_name, s.schedule_datetime
    FROM feedings f
    JOIN schedules s ON s.id = f.schedule_id
    JOIN fish_cages fc ON fc.id = s.fish_cage
    WHERE f.status = '$status'
    " . (!$isAdmin ? "AND s.assigned_to = $userId" : "") . "

    UNION ALL

    -- Stocking
    SELECT 'Stocking', st.id, '' AS result, st.status,
           fc.cage_name, s.schedule_datetime
    FROM stocking st
    JOIN schedules s ON s.id = st.schedule_id
    JOIN fish_cages fc ON fc.id = s.fish_cage
    WHERE st.status = '$status'
    " . (!$isAdmin ? "AND s.assigned_to = $userId" : "") . "

    UNION ALL

    -- Transferring
    SELECT 'Transferring', t.id, '' AS result, t.status,
           CONCAT(fc_from.cage_name, ' to ', fc_to.cage_name) AS cage_name,
           t.date_transferred AS schedule_datetime
    FROM transfers t
    LEFT JOIN fish_cages fc_from ON fc_from.id = t.from_cage
    LEFT JOIN fish_cages fc_to ON fc_to.id = t.to_cage
    WHERE t.status = '$status'
    " . (!$isAdmin ? "AND t.assigned_to = $userId" : "") . "

    UNION ALL

    -- Net Cleaning
    SELECT 'Net Cleaning', n.id, '' AS result, n.status,
           fc.cage_name, s.schedule_datetime
    FROM net_cleaning n
    JOIN schedules s ON s.id = n.schedule_id
    JOIN fish_cages fc ON fc.id = s.fish_cage
    WHERE n.status = '$status'
    " . (!$isAdmin ? "AND s.assigned_to = $userId" : "") . "

    UNION ALL

    -- Net Checking
    SELECT 'Net Checking', nc.id, '' AS result, nc.status,
           fc.cage_name, s.schedule_datetime
    FROM net_checking nc
    JOIN schedules s ON s.id = nc.schedule_id
    JOIN fish_cages fc ON fc.id = s.fish_cage
    WHERE nc.status = '$status'
    " . (!$isAdmin ? "AND s.assigned_to = $userId" : "") . "

    UNION ALL

-- Net Repairing
SELECT 'Net Repairing', nr.id, '' AS result, nr.status,
       fc.cage_name, s.schedule_datetime
FROM net_repairing nr
JOIN schedules s ON s.id = nr.schedule_id
JOIN fish_cages fc ON fc.id = s.fish_cage
WHERE nr.status = '$status'
" . (!$isAdmin ? "AND s.assigned_to = $userId" : "") . "

    UNION ALL

    -- Delivering
    SELECT 'Delivering', d.id, '' AS result, d.status,
           fc.cage_name, d.delivery_date AS schedule_datetime
    FROM deliveries d
    LEFT JOIN fish_cages fc ON fc.id = d.cage_id
    WHERE d.status = '$status'
    " . (!$isAdmin ? "AND d.assigned_to = $userId" : "") . "

) AS all_tasks
ORDER BY schedule_datetime DESC
";

// Fetch tasks
$tasks = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

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
    <title>Tasks - <?= htmlspecialchars($status) ?></title>
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
                Tasks - <?= htmlspecialchars($status) ?>
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

                            <th>Status</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td>
                                        <?= date('M d, Y', strtotime($task['schedule_datetime'])) ?><br> at <?= date('h:i A', strtotime($task['schedule_datetime'])) ?>
                                    </td>
                                    <td><?= htmlspecialchars($task['type']) ?></td>
                                    <td><?= htmlspecialchars($task['cage_name']) ?></td>
                                    
                                    <td><span class="status <?= strtolower($task['status']) ?>"><?= $task['status'] ?></span></td>
                                    
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
                title: 'Tasks - <?= htmlspecialchars($status) ?> Report', // Report title
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
                                <h2 style="margin: 0; font-size: 18px; color: #2d3748; text-transform: uppercase;">Tasks - <?= htmlspecialchars($status) ?> Report</h2>
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
                    { text: 'Tasks - <?= htmlspecialchars($status) ?> Report', style: 'reportTitle', alignment: 'right' },
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
            '30%', // Schedule
            '30%',
            '30%',
            '10%'  // Status
        ];
    }
doc.pageMargins = [35, 70, 40, 50]; // [left, top, right, bottom]

    }
}
        ],
        columnDefs: [
            { width: "150px", targets: 0 }, // Scheduled Date
            { width: "130px", targets: 1 }, // Type
            { width: "130px", targets: 2 }, // Cage
            { width: "120px", targets: 3 }  // Status
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
