<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include __DIR__ . '/../db_connect.php'; // up one level to root
include __DIR__ . '/donor_functions.php'; // same folder

if (!isset($_GET['donor_id'])) {
    header("Location: manage_donors.php");
    exit;
}

$donor_id = intval($_GET['donor_id']);
$donor = getDonorById($conn, $donor_id);

if (!$donor) {
    header("Location: manage_donors.php?error=DonorNotFound");
    exit;
}

$message = '';

// ===== HANDLE UPDATE DONOR FORM =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_donor'])) {
    $name = trim($_POST['name'] ?? '');
    $age = trim($_POST['age'] ?? null);
    $gender = trim($_POST['gender'] ?? null);
    $blood_group = trim($_POST['blood_group'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $last_donation_date = trim($_POST['last_donation_date'] ?? '');

    if ($name === '' || $blood_group === '' || $phone === '') {
        $message = '⚠️ Missing required fields';
    } else {
        $res = updateDonor($conn, $donor_id, [
            'name' => $name,
            'age' => $age,
            'gender' => $gender,
            'blood_group' => $blood_group,
            'phone' => $phone,
            'email' => $email,
            'address' => $address,
            'last_donation_date' => $last_donation_date
        ]);

        if ($res['ok']) {
            $message = '✅ Donor updated successfully!';
            $donor = getDonorById($conn, $donor_id); // refresh data
        } else {
            $message = '❌ Error: ' . ($res['error'] ?? 'Failed to update donor');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Donor - LifePulse</title>
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
.form-card input, .form-card select { width: 100%; padding: 10px; margin-bottom: 10px; border-radius: var(--radius); border: 1px solid #ccc; }
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
        <h2>Edit Donor</h2>

        <?php if ($message): ?>
            <div class="alert"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="form-card">
            <h3>Update Donor Details</h3>
            <form method="POST" action="">
                <input type="hidden" name="update_donor" value="1">
                
                <label>Name *</label>
                <input type="text" name="name" value="<?= htmlspecialchars($donor['name']); ?>" required>

                <label>Age</label>
                <input type="number" name="age" value="<?= htmlspecialchars($donor['age']); ?>">

                <label>Gender</label>
                <select name="gender">
                    <option value="">Select Gender</option>
                    <option value="Male" <?= $donor['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= $donor['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                    <option value="Other" <?= $donor['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                </select>

                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($donor['email']); ?>">

                <label>Phone *</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($donor['phone']); ?>" required>

                <label>Blood Group *</label>
                <input type="text" name="blood_group" value="<?= htmlspecialchars($donor['blood_group']); ?>" required>

                <label>Address</label>
                <input type="text" name="address" value="<?= htmlspecialchars($donor['address']); ?>">

                <label>Last Donation Date</label>
                <input type="date" name="last_donation_date" value="<?= htmlspecialchars($donor['last_donation_date']); ?>">

                <button type="submit">Update Donor</button>
            </form>
        </div>

    </div>
</div>

</body>
</html>
