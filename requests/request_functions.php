<?php
// filepath: requests/request_functions.php
// include with: include_once __DIR__ . '/request_functions.php';

if (!isset($conn)) {
    // Expect caller already included ../db_connect.php
    // If not, uncomment the next line:
    // include_once __DIR__ . '/../db_connect.php';
}

// GET ALL REQUESTS
function getAllRequests($conn) {
    $sql = "SELECT request_id, patient_name, blood_group, units_needed, hospital, contact, status, request_date 
            FROM blood_requests ORDER BY request_date DESC";

    $res = $conn->query($sql);
    if (!$res) {
        die("Database query failed: " . $conn->error);
    }

    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }
    return $rows;
}

// GET REQUEST BY ID
function getRequestById($conn, $request_id) {
    $stmt = $conn->prepare("SELECT request_id, patient_name, blood_group, units_needed, hospital, contact, status, request_date 
                            FROM blood_requests WHERE request_id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc() ?: null;
    $stmt->close();
    return $row;
}

// CREATE REQUEST
function createRequest($conn, $data) {
    $sql = "INSERT INTO blood_requests (patient_name, blood_group, units_needed, hospital, contact, status, request_date) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $status = $data['status'] ?? 'pending';
    // s = string, i = integer
    $stmt->bind_param(
        "ssisss", 
        $data['patient_name'], 
        $data['blood_group'], 
        $data['units_needed'], 
        $data['hospital'], 
        $data['contact'], 
        $status
    );
    $ok = $stmt->execute();
    if (!$ok) $err = $stmt->error;
    $id = $stmt->insert_id;
    $stmt->close();
    return ['ok' => $ok, 'id' => $id, 'error' => $err ?? null];
}

// UPDATE REQUEST
function updateRequest($conn, $request_id, $data) {
    $sql = "UPDATE blood_requests SET patient_name=?, blood_group=?, units_needed=?, hospital=?, contact=?, status=? 
            WHERE request_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssisssi", 
        $data['patient_name'], 
        $data['blood_group'], 
        $data['units_needed'], 
        $data['hospital'], 
        $data['contact'], 
        $data['status'], 
        $request_id
    );
    $ok = $stmt->execute();
    if (!$ok) $err = $stmt->error;
    $stmt->close();
    return ['ok' => $ok, 'error' => $err ?? null];
}

// DELETE REQUEST
function deleteRequest($conn, $request_id) {
    $stmt = $conn->prepare("DELETE FROM blood_requests WHERE request_id = ?");
    $stmt->bind_param("i", $request_id);
    $ok = $stmt->execute();
    if (!$ok) $err = $stmt->error;
    $stmt->close();
    return ['ok' => $ok, 'error' => $err ?? null];
}
?>