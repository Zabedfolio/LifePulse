<?php
include 'db_connect.php'; // Connect to LifePulse_DB
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LifePulse - Blood Bank Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent: #d32f2f; /* Red for blood theme */
            --bg: #f8f9fa; /* Light gray background */
            --text: #333; /* Dark text */
            --card-bg: #ffffff; /* White cards */
            --radius: 12px; /* Rounded corners */
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.05); /* Subtle shadow */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        header {
            background: var(--card-bg);
            padding: 20px 0;
            box-shadow: var(--shadow);
        }

        .logo {
            font-size: 28px;
            font-weight: 700;
            color: var(--accent);
        }

        .hero {
            text-align: center;
            padding: 80px 0;
            background: linear-gradient(to bottom, rgba(211, 47, 47, 0.1), transparent);
        }

        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
            color: var(--accent);
        }

        .hero p {
            font-size: 20px;
            max-width: 600px;
            margin: 0 auto 40px;
        }

        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .btn {
            padding: 14px 28px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            border-radius: var(--radius);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--accent);
            color: white;
            border: 2px solid var(--accent);
        }

        .btn-primary:hover {
            background: transparent;
            color: var(--accent);
        }

        .btn-secondary {
            background: transparent;
            color: var(--accent);
            border: 2px solid var(--accent);
        }

        .btn-secondary:hover {
            background: var(--accent);
            color: white;
        }

        .section {
            padding: 60px 0;
        }

        .section h2 {
            text-align: center;
            font-size: 32px;
            margin-bottom: 40px;
            color: var(--text);
        }

        .data-table {
            background: var(--card-bg);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            margin: 0 auto;
            max-width: 800px;
        }

        .data-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th, .data-table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .data-table th {
            background: #f4f4f4;
            font-weight: 600;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .donors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .donor-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
            text-align: center;
        }

        .donor-card h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: var(--accent);
        }

        .donor-card p {
            font-size: 14px;
            margin: 5px 0;
            color: #555;
        }

        footer {
            text-align: center;
            padding: 20px 0;
            background: var(--card-bg);
            font-size: 14px;
            color: #777;
            box-shadow: var(--shadow);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 36px;
            }
            .cta-buttons {
                flex-direction: column;
                gap: 15px;
            }
            .data-table {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>

    <header>
        <div class="container">
            <div class="logo">LifePulse</div>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <h1>Donate Blood, Save Lives</h1>
            <p>Join our community of donors and help those in need. Request blood easily or register to become a donor today.</p>
            <div class="cta-buttons">
                <a href="donor/add_donor.php" class="btn btn-primary">Register as Donor</a>
                <a href="appointments/book_appointment.php" class="btn btn-secondary">Book Appointment</a>
                <a href="requests/add_requests.php" class="btn btn-secondary">Request Blood</a>
                <a href="Admin/admin_login.php" class="btn btn-secondary">Admin Login</a>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2>Available Blood Stock</h2>
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Blood Group</th>
                            <th>Units Available</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stock_query = "SELECT blood_group, units_available FROM blood_stock ORDER BY blood_group";
                        $stock_result = $conn->query($stock_query);
                        if ($stock_result->num_rows > 0) {
                            while ($row = $stock_result->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . htmlspecialchars($row['blood_group']) . "</td>
                                        <td>" . htmlspecialchars($row['units_available']) . "</td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='2' style='text-align: center;'>No stock data available.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2>Current Blood Requests</h2>
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th>Blood Group</th>
                            <th>Units Needed</th>
                            <th>Hospital</th>
                            <th>Request Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $requests_query = "SELECT patient_name, blood_group, units_needed, hospital, request_date 
                                           FROM blood_requests WHERE status = 'pending' ORDER BY request_date DESC";
                        $requests_result = $conn->query($requests_query);
                        if ($requests_result->num_rows > 0) {
                            while ($row = $requests_result->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . htmlspecialchars($row['patient_name']) . "</td>
                                        <td>" . htmlspecialchars($row['blood_group']) . "</td>
                                        <td>" . htmlspecialchars($row['units_needed']) . "</td>
                                        <td>" . htmlspecialchars($row['hospital']) . "</td>
                                        <td>" . htmlspecialchars($row['request_date']) . "</td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' style='text-align: center;'>No pending requests at this time.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2>Registered Donors</h2>
            <div class="donors-grid">
                <?php
                $donors_query = "SELECT name, age, address AS place, phone 
                                 FROM donors ORDER BY created_at DESC";
                $donors_result = $conn->query($donors_query);
                if ($donors_result->num_rows > 0) {
                    while ($row = $donors_result->fetch_assoc()) {
                        echo '<div class="donor-card">
                                <h3>' . htmlspecialchars($row['name']) . '</h3>
                                <p>Age: ' . htmlspecialchars($row['age'] ?: 'Not specified') . '</p>
                                <p>Place: ' . htmlspecialchars($row['place'] ?: 'Not specified') . '</p>
                                <p>Mobile: ' . htmlspecialchars($row['phone']) . '</p>
                              </div>';
                    }
                } else {
                    echo '<p style="text-align: center; width: 100%;">No registered donors yet.</p>';
                }
                ?>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            &copy; 2025 LifePulse. All rights reserved.
        </div>
    </footer>

</body>
</html>