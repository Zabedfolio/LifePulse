<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../db_connect.php';
include __DIR__ . '/request_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_request'])) {
    $data = [
        'patient_name' => trim($_POST['patient_name'] ?? ''),
        'blood_group' => trim($_POST['blood_group'] ?? ''),
        'units_needed' => intval($_POST['units_needed'] ?? 0),
        'hospital' => trim($_POST['hospital'] ?? ''),
        'contact' => trim($_POST['contact'] ?? ''),
        'status' => 'pending'
    ];

    if ($data['patient_name'] && $data['blood_group'] && $data['units_needed'] > 0 && $data['contact']) {
        $res = createRequest($conn, $data);
        if ($res['ok']) {
            echo json_encode(['ok' => true, 'message' => 'Request submitted successfully! An admin will review it.']);
        } else {
            echo json_encode(['ok' => false, 'error' => $res['error'] ?? 'Failed to submit request']);
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
    <title>Request Blood - LifePulse</title>
    <style>
        :root {
            --accent: #d32f2f;
            --card-bg: #ffffff;
            --radius: 10px;
        }
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: #f4f4f4;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .form-card {
            background: var(--card-bg);
            padding: 40px 35px;
            padding-left: 60px;
            padding-right: 60px;
            border-radius: var(--radius);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            width: 400px;
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
            border: none;
            color: white;
            font-size: 16px;
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .form-card button:hover {
            background: #b71c1c;
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
    <h2>Request Blood</h2>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, '✅') === 0 ? 'success' : 'error'; ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label>Patient Name *</label>
        <input type="text" name="patient_name" required>

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

        <label>Units Needed *</label>
        <input type="number" name="units_needed" min="1" required>

        <label>Hospital</label>
        <input type="text" name="hospital">

        <label>Contact (Phone/Email) *</label>
        <input type="text" name="contact" required>

        <button type="submit" name="add_request">Submit Request</button>
    </form>
</div>

</body>
</html>