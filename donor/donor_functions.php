<?php
// filepath: donor/donor_functions.php
// include with: include_once __DIR__ . '/donor_functions.php';

if (!isset($conn)) {
    // Expect caller already included ../db_connect.php
    // If not, uncomment the next line:
    // include_once __DIR__ . '/../db_connect.php';
}

// GET ALL DONORS
function getAllDonors($conn) {
    $sql = "SELECT donor_id, name, age, gender, blood_group, phone, email, address, last_donation_date, created_at 
            FROM donors ORDER BY created_at DESC";

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

// GET DONOR BY ID
function getDonorById($conn, $donor_id) {
    $stmt = $conn->prepare("SELECT donor_id, name, age, gender, blood_group, phone, email, address, last_donation_date 
                            FROM donors WHERE donor_id = ?");
    $stmt->bind_param("i", $donor_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc() ?: null;
    $stmt->close();
    return $row;
}

// CREATE DONOR (updated to also increment blood stock)
function createDonor($conn, $data) {
    $sql = "INSERT INTO donors (name, age, gender, blood_group, phone, email, address, last_donation_date, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $ld = $data['last_donation_date'] ?: null;
    // s = string, i = integer
    $stmt->bind_param(
        "sissssss", 
        $data['name'], 
        $data['age'], 
        $data['gender'], 
        $data['blood_group'], 
        $data['phone'], 
        $data['email'], 
        $data['address'], 
        $ld
    );
    $ok = $stmt->execute();
    if (!$ok) $err = $stmt->error;
    $id = $stmt->insert_id;
    $stmt->close();

    if ($ok) {
        // Update blood stock: add 1 unit for this blood group
        $bg = $data['blood_group'];
        $stock_sql = "INSERT INTO blood_stock (blood_group, units_available) 
                      VALUES (?, 1) 
                      ON DUPLICATE KEY UPDATE units_available = units_available + 1";
        $stock_stmt = $conn->prepare($stock_sql);
        $stock_stmt->bind_param("s", $bg);
        $stock_ok = $stock_stmt->execute();
        if (!$stock_ok) {
            // Optional: log error, but continue (don't fail donor insert)
            error_log("Blood stock update failed: " . $stock_stmt->error);
        }
        $stock_stmt->close();
    }

    return ['ok' => $ok, 'id' => $id, 'error' => $err ?? null];
}

// UPDATE DONOR
function updateDonor($conn, $donor_id, $data) {
    $sql = "UPDATE donors SET name=?, age=?, gender=?, blood_group=?, phone=?, email=?, address=?, last_donation_date=? 
            WHERE donor_id=?";
    $stmt = $conn->prepare($sql);
    $ld = $data['last_donation_date'] ?: null;
    $stmt->bind_param(
        "sissssssi", 
        $data['name'], 
        $data['age'], 
        $data['gender'], 
        $data['blood_group'], 
        $data['phone'], 
        $data['email'], 
        $data['address'], 
        $ld, 
        $donor_id
    );
    $ok = $stmt->execute();
    if (!$ok) $err = $stmt->error;
    $stmt->close();
    return ['ok' => $ok, 'error' => $err ?? null];
}

// DELETE DONOR
function deleteDonor($conn, $donor_id) {
    $stmt = $conn->prepare("DELETE FROM donors WHERE donor_id = ?");
    $stmt->bind_param("i", $donor_id);
    $ok = $stmt->execute();
    if (!$ok) $err = $stmt->error;
    $stmt->close();
    return ['ok' => $ok, 'error' => $err ?? null];
}
?>