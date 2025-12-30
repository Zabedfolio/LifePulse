<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include '../db_connect.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Dashboard statistics
$total_donors = $conn->query("SELECT COUNT(*) as total FROM donors")->fetch_assoc()['total'];
$total_requests = $conn->query("SELECT COUNT(*) as total FROM blood_requests")->fetch_assoc()['total'];
$total_blood_given = $conn->query("SELECT SUM(units_needed) as total FROM blood_requests WHERE status='completed'")->fetch_assoc()['total'] ?? 0;
$pending_requests = $conn->query("SELECT COUNT(*) as total FROM blood_requests WHERE status='pending'")->fetch_assoc()['total'];

// Blood stock
$blood_stock_result = $conn->query("SELECT blood_group, units_available FROM blood_stock");
$blood_stock = [];
while ($row = $blood_stock_result->fetch_assoc()) {
    $blood_stock[$row['blood_group']] = $row['units_available'];
}

// Donor distribution by place
$places_result = $conn->query("SELECT address AS place, COUNT(*) AS total FROM donors GROUP BY address ORDER BY total DESC");
$places = $place_counts = [];
while ($row = $places_result->fetch_assoc()) {
    $places[] = $row['place'];
    $place_counts[] = $row['total'];
}

// Blood Given Data for Chart
$current_year = date('Y');
$monthly_data = $conn->query("
    SELECT MONTH(request_date) as month, SUM(units_needed) as total
    FROM blood_requests
    WHERE status='completed' AND YEAR(request_date) = $current_year
    GROUP BY MONTH(request_date)
")->fetch_all(MYSQLI_ASSOC);

$monthly_units = array_fill(1, 12, 0);
foreach ($monthly_data as $row) {
    $monthly_units[(int)$row['month']] = (int)$row['total'];
}

// Yearly Blood Given (all years)
$yearly_data = $conn->query("
    SELECT YEAR(request_date) as year, SUM(units_needed) as total
    FROM blood_requests
    WHERE status='completed'
    GROUP BY YEAR(request_date)
    ORDER BY year ASC
")->fetch_all(MYSQLI_ASSOC);

$years = [];
$yearly_units = [];
foreach ($yearly_data as $row) {
    $years[] = $row['year'];
    $yearly_units[] = (int)$row['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>LifePulse Admin Dashboard</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* ===== ROOT & GLOBAL ===== */
:root {
    --accent: #d32f2f;
    --sidebar-bg: #2c2c2c;
    --card-bg: #ffffff;
    --muted: #6a6a6a;
    --radius: 10px;
}

body {
    margin: 0;
    font-family: 'Inter', sans-serif;
    background: #f4f4f4;
}

/* ===== LAYOUT ===== */
.layout {
    display: flex;
    min-height: 100vh;
}

/* ===== SIDEBAR ===== */
.sidebar {
    width: 220px;
    background: var(--sidebar-bg);
    color: white;
    padding: 24px 16px;
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
}
.sidebar h2 {
    margin: 0 0 30px 0;
    font-size: 22px;
    letter-spacing: 1px;
}
.sidebar ul {
    list-style: none;
    padding: 0;
    margin: 0;
    flex: 1;
}
.sidebar ul li {
    margin: 14px 0;
}
.sidebar ul li a {
    color: white;
    text-decoration: none;
    padding: 10px 14px;
    display: block;
    border-radius: var(--radius);
    transition: all 0.3s ease;
    font-weight: 500;
}
.sidebar ul li a:hover {
    background: rgba(255,255,255,0.1);
}

/* ===== MAIN CONTENT ===== */
.main-content {
    flex: 1;
    margin-left: 250px;
    padding: 30px 40px;
}

/* ===== HEADER ===== */
.main-content h1 {
    font-size: 28px;
    margin-bottom: 25px;
}

/* ===== CARDS ===== */
.cards {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 30px;
}
.card {
    flex: 1 1 250px;
    color: white;
    padding: 20px;
    border-radius: var(--radius);
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    position: relative;
    overflow: hidden;
}
.card h3 {
    margin: 0;
    font-size: 14px;
    color: rgba(255,255,255,0.9);
}
.card p {
    font-size: 28px;
    font-weight: bold;
    text-align: center;
    margin-top: 10px;
}

/* Gradient backgrounds for each card */
.card.total-donors { background: linear-gradient(to top, #ff6b6b, #f44336); }
.card.total-requests { background: linear-gradient(to top, #42a5f5, #1e88e5); }
.card.total-blood-given { background: linear-gradient(to top, #66bb6a, #43a047); }
.card.pending-requests { background: linear-gradient(to top, #ffca28, #ffa000); }

/* ===== ROW 2 COLUMN ===== */
.row-2col {
    display: flex;
    gap: 20px;
    margin-bottom: 40px;
    flex-wrap: wrap;
}

/* Chart box (reduced height) */
.chart-box {
    flex: 1.3;
    background: var(--card-bg);
    padding: 20px;
    border-radius: var(--radius);
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    min-height: auto; /* decreased from 380px */
}

/* Table next to chart (reduced height) */
.modern-table {
    flex: 0.8;
    width: 100%;
    background: var(--card-bg);
    border-collapse: collapse;
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    max-height: auto; /* decreased */
}
.modern-table th, .modern-table td {
    padding: 12px 15px;
    text-align: center;
}
.modern-table th {
    background: #fafafa;
}
.modern-table td {
    border-top: 1px solid #eee;
}

/* ===== Filter Buttons ===== */
.filter-btn {
    background: #f4f4f4;
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 8px 16px;
    margin-right: 10px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
    color: #333;
}
.filter-btn.active {
    background: #d32f2f;
    border-color: #d32f2f;
    color: white;
}
.filter-btn:hover {
    background: #e53935;
    color: white;
    border-color: #d32f2f;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 900px) {
    .row-2col {
        flex-direction: column;
    }
    .main-content {
        margin-left: 0;
        padding: 20px;
    }
    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }
}
</style>
</head>

<body>

<div class="layout">

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>LifePulse</h2>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="manage_donors.php">Manage Donors</a></li>
            <li><a href="manage_requests.php">Manage Requests</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1>Dashboard</h1>

        <!-- Cards -->
        <div class="cards">
            <div class="card total-donors">
                <h3>Total Donors</h3>
                <p><?= $total_donors ?></p>
            </div>
            <div class="card total-requests">
                <h3>Total Blood Requests</h3>
                <p><?= $total_requests ?></p>
            </div>
            <div class="card total-blood-given">
                <h3>Total Blood Given</h3>
                <p><?= $total_blood_given ?></p>
            </div>
            <div class="card pending-requests">
                <h3>Pending Requests</h3>
                <p><?= $pending_requests ?></p>
            </div>
        </div>

        <!-- Blood Stock -->
        <h2>Blood Stock</h2>
        <div class="row-2col">
            <div class="chart-box">
                <canvas id="bloodStockChart"></canvas>
            </div>
            <table class="modern-table">
                <thead>
                    <tr><th>Group</th><th>Units</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($blood_stock as $group=>$units): ?>
                        <tr><td><?= $group ?></td><td><?= $units ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Donor Distribution -->
        <h2>Donor Distribution by Place</h2>
        <div class="row-2col">
            <div class="chart-box">
                <canvas id="placePieChart"></canvas>
            </div>
            <table class="modern-table">
                <thead>
                    <tr><th>Place</th><th>Total</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($places as $i=>$place): ?>
                        <tr><td><?= $place ?></td><td><?= $place_counts[$i] ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Blood Given Chart with Button Filter -->
        <h2>Blood Given</h2>
        <div class="chart-filter-buttons" style="margin-bottom: 20px;">
            <button id="monthlyBtn" class="filter-btn active" onclick="updateChart('monthly')">Monthly</button>
            <button id="yearlyBtn" class="filter-btn" onclick="updateChart('yearly')">Yearly</button>
        </div>
        <div class="chart-box">
            <canvas id="bloodGivenChart"></canvas>
        </div>

    </div>
</div>

<script>
// Blood Stock Chart
const bloodCtx = document.getElementById('bloodStockChart').getContext('2d');
new Chart(bloodCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($blood_stock)) ?>,
        datasets: [{
            label: 'Units',
            data: <?= json_encode(array_values($blood_stock)) ?>,
            backgroundColor: '#d32f2f'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

// Generate random colors for Pie chart
function getRandomColors(count) {
    const colors = [];
    for(let i = 0; i < count; i++) {
        colors.push('#' + Math.floor(Math.random()*16777215).toString(16).padStart(6,'0'));
    }
    return colors;
}

const randomColors = getRandomColors(<?= count($places) ?>);


// Place Pie Chart
const placeCtx = document.getElementById('placePieChart').getContext('2d');
new Chart(placeCtx, {
    type: 'pie',
    data: {
        labels: <?= json_encode($places) ?>,
        datasets: [{
            label: 'Donors',
            data: <?= json_encode($place_counts) ?>,
            backgroundColor: randomColors
        }]
    },
    options: { responsive: true }
});

// Blood Given Line Chart
let ctx = document.getElementById('bloodGivenChart').getContext('2d');

// Monthly data
const monthlyLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const monthlyData = <?= json_encode(array_values($monthly_units)) ?>;

// Yearly data: 2024-2030
const allYears = [2024,2025,2026,2027,2028,2029,2030];
const yearlyDataFromDB = <?= json_encode(array_combine($years, $yearly_units)) ?>;

// Fill yearly data, default to 0 if no data
const yearlyLabels = allYears;
const yearlyData = allYears.map(y => yearlyDataFromDB[y] ?? 0);


let bloodGivenChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: monthlyLabels,
        datasets: [{
            label: 'Units Given',
            data: monthlyData,
            fill: true,
            backgroundColor: 'rgba(102,187,106,0.2)',
            borderColor: 'rgba(102,187,106,1)',
            tension: 0.3,
            pointBackgroundColor: 'rgba(102,187,106,1)',
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

function updateChart(filter) {
    document.getElementById('monthlyBtn').classList.remove('active');
    document.getElementById('yearlyBtn').classList.remove('active');
    if(filter === 'monthly') {
        document.getElementById('monthlyBtn').classList.add('active');
        bloodGivenChart.data.labels = monthlyLabels;
        bloodGivenChart.data.datasets[0].data = monthlyData;
        bloodGivenChart.data.datasets[0].backgroundColor = 'rgba(102,187,106,0.2)';
        bloodGivenChart.data.datasets[0].borderColor = 'rgba(102,187,106,1)';
    } else {
        document.getElementById('yearlyBtn').classList.add('active');
        bloodGivenChart.data.labels = yearlyLabels;
        bloodGivenChart.data.datasets[0].data = yearlyData;
        bloodGivenChart.data.datasets[0].backgroundColor = 'rgba(66,165,245,0.2)';
        bloodGivenChart.data.datasets[0].borderColor = 'rgba(66,165,245,1)';
    }
    bloodGivenChart.update();
}

// Initialize as monthly
updateChart('monthly');
</script>

</body>
</html>
