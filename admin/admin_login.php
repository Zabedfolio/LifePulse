<?php
session_start();
include '../db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM admins WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $_SESSION['admin'] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        $message = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login - LifePulse</title>

<style>
/* ===== ROOT & GLOBAL ===== */
:root {
    --accent: #d32f2f;
    --sidebar-bg: #2c2c2c;
    --card-bg: #ffffff;
    --muted: #6a6a6a;
    --radius: 10px;
    --font-family: 'Inter', sans-serif;
}

body {
    margin: 0;
    font-family: var(--font-family);
    background: #f4f4f4;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
}

/* ===== LOGIN CARD ===== */
.login-card {
    background: var(--card-bg);
    padding: 40px 35px;
    border-radius: var(--radius);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    width: 360px;
    text-align: center;
}

.login-card h2 {
    margin-bottom: 25px;
    font-size: 24px;
    color: var(--accent);
}

.login-card input {
    width: 90%;
    padding: 12px;
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: var(--radius);
    font-size: 14px;
}

.login-card button {
    width: 98%;
    padding: 12px;
    background: var(--accent);
    border: 2px solid var(--accent);
    color: white;
    font-size: 16px;
    border-radius: var(--radius);
    cursor: pointer;
    margin-top: 15px;
    transition: all 0.3s ease;
}

.login-card button:hover {
    background: white;
    color: var(--accent);
}

.error {
    color: #d32f2f;
    font-size: 14px;
    margin-top: 10px;
}
</style>
</head>

<body>

<div class="login-card">
    <h2>Admin Login</h2>

    <?php if ($message != ""): ?>
        <div class="error"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>
