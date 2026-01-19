<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../db_connect.php';
include '../requests/request_functions.php'; // Assuming request_functions.php exists with createRequest function

$message = '';

// Handle Add Request
if (isset($_POST['add_request'])) {
    $data = [
        'patient_name' => trim($_POST['patient_name'] ?? ''),
        'blood_group' => trim($_POST['blood_group'] ?? ''),
        'units_needed' => intval($_POST['units_needed'] ?? 0),
        'hospital' => trim($_POST['hospital'] ?? ''),
        'contact' => trim($_POST['contact'] ?? ''),
        'status' => trim($_POST['status'] ?? 'pending')
    ];
    if ($data['patient_name'] && $data['blood_group'] && $data['units_needed'] > 0) {
        $res = createRequest($conn, $data);
        if ($res['ok']) {
            $message = 'Request added successfully!';
        } else {
            $message = 'Error: ' . ($res['error'] ?? 'Unknown');
        }
    } else {
        $message = 'Missing or invalid required fields';
    }
}

// Delete Request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM blood_requests WHERE request_id = $delete_id");
    header("Location: manage_requests.php?deleted=1");
    exit;
}

// Fetch Requests
$requests = mysqli_query($conn, "SELECT * FROM blood_requests ORDER BY request_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Requests - LifePulse</title>

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
.sidebar h2 { margin: 0 0 30px 0; font-size: 22px; letter-spacing: 1px; }
.sidebar ul { list-style: none; padding: 0; margin: 0; flex: 1; }
.sidebar ul li { margin: 14px 0; }
.sidebar ul li a { color: white; text-decoration: none; padding: 10px 14px; display: block; border-radius: var(--radius); transition: all 0.3s ease; font-weight: 500; }
.sidebar ul li a:hover { background: rgba(255,255,255,0.1); }

/* ===== MAIN CONTENT ===== */
.main-content {
    flex: 1;
    margin-left: 250px;
    padding: 40px 30px;
}

/* ===== HEADERS ===== */
h1 {
    font-size: 28px;
    margin: 0 0 30px 0;
    color: #333;
}

/* ===== FORM CARD ===== */
.form-card {
    background: var(--card-bg);
    padding: 30px;
    padding-right: 55px;
    border-radius: var(--radius);
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 30px;
    max-width: 600px;
}
.form-card h3 { margin-top: 0; color: var(--accent); }
.form-card input, .form-card select { width: 100%; padding: 10px; margin-bottom: 10px; margin-top: 5px; border-radius: var(--radius); border: 1px solid #ccc; }
.form-card button {
    padding: 12px 20px;
    background: var(--accent);
    color: white;
    border: none;
    border-radius: var(--radius);
    cursor: pointer;
    font-size: 16px;
}
.form-card button:hover { background: #b71c1c; }

/* ===== MESSAGES ===== */
.message {
    padding: 12px;
    margin-bottom: 20px;
    border-radius: var(--radius);
    font-weight: 500;
}
.success { background: #e8f5e9; color: #2e7d32; }
.error { background: #ffebee; color: #c62828; }

/* ===== TABLE ===== */
.table-wrapper {
    background: var(--card-bg);
    border-radius: var(--radius);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}
.table-wrapper table {
    width: 100%;
    border-collapse: collapse;
}
.table-wrapper th,
.table-wrapper td {
    padding: 14px 16px;
    text-align: left;
    border-bottom: 1px solid #eee;
}
.table-wrapper th {
    background: #fafafa;
    font-weight: 600;
    color: #444;
}
.table-wrapper td strong {
    color: var(--accent);
}
.table-wrapper .delete-btn {
    background: var(--accent);
    color: white;
    padding: 6px 12px;
    border-radius: var(--radius);
    text-decoration: none;
    font-size: 13px;
    transition: all 0.3s ease;
}
.table-wrapper .delete-btn:hover {
    background: #b71c1c;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 900px) {
    .main-content {
        margin-left: 0;
        padding: 20px;
    }
    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }
    .form-card form {
        grid-template-columns: 1fr;
    }
    .form-card button {
        grid-column: span 1;
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
        <h1>Manage Requests</h1>

        <?php if ($message): ?>
            <div class="message <?= strpos($message, '✅') === 0 ? 'success' : 'error'; ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="message success">✅ Request deleted successfully.</div>
        <?php endif; ?>

        <!-- Add Request Form -->
        <div class="form-card">
            <form method="POST">
                <div>
                    <label>Patient Name *</label>
                    <input type="text" name="patient_name" required>
                </div>
                <div>
                    <label>Blood Group *</label>
                    <select name="blood_group" required>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                </div>
                <div>
                    <label>Units Needed *</label>
                    <input type="number" name="units_needed" min="1" required>
                </div>
                <div>
                    <label>Hospital</label>
                    <input type="text" name="hospital">
                </div>
                <div>
                    <label>Contact</label>
                    <input type="text" name="contact">
                </div>
                <div>
                    <label>Status</label>
                    <select name="status">
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <button type="submit" name="add_request">Add Request</button>
            </form>
        </div>

        <!-- Requests Table -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Patient Name</th>
                        <th>Blood Group</th>
                        <th>Units Needed</th>
                        <th>Hospital</th>
                        <th>Request Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($requests)): ?>
                    <tr>
                        <td><?= $row['request_id']; ?></td>
                        <td><?= htmlspecialchars($row['patient_name']); ?></td>
                        <td><strong><?= htmlspecialchars($row['blood_group']); ?></strong></td>
                        <td><?= $row['units_needed']; ?></td>
                        <td><?= htmlspecialchars($row['hospital']); ?></td>
                        <td><?= $row['request_date']; ?></td>
                        <td><?= htmlspecialchars($row['status']); ?></td>
                        <td>
                            <a href="manage_requests.php?delete_id=<?= $row['request_id']; ?>" 
                               class="delete-btn"
                               onclick="return confirm('Are you sure you want to delete this request?');">
                               Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

</body>
</html>