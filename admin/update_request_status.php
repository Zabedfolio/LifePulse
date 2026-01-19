<?php
header('Content-Type: application/json');

include 'db_connect.php';
include 'requests/request_functions.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['request_id']) || !isset($data['status'])) {
    echo json_encode(['ok' => false, 'error' => 'Invalid data']);
    exit;
}

$request_id = intval($data['request_id']);
$status = trim($data['status']);

if (!in_array($status, ['pending', 'completed'])) {
    echo json_encode(['ok' => false, 'error' => 'Invalid status']);
    exit;
}

// Get current request data (to fill dummy fields)
$request = getRequestById($conn, $request_id);
if (!$request) {
    echo json_encode(['ok' => false, 'error' => 'Request not found']);
    exit;
}

$res = updateRequest($conn, $request_id, [
    'patient_name' => $request['patient_name'],
    'blood_group' => $request['blood_group'],
    'units_needed' => $request['units_needed'],
    'hospital' => $request['hospital'] ?? '',
    'contact' => $request['contact'] ?? '',
    'status' => $status
]);

echo json_encode(['ok' => $res['ok'], 'error' => $res['error'] ?? null]);
?>