<?php 
session_start();

$popupSuccess = $_SESSION['popup_success'] ?? '';
$popupError   = $_SESSION['popup_error'] ?? '';

unset($_SESSION['popup_success'], $_SESSION['popup_error']);

if (
    !isset($_SESSION['email']) ||
    !isset($_SESSION['role']) ||
    !isset($_SESSION['id']) ||
    $_SESSION['role'] !== "admin"
) {
    header("Location: ../index.php");
    exit();
}

include "../auth/config.php";
include "../model/user.php";

$users = get_all_users($conn);

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
	<title>User Management</title>
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
                <h4>User Management</h4>
                <button class="menu-toggle" onclick="openSidebar()">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
            
            <div class="content-card">
                <div class="card-header">
                    <h3 class="title">Data Management</h3>
                    <button class="btn-add" onclick="openAddUserModal()">
                        <i class="fa-solid fa-plus"></i> Add User
                    </button>
                    <a href="approved_user.php" class="btn-add" style="background-color: #e67e22; text-decoration: none; display: flex; align-items: center; height: 45px; justify-content: center;">
                        <i class="fa-solid fa-clock-rotate-left" style="margin-right: 5px;"></i> Pending Queue
                    </a>
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
                        <th scope="col">Email</th>
                        <th scope="col">Contact No</th>
                        <th scope="col">Full name</th>
                        <th scope="col">Role</th>
                        <th scope="col" class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=0; foreach ($users as $user) { ?>
                        <tr>
                        <td><?=$user['email']?></td>
                        <td><?=$user['mobile_num']?></td>
                        <td><?=$user['name']?></td>
                        <td><?=$user['role']?></td>
                        <td class="col-actions">
                            <button class="edit-btn"
                                onclick="openEditUserModal(
                                    '<?=$user['id']?>',
                                    '<?=$user['name']?>',
                                    '<?=$user['email']?>',
                                    '<?=$user['mobile_num']?>',
                                    '<?=$user['role']?>'
                                )">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>

                            <a href="../model/delete_user.php?id=<?=$user['id']?>" class="delete-btn"><i class="fa-solid fa-trash"></i></a>
                        </td>
                        </tr>
                    <?php	} ?>
                    </tbody>
                </table>
            </div>
            <?php include "../inc/footer.php" ?>
		</section>
	</div>
    

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3>Add User</h3>
                <button class="close-btn" onclick="closeAddUserModal()">&times;</button>
            </div>

            <form action="../auth/login_register.php" method="POST">

                <input type="hidden" name="from_admin" value="1">

                <input type="text" name="name" placeholder="Full name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="mobile_num" placeholder="Contact No" required>
                <input type="password" name="password" placeholder="Password" required>

                <select name="role" required>
                    <option value="">Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>

                <div class="modal-actions">
                    <button type="submit" name="register">Save User</button>
                    <button type="button" class="btn-cancel" onclick="closeAddUserModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3>Edit User</h3>
                <button class="close-btn" onclick="closeEditUserModal()">&times;</button>
            </div>

            <form action="../model/update_user.php" method="POST">

                <input type="hidden" name="id" id="edit_id">

                <input type="text" name="name" id="edit_name" required>
                <input type="email" name="email" id="edit_email" required>
                <input type="text" name="mobile_num" id="edit_mobile" required>
                <input type="password" name="password" placeholder="New Password (leave blank to keep current)">


                <select name="role" id="edit_role" required>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>

                <div class="modal-actions">
                    <button type="submit">Update</button>
                    <button type="button" class="btn-cancel" onclick="closeEditUserModal()">Cancel</button>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script>
        function openAddUserModal() {
            document.getElementById("addUserModal").style.display = "flex";
        }

        function closeAddUserModal() {
            document.getElementById("addUserModal").style.display = "none";
        }

        function closePopup() {
            document.getElementById("systemPopup").remove();
        }

        function openEditUserModal(id, name, email, mobile, role) {
            document.getElementById("edit_id").value = id;
            document.getElementById("edit_name").value = name;
            document.getElementById("edit_email").value = email;
            document.getElementById("edit_mobile").value = mobile;
            document.getElementById("edit_role").value = role;

            document.getElementById("editUserModal").style.display = "flex";
        }

        function closeEditUserModal() {
            document.getElementById("editUserModal").style.display = "none";
        }

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                if (!confirm("Are you sure you want to delete this user?")) {
                    e.preventDefault();
                }
            });
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
            // Excel Export
        {
            extend: 'excelHtml5',
            className: 'button-excel',
            title: 'User Management Report', // Report title
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
                                <h2 style="margin: 0; font-size: 18px; color: #2d3748; text-transform: uppercase;">User Management Report</h2>
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
                    { text: 'User Management Report', style: 'reportTitle', alignment: 'right' },
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
            fontSize: 11,
            color: '#000',
            alignment: 'center'
        };

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
            '30%', // Email
            '20%', // Contact No
            '30%', // Full name
            '20%', // Role
        ];
    }
doc.pageMargins = [35, 70, 40, 50]; // [left, top, right, bottom]

    }
}
        ],
        columnDefs: [
            { width: "250px", targets: 0 }, // Email
            { width: "150px", targets: 1 }, // Contact No
            { width: "200px", targets: 2 }, // Full name
            { width: "110px", targets: 3 }, // Role
            { width: "90px", targets: 4 }  // Actions
        ],
        scrollX: true,        // enable horizontal scroll if needed
        autoWidth: false,      // important: lets columnDefs widths take effect
        order: [[3, 'asc']], // sort by Created column descending
        language: {
            emptyTable: "No users found" // <-- put this here, no second initialization
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