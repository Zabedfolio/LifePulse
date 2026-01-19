<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../admin_login.php"); // Redirect to login if not admin
    exit;
}

include __DIR__ . '/../db_connect.php'; // up one level to root
include __DIR__ . '/donor_functions.php'; // same folder

if (!isset($_GET['donor_id'])) {
    header("Location: ../manage_donors.php");
    exit;
}

$donor_id = intval($_GET['donor_id']);
$donor = getDonorById($conn, $donor_id);

if (!$donor) {
    header("Location: ../manage_donors.php?error=DonorNotFound");
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
.sidebar { width: 220px; background: var(--sidebar-bg); color: white; padding: 20px; position: fixed; height: 100vh; }
.sidebar h2 { margin-bottom: 20px; }
.sidebar ul { list-style: none; }
.sidebar li { margin: 10px 0; }
.sidebar a { color: white; text-decoration: none; }
.main-content { flex: 1; margin-left: 240px; padding: 30px; }
.card { background: var(--card-bg); padding: 20px; border-radius: var(--radius); box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px; }
.card h3 { color: var(--accent); margin-bottom: 20px; }
.card label { display: block; margin: 10px 0 5px; font-weight: 600; }
.card input, .card select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: var(--radius); }
.card button { padding: 10px; background: var(--accent); color: white; border: none; border-radius: var(--radius); cursor: pointer; }
.card button:hover { background: #b71c1c; }
.message { padding: 10px; border-radius: var(--radius); margin-bottom: 20px; }
.success { background: #e8f5e9; color: #2e7d32; }
.error { background: #ffebee; color: #c62828; }
</style>
</head>
<body>

<?php include 'sidebar.php'; // Assuming you have sidebar.php for admin nav ?>

<div class="layout">
    <div class="main-content">
        <div class="card">
            <?php if ($message): ?>
                <div class="message <?= strpos($message, '✅') === 0 ? 'success' : 'error'; ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
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