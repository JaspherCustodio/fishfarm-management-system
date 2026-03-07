<?php

session_start();
if (
    !isset($_SESSION['email']) ||
    !isset($_SESSION['role']) ||
    !isset($_SESSION['id'])
) {
    header("Location: ../index.php");
    exit();
}

$isAdmin = $_SESSION['role'] === 'admin';


include "../auth/config.php";



// Upcoming schedules in the next 7 days
$userId = (int)$_SESSION['id'];

// Past Due Tasks (for dashboard)
$dueThisWeek = 0;

$tables = [
    'fish_cage_management' => 'schedule_id',
    'stocking'             => 'schedule_id',
    'transfers'            => 'schedule_id',
    'feedings'             => 'schedule_id',
    'samplings'            => 'schedule_id',
    'net_cleaning'         => 'schedule_id',
    'net_checking'         => 'schedule_id',
    'net_repairing'        => 'schedule_id',
];

// Build query for UNION ALL
$queries = [];

foreach ($tables as $table => $scheduleField) {
    $q = "SELECT COUNT(*) AS total
          FROM $table t
          JOIN schedules s ON s.id = t.$scheduleField
          WHERE t.status != 'Completed'
            AND s.schedule_datetime < CURDATE()";

    if (!$isAdmin) {
        $q .= " AND s.assigned_to = $userId";
    }

    $queries[] = $q;
}

// Deliveries table has delivery_date instead of schedule_datetime
$deliveriesQuery = "SELECT COUNT(*) AS total
                    FROM deliveries d
                    WHERE d.status != 'Completed'
                      AND d.delivery_date < CURDATE()";
if (!$isAdmin) $deliveriesQuery .= " AND d.assigned_to = $userId";

$queries[] = $deliveriesQuery;

// Combine all queries using UNION ALL
$unionQuery = implode(" UNION ALL ", $queries);

$finalQuery = "SELECT SUM(total) AS total_due FROM ($unionQuery) AS all_tasks";
$result = $conn->query($finalQuery);
$dueThisWeek = $result ? (int)$result->fetch_assoc()['total_due'] : 0;

// Total schedules in the last 30 days
$schedules30daysQuery = "
    SELECT COUNT(*) AS total
    FROM (
        SELECT s.id
        FROM fish_cage_management fcm
        LEFT JOIN schedules s ON s.id = fcm.schedule_id
        WHERE fcm.status != 'completed' 
          AND s.schedule_datetime >= CURDATE() - INTERVAL 30 DAY

        UNION ALL

        SELECT s.id
        FROM stocking st
        LEFT JOIN schedules s ON s.id = st.schedule_id
        WHERE st.status != 'completed' 
          AND s.schedule_datetime >= CURDATE() - INTERVAL 30 DAY

        UNION ALL

        SELECT s.id
        FROM transfers tr
        LEFT JOIN schedules s ON s.id = tr.schedule_id
        WHERE tr.status != 'completed' 
          AND s.schedule_datetime >= CURDATE() - INTERVAL 30 DAY

        UNION ALL

        SELECT dl.id
        FROM deliveries dl
        WHERE dl.status != 'completed' 
          AND dl.delivery_date >= CURDATE() - INTERVAL 30 DAY

        UNION ALL

        SELECT s.id
        FROM feedings fd
        LEFT JOIN schedules s ON s.id = fd.schedule_id
        WHERE fd.status != 'completed' 
          AND s.schedule_datetime >= CURDATE() - INTERVAL 30 DAY

        UNION ALL

        SELECT s.id
        FROM samplings sm
        LEFT JOIN schedules s ON s.id = sm.schedule_id
        WHERE sm.status != 'completed' 
          AND s.schedule_datetime >= CURDATE() - INTERVAL 30 DAY

        UNION ALL

        SELECT s.id
        FROM net_cleaning nc
        LEFT JOIN schedules s ON s.id = nc.schedule_id
        WHERE nc.status != 'completed' 
          AND s.schedule_datetime >= CURDATE() - INTERVAL 30 DAY

        UNION ALL

        SELECT s.id
        FROM net_checking nck
        LEFT JOIN schedules s ON s.id = nck.schedule_id
        WHERE nck.status != 'completed' 
          AND s.schedule_datetime >= CURDATE() - INTERVAL 30 DAY

        UNION ALL

        SELECT s.id
        FROM net_repairing nr
        LEFT JOIN schedules s ON s.id = nr.schedule_id
        WHERE nr.status != 'completed' 
          AND s.schedule_datetime >= CURDATE() - INTERVAL 30 DAY
    ) AS all_tasks
";

// Non-admins only see their tasks
if (!$isAdmin) {
    $schedules30daysQuery = "
        SELECT COUNT(*) AS total
        FROM (
            SELECT s.id
            FROM fish_cage_management fcm
            LEFT JOIN schedules s ON s.id = fcm.schedule_id
            WHERE fcm.status != 'completed' 
              AND s.schedule_datetime >= CURDATE() - INTERVAL 30 DAY
              AND s.assigned_to = $userId

            UNION ALL

            SELECT s.id
            FROM stocking st
            LEFT JOIN schedules s ON s.id = st.schedule_id
            WHERE st.status != 'completed' 
              AND s.schedule_datetime >= CURDATE() - INTERVAL 30 DAY
              AND s.assigned_to = $userId

            UNION ALL

            SELECT s.id
            FROM transfers tr
            LEFT JOIN schedules s ON s.id = tr.schedule_id
            WHERE tr.status != 'completed' 
              AND s.schedule_datetime >= CURDATE() - INTERVAL 30 DAY
              AND s.assigned_to = $userId

            UNION ALL

            SELECT dl.id
            FROM deliveries dl
            WHERE dl.status != 'completed' 
              AND dl.delivery_date >= CURDATE() - INTERVAL 30 DAY
              AND dl.assigned_to = $userId

            UNION ALL

            SELECT s.id
            FROM feedings fd
            LEFT JOIN schedules s ON s.id = fd.schedule_id
            WHERE fd.status != 'completed' 
              AND s.schedule_datetime >= CURDATE() - INTERVAL 30 DAY
              AND s.assigned_to = $userId

            UNION ALL

            SELECT s.id
            FROM samplings sm
            LEFT JOIN schedules s ON s.id = sm.schedule_id
            WHERE sm.status != 'completed' 
              AND s.schedule_datetime >= CURDATE() - INTERVAL 30 DAY
              AND s.assigned_to = $userId

            UNION ALL

            SELECT s.id
            FROM net_cleaning nc
            LEFT JOIN schedules s ON s.id = nc.schedule_id
            WHERE nc.status != 'completed' 
              AND s.schedule_datetime >= CURDATE() - INTERVAL 30 DAY
              AND s.assigned_to = $userId

            UNION ALL

            SELECT s.id
            FROM net_checking nck
            LEFT JOIN schedules s ON s.id = nck.schedule_id
            WHERE nck.status != 'completed' 
              AND s.schedule_datetime >= CURDATE() - INTERVAL 30 DAY
              AND s.assigned_to = $userId

            UNION ALL

            SELECT s.id
            FROM net_repairing nr
            LEFT JOIN schedules s ON s.id = nr.schedule_id
            WHERE nr.status != 'completed' 
              AND s.schedule_datetime >= CURDATE() - INTERVAL 30 DAY
              AND s.assigned_to = $userId
        ) AS all_tasks
    ";
}

$totalSchedules = $conn->query($schedules30daysQuery)->fetch_assoc()['total'] ?? 0;



// Total cages
$totalCages = $conn->query("
    SELECT COUNT(*) AS total FROM fish_cages
")->fetch_assoc()['total'];

// Total employees (users with role = user)
$totalEmployees = $conn->query("
    SELECT COUNT(*) AS total 
    FROM users
    WHERE role = 'user'
")->fetch_assoc()['total'];

$assignedFilter = "";

if (!$isAdmin) {
    $assignedFilter = "JOIN schedules s ON s.id = main.schedule_id 
                       WHERE s.assigned_to = $userId";
} else {
    $assignedFilter = "";
}


$taskTables = [
    'fish_cage_management',
    'samplings',
    'feedings',
    'stocking',
    'transfers',
    'net_cleaning',
    'net_checking',
    'net_repairing',
    'deliveries'
];

$queries = [];
foreach ($taskTables as $table) {
    if ($table === 'deliveries') {
        $q = "SELECT LOWER(status) AS status FROM deliveries";
        if (!$isAdmin) $q .= " WHERE assigned_to = $userId";
    } else {
        $q = "SELECT LOWER(status) AS status FROM $table";
        if (!$isAdmin) $q .= " JOIN schedules s ON s.id = $table.schedule_id AND s.assigned_to = $userId";
    }
    $queries[] = $q;
}

$statusCountsQuery = "SELECT status, COUNT(*) AS total FROM (".implode(" UNION ALL ", $queries).") AS all_tasks GROUP BY status";

$statusCountsResult = $conn->query($statusCountsQuery);

$statusCounts = [];
if ($statusCountsResult) {
    $statusCounts = $statusCountsResult->fetch_all(MYSQLI_ASSOC);
}

$pendingTasks   = $ongoingTasks = $completedTasks = $cancelledTasks = 0;

foreach ($statusCounts as $row) {
    $status = ucfirst(strtolower($row['status'])); // normalize
    switch ($status) {
        case 'Pending':   $pendingTasks   = (int)$row['total']; break;
        case 'Ongoing':   $ongoingTasks   = (int)$row['total']; break;
        case 'Completed': $completedTasks = (int)$row['total']; break;
        case 'Cancelled': $cancelledTasks = (int)$row['total']; break;
    }
}


// Recent cage management
$recentCageManagement = $conn->query("
    SELECT 
        fcm.date,
        fcm.result,
        fcm.status,
        fc.cage_name
    FROM fish_cage_management fcm
    JOIN schedules s ON s.id = fcm.schedule_id
    JOIN fish_cages fc ON fc.id = s.fish_cage
    ORDER BY fcm.id DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);


$cageTransferHealth = $conn->query("
    SELECT
        fc.cage_name,
        SUM(t.mortality) AS total_mortality,
        SUM(t.quantity_before - t.quantity_after) AS total_loss
    FROM transfers t
    JOIN fish_cages fc ON fc.id = t.from_cage
    GROUP BY fc.id
")->fetch_all(MYSQLI_ASSOC);

function cageHealthPercent($mortality, $loss, $startingQuantity = 100) {
    $mortalityRate = ($mortality / max($startingQuantity, 1)) * 100;
    $lossRate = ($loss / max($startingQuantity, 1)) * 100; // loss as %

    $score = 100;

    $score -= ($mortalityRate * 0.5);  // mortality penalty
    $score -= ($lossRate * 0.5);       // loss penalty as percentage

    return max(0, min(100, round($score, 1)));
}

	$growthData = $conn->query("
		SELECT
			fc.id AS cage_id,
			fc.cage_name,
			sp.sampling_date,
			sp.avg_weight
		FROM samplings sp
		JOIN schedules s ON s.id = sp.schedule_id
		JOIN fish_cages fc ON fc.id = s.fish_cage
		ORDER BY fc.id, sp.sampling_date
	")->fetch_all(MYSQLI_ASSOC);

	$chartData = [];

	foreach ($growthData as $row) {
		$cage = $row['cage_name'];

		if (!isset($chartData[$cage])) {
			$chartData[$cage] = [
				'dates' => [],
				'weights' => []
			];
		}

		$chartData[$cage]['dates'][]   = $row['sampling_date'];
		$chartData[$cage]['weights'][] = (float)$row['avg_weight'];
	}

$yearlyExpenses = $conn->query("
    SELECT 
        DATE_FORMAT(expense_date, '%Y-%m') AS month,
        SUM(amount) AS total
    FROM expenses
    GROUP BY month
    ORDER BY month
")->fetch_all(MYSQLI_ASSOC);

$categoryExpenses = $conn->query("
    SELECT 
        category,
        SUM(amount) AS total
    FROM expenses
    GROUP BY category
")->fetch_all(MYSQLI_ASSOC);

$monthlyLabels = [];
$monthlyTotals = [];

foreach ($yearlyExpenses as $row) {
    $monthlyLabels[] = $row['month'];
    $monthlyTotals[] = (float)$row['total'];
}

$categoryLabels = [];
$categoryTotals = [];

foreach ($categoryExpenses as $row) {
    $categoryLabels[] = $row['category'];
    $categoryTotals[] = (float)$row['total'];
}


// Current month & year
$currentMonth = date('m');
$currentYear  = date('Y');

$salesQuery = "
    SELECT SUM(sale_amount) AS total_sales
    FROM deliveries
    WHERE status = 'Completed'
      AND MONTH(delivery_date) = '$currentMonth'
      AND YEAR(delivery_date) = '$currentYear'
";

$salesResult = $conn->query($salesQuery);
$totalSales  = $salesResult->fetch_assoc()['total_sales'] ?? 0;

$expenseQuery = "
    SELECT SUM(amount) AS total_expenses
    FROM expenses
    WHERE MONTH(expense_date) = '$currentMonth'
      AND YEAR(expense_date) = '$currentYear'
";

$expenseResult = $conn->query($expenseQuery);
$totalExpenses = $expenseResult->fetch_assoc()['total_expenses'] ?? 0;

$netProfit = $totalSales - $totalExpenses;

$roi = 0;
if ($totalExpenses > 0) {
    $roi = ($netProfit / $totalExpenses) * 100;
}

// 🐟 Total Fish Inventory (Remaining from Completed Stocking)
$totalInventory = $conn->query("
    SELECT SUM(current_quantity) AS total_inventory
    FROM stocking
    WHERE status = 'Completed'
")->fetch_assoc()['total_inventory'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" type="image/png" href="../assets/img/dpa-logo.png">
	<title>Dashboard</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

	<link rel="stylesheet" href="../assets/style.css">
</head>
<body class="dashboard-page">

	<?php include "../inc/header.php" ?>

	<div class="body">
		
		<?php include "../inc/nav.php" ?>
		<div class="sidebar-overlay" onclick="closeSidebar()"></div>
		<section class="section-1">
			<div class="content-header">
                <h4>Dashboard</h4>
                <button class="menu-toggle" onclick="openSidebar()">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

			<?php if ($isAdmin): ?>
			<div class="dashboard-cards main">
				<!-- Fish Cages -->
				<a href="fish_cage.php" class="dash-card">
					<i class="fa fa-cubes"></i>
					<div class="card-text">
						<h3><?= $totalCages ?></h3>
						<p>Fish Cages</p>
					</div>
				</a>

				<!-- Employees -->
				<a href="user_management.php" class="dash-card">
					<i class="fa fa-user"></i>
					<div class="card-text">
						<h3><?= $totalEmployees ?></h3>
						<p>Employees</p>
					</div>
				</a>

				<!-- Schedules -->
				<a href="schedules.php?filter=last30days" class="dash-card">
					
					<i class="fa-regular fa-calendar"></i>
					<div class="card-text">
						<h3><?= $totalSchedules ?></h3>
						<p>Schedules (30 days)</p>
					</div>
				</a>
			</div>

		<?php endif; ?>

		<div class="dashboard-cards">
			<!-- Pending Tasks -->
			<a href="tasks.php?status=Pending" class="dash-card pending">
				<i class="fa fa-clock"></i>
				<div class="card-text">
					<h3><?= $pendingTasks ?></h3>
					<p>Pending Tasks</p>
				</div>
			</a>

			<!-- Ongoing Tasks -->
			<a href="tasks.php?status=Ongoing" class="dash-card ongoing">
				<i class="fa fa-spinner"></i>
				<div class="card-text">
					<h3><?= $ongoingTasks ?></h3>
					<p>Ongoing Tasks</p>
				</div>
			</a>

			<!-- Completed Tasks -->
			<a href="tasks.php?status=Completed" class="dash-card completed">
				<i class="fa fa-check-circle"></i>
				<div class="card-text">
					<h3><?= $completedTasks ?></h3>
					<p>Completed Tasks</p>
				</div>
			</a>

			<!-- Upcoming Schedules -->
			<a href="schedules.php?filter=due_this_week" class="dash-card past-due">
				<i class="fa fa-calendar-day"></i>
				<div class="card-text">
					<h3><?= $dueThisWeek ?></h3>
					<p>Past Due Tasks</p>
				</div>
			</a>

			<!-- Cancelled Tasks -->
			<a href="tasks.php?status=Cancelled" class="dash-card cancelled">
				<i class="fa fa-times-circle"></i>
				<div class="card-text">
					<h3><?= $cancelledTasks ?></h3>
					<p>Cancelled Tasks</p>
				</div>
			</a>
		</div>
		

<?php if ($isAdmin): ?>
<div class="dashboard-cards main">
	<div class="dash-card inventory">
		<i class="fa-solid fa-fish"></i>

		<div class="card-text">
			<h3><?= number_format($totalInventory) ?></h3>
		</div>

		<p>Total Fish Inventory</p>
	</div>

    <div class="dash-card sales">
        <i class="fa-solid fa-money-bill-trend-up"></i>
        <div class="card-text">
            <h3>₱<?= number_format($totalSales, 2) ?></h3>
        </div>
		<p>This Month Sales</p>
    </div>

    <div class="dash-card expense">
        <i class="fa-solid fa-file-invoice-dollar"></i>
        <div class="card-text">
            <h3>₱<?= number_format($totalExpenses, 2) ?></h3>
        </div>
		<p>This Month Expenses</p>
    </div>

    <div class="dash-card <?= $netProfit < 0 ? 'negative' : 'profit' ?>">
        <i class="fa-solid fa-coins"></i>
        <div class="card-text">
            <h3>₱<?= number_format($netProfit, 2) ?></h3>
        </div>
		<p>This Month Net Profit</p>
    </div>

    <div class="dash-card roi">
        <i class="fa-solid fa-percent"></i>
        <div class="card-text">
            <h3><?= number_format($roi, 2) ?>%</h3>
        </div>
		<p>This Month ROI</p>
    </div>

</div>
<?php endif; ?>


		<?php if ($isAdmin): ?>
		<div class="content-card">
			<div class="card-header dash-report">
				<i class="fa-solid fa-chart-line"></i>
				<h3 class="title">Full Expense Report</h3>
			</div>

			<div style="display:grid; grid-template-columns:1fr 1fr; gap:30px;">
				
				<div class="chart-card">
					<h4>Yearly Expenses</h4>
					<canvas id="monthlyChart" style="height:300px;"></canvas>
				</div>

				<div class="chart-card">
					<h4>Expense by Category</h4>
					<canvas id="categoryChart" style="height:300px;"></canvas>
				</div>

			</div>
		</div>
		<?php endif; ?>


			<div class="content-card">
				<div class="card-header dash-report">
					<i class="fa-solid fa-fish"></i>
					<h3 class="title">Fish Growth (Average Weight)</h3>
				</div>

				<?php $i = 0; foreach ($chartData as $cageName => $data): ?>
					<div class="chart-card" style="margin-bottom:40px;">
						<h4><?= htmlspecialchars($cageName) ?></h4>
						<canvas id="chart-<?= $i ?>" style="height:300px;"></canvas>
					</div>
				<?php $i++; endforeach; ?>

			</div>

			
			<?php if ($isAdmin): ?>
			<div class="content-card">
				<div class="card-header dash-report">
					<i class="fa-regular fa-calendar-days"></i>
					<h3 class="title">Cage Transfer Health Overview</h3>
				</div>

				<div class="cage-health-grid">
					<?php foreach ($cageTransferHealth as $cage):
    $startingQty = 1000; // you can fetch real starting quantity per cage
    $mortalityPercent = ($cage['total_mortality'] / max($startingQty, 1)) * 100;
    $lossTotal = $cage['total_loss'];

    $health = cageHealthPercent($cage['total_mortality'], $lossTotal, $startingQty);
    $color = $health >= 70 ? '#2ecc71' : ($health >= 40 ? '#f39c12' : '#e74c3c');
?>

<div class="health-card">
    <h4><?= htmlspecialchars($cage['cage_name']) ?></h4>
	<p class="health-info">
        Mortality Rate:
	</p>
	
<div class="circle" style="background: conic-gradient(<?= $color ?> <?= $health ?>%, #e0e0e0 <?= $health ?>%);">
        <span><?= $health ?>%</span>
    </div>
    <p class="health-info">
        Loss: <?= (int)$lossTotal ?>
    </p>
</div>
<?php endforeach; ?>
				</div>
			</div>
			
			<?php endif; ?>

			<?php include "../inc/footer.php" ?>

		</section>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<script>

		const chartData = <?= json_encode($chartData) ?>;

		let index = 0;

		Object.values(chartData).forEach(data => {
			const ctx = document.getElementById('chart-' + index);
			if (!ctx) return;

			new Chart(ctx, {
				type: 'line',
				data: {
					labels: data.dates,
					datasets: [{
						label: 'Average Weight',
						data: data.weights,
						borderColor: '#1e90ff',
						backgroundColor: 'rgba(30,144,255,0.25)',
						borderWidth: 3,
						tension: 0.4,
						fill: true,
						pointRadius: 5
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					scales: {
						x: {
							ticks: { color: '#333' },
							grid: { color: '#e0e0e0' }
						},
						y: {
							beginAtZero: true,
							ticks: { color: '#333' },
							grid: { color: '#e0e0e0' }
						}
					},
					plugins: {
						legend: {
							labels: { color: '#333' }
						}
					}
				}

			});

			index++;
		});

		const monthlyLabels = <?= json_encode($monthlyLabels) ?>;
		const monthlyTotals = <?= json_encode($monthlyTotals) ?>;

		const categoryLabels = <?= json_encode($categoryLabels) ?>;
		const categoryTotals = <?= json_encode($categoryTotals) ?>;

		// 📈 Monthly Line Chart
		new Chart(document.getElementById('monthlyChart'), {
			type: 'line',
			data: {
				labels: monthlyLabels,
				datasets: [{
					label: 'Monthly Expenses',
					data: monthlyTotals,
					borderColor: '#1e90ff',
					backgroundColor: 'rgba(30,144,255,0.2)',
					tension: 0.4,
					fill: true,
					borderWidth: 3
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false
			}
		});

		// 📊 Category Bar Chart
		function generateColors(count) {
			const colors = [];
			for (let i = 0; i < count; i++) {
				const hue = Math.floor((360 / count) * i);
				colors.push(`hsl(${hue}, 70%, 55%)`);
			}
			return colors;
		}

		const categoryColors = generateColors(categoryLabels.length);

		new Chart(document.getElementById('categoryChart'), {
			type: 'bar',
			data: {
				labels: categoryLabels,
				datasets: [{
					label: 'Expenses by Category',
					data: categoryTotals,
					backgroundColor: categoryColors
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false
			}
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