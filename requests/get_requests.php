<?php
// filepath: requests/get_requests.php
header('Content-Type: application/json; charset=utf-8');

include __DIR__ . '/../db_connect.php';
include __DIR__ . '/request_functions.php';

// Optional: check admin session if you want only admins to call this
// session_start();
// if (!isset($_SESSION['admin'])) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

$requests = getAllRequests($conn);
if ($requests === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Failed to fetch requests']);
} else {
    echo json_encode(['ok' => true, 'count' => count($requests), 'requests' => $requests]);
}
?>