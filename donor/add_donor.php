<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../db_connect.php';
include __DIR__ . '/donor_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_donor'])) {
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'age' => intval($_POST['age'] ?? 0),
        'gender' => trim($_POST['gender'] ?? ''),
        'blood_group' => trim($_POST['blood_group'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'last_donation_date' => trim($_POST['last_donation_date'] ?? null)
    ];

    if ($data['name'] && $data['blood_group'] && $data['phone']) {
        $res = createDonor($conn, $data);
        if ($res['ok']) {
            echo json_encode(['ok' => true, 'message' => 'Donor registered successfully! An admin will contact you.']);
        } else {
            echo json_encode(['ok' => false, 'error' => $res['error'] ?? 'Failed to register']);
        }
    } else {
        echo json_encode(['ok' => false, 'error' => 'Please fill all required fields.']);
    }
} else {
    echo json_encode(['ok' => false, 'error' => 'Invalid request']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register as Donor - LifePulse</title>
    <style>
        :root {
            --accent: #d32f2f;
            --card-bg: #ffffff;
            --radius: 10px;
        }
        body {
            margin-top: 100px;
            margin-bottom: 100px;
            font-family: 'Inter', sans-serif;
            background: #f4f4f4;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .form-card {
            background: var(--card-bg);
            padding: 35px 35px;
            padding-left: 60px;
            padding-right: 60px;
            border-radius: var(--radius);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            width: 600px;
        }
        .form-card h2 {
            margin-bottom: 25px;
            font-size: 24px;
            color: var(--accent);
            text-align: center;
        }
        .form-card label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-card input, .form-card select {
            width: 95%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: var(--radius);
            font-size: 14px;
        }
        .form-card button {
            width: 100%;
            padding: 12px;
            background: var(--accent);
            border: 3px solid var(--accent);
            color: white;
            font-size: 16px;
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .form-card button:hover {
            background: #b71c1c;
            border: 3px solid var(--accent);
            background-color: #ffffffff;
            color: #b71c1c;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: var(--radius);
            text-align: center;
        }
        .success { background: #e8f5e9; color: #2e7d32; }
        .error { background: #ffebee; color: #c62828; }
    </style>
</head>
<body>

<div class="form-card">
    <h2>Register as Donor</h2>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, '✅') === 0 ? 'success' : 'error'; ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label>Name *</label>
        <input type="text" name="name" required>

        <label>Age</label>
        <input type="number" name="age" min="18">

        <label>Gender</label>
        <select name="gender">
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select>

        <label>Blood Group *</label>
        <select name="blood_group" required>
            <option value="">Select Blood Group</option>
            <option value="A+">A+</option>
            <option value="A-">A-</option>
            <option value="B+">B+</option>
            <option value="B-">B-</option>
            <option value="AB+">AB+</option>
            <option value="AB-">AB-</option>
            <option value="O+">O+</option>
            <option value="O-">O-</option>
        </select>

        <label>Phone *</label>
        <input type="text" name="phone" required>

        <label>Email</label>
        <input type="email" name="email">

        <label>Address</label>
        <input type="text" name="address">

        <label>Last Donation Date</label>
        <input type="date" name="last_donation_date">

        <button type="submit" name="add_donor">Register</button>
    </form>
</div>

</body>
</html>