 <?php
// filepath: donor/delete_donor.php
header('Content-Type: application/json; charset=utf-8');
session_start();
include_once __DIR__ . '/../db_connect.php';
include_once __DIR__ . '/donor_functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok'=>false,'error'=>'Only POST allowed']);
    exit;
}

// if (!isset($_SESSION['admin'])) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid id']); exit; }

$res = deleteDonor($conn, $id);
if ($res['ok']) echo json_encode(['ok'=>true,'message'=>'Donor deleted']);
else echo json_encode(['ok'=>false,'error'=>$res['error'] ?? 'Delete failed']);
