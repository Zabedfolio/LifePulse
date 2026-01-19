<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include __DIR__ . '/../db_connect.php'; // up one level to root
include __DIR__ . '/../donor/donor_functions.php';

if (!function_exists('getAllDonors')) {
    die('donor_functions.php not loaded');
}



$message = '';

// ===== HANDLE ADD DONOR FORM =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_donor'])) {
    $name = trim($_POST['name'] ?? '');
    $blood_group = trim($_POST['blood_group'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $last_donation_date = trim($_POST['last_donation_date'] ?? '');

    $age = trim($_POST['age'] ?? null);
$gender = trim($_POST['gender'] ?? null);

$res = createDonor($conn, [
    'name' => $name,
    'age' => $age,
    'gender' => $gender,
    'blood_group' => $blood_group,
    'phone' => $phone,
    'email' => $email,
    'address' => $address,
    'last_donation_date' => $last_donation_date
]);

}

// ===== DELETE DONOR =====
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $res = deleteDonor($conn, $delete_id);
    if ($res['ok']) {
        header("Location: manage_donors.php?deleted=1");
    } else {
        $message = '❌ Error deleting donor: ' . ($res['error'] ?? 'Unknown error');
    }
    exit;
}

// ===== FETCH DONORS =====
$donors = getAllDonors($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Donors - LifePulse</title>
<style>
:root {
    --accent: #d32f2f;
    --sidebar-bg: #2c2c2c;
    --card-bg: #ffffff;
    --muted: #6a6a6a;
    --radius: 10px;
}
body { margin: 0; font-family: 'Inter', sans-serif; background: #f4f4f4; }
.layout { display: flex; min-height: 100vh; }
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
.main-content { flex: 1; margin-left: 250px; padding: 30px 40px; }
.main-content h2 { font-size: 26px; margin-bottom: 25px; color: var(--accent); }
.alert { background: #d4f5d4; padding: 12px; border-left: 5px solid #2ecc71; color: #2e7d32; margin-bottom: 20px; border-radius: 6px; font-size: 14px; }

.table-wrapper {
    background: var(--card-bg);
    padding: 20px;
    border-radius: var(--radius);
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow-x: auto;
}
.table-wrapper table { width: 100%; border-collapse: collapse; }
.table-wrapper th, .table-wrapper td { padding: 12px 12px; text-align: left; }
.table-wrapper th { background: #fafafa; font-weight: 600; }
.table-wrapper td { border-top: 1px solid #eee; }
.delete-btn { padding: 6px 12px; background: var(--accent); color: white; text-decoration: none; border-radius: 6px; font-size: 13px; transition: 0.2s; }
.delete-btn:hover { background: #b71c1c; }
.edit-btn { padding: 6px 12px; background: #0eb434ff; color: white; text-decoration: none; border-radius: 6px; font-size: 13px; margin-right: 8px; transition: 0.2s; }
.edit-btn:hover { background: #0b8828ff; }

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

@media (max-width: 900px) {
    .main-content { margin-left: 0; padding: 20px; }
    .sidebar { position: relative; width: 100%; height: auto; }
}
</style>
</head>
<body>

<div class="layout">
    <div class="sidebar">
        <h2>LifePulse</h2>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="manage_donors.php">Manage Donors</a></li>
            <li><a href="manage_requests.php">Manage Requests</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h2>Manage Donors</h2>

        <?php if ($message): ?>
            <div class="alert"><?= htmlspecialchars($message) ?></div>
        <?php elseif (isset($_GET['deleted'])): ?>
            <div class="alert">Donor deleted successfully.</div>
        <?php endif; ?>

        <!-- ADD DONOR FORM -->
<div class="form-card">
    <h3>Add New Donor</h3>
    <form method="POST" action="">
        <input type="hidden" name="add_donor" value="1">
        <label>Name *</label>
        <input type="text" name="name" required>
        <label>Age</label>
        <input type="number" name="age">
        <label>Gender</label>
        <select name="gender">
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select>
        <label>Email</label>
        <input type="email" name="email">
        <label>Phone *</label>
        <input type="text" name="phone" required>
        <label>Blood Group *</label>
        <input type="text" name="blood_group" required>
        <label>Address</label>
        <input type="text" name="address">
        <label>Last Donation Date</label>
        <input type="date" name="last_donation_date">
        <button type="submit">Add Donor</button>
    </form>
</div>

<!-- DONORS TABLE -->
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Blood Group</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Address</th>
                <th>Last Donation</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($donors as $row): ?>
            <tr>
                <td><?= $row['donor_id']; ?></td>
                <td><?= htmlspecialchars($row['name']); ?></td>
                <td><?= htmlspecialchars($row['age']); ?></td>
                <td><?= htmlspecialchars($row['gender']); ?></td>
                <td><strong><?= htmlspecialchars($row['blood_group']); ?></strong></td>
                <td><?= htmlspecialchars($row['phone']); ?></td>
                <td><?= htmlspecialchars($row['email']); ?></td>
                <td><?= htmlspecialchars($row['address']); ?></td>
                <td><?= htmlspecialchars($row['last_donation_date']); ?></td>
                <td>
                    <a href="edit_donor.php?donor_id=<?= $row['donor_id']; ?>" class="edit-btn">Edit</a>
                    <a href="manage_donors.php?delete_id=<?= $row['donor_id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this donor?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


    </div>
</div>

</body>
</html>
