<?php
// filepath: donor/get_donors.php
header('Content-Type: application/json; charset=utf-8');
session_start();
include_once __DIR__ . '/../db_connect.php';
include_once __DIR__ . '/donor_functions.php';

// Optional: check admin session if you want only admins to call this
// if (!isset($_SESSION['admin'])) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

$donors = getAllDonors($conn);
echo json_encode(['ok' => true, 'count' => count($donors), 'donors' => $donors]);
