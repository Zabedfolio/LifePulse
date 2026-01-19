<?php
include 'db_connect.php'; // Connect to LifePulse_DB
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Handle filters for Blood Requests
$request_filter_blood_group = isset($_GET['request_blood_group']) ? $_GET['request_blood_group'] : '';
$request_filter_place = isset($_GET['request_place']) ? $_GET['request_place'] : '';
// Build query for pending requests with filters
$requests_query = "SELECT patient_name, blood_group, units_needed, hospital, request_date
                   FROM blood_requests WHERE status = 'pending'";
if ($request_filter_blood_group) {
    $requests_query .= " AND blood_group = '" . $conn->real_escape_string($request_filter_blood_group) . "'";
}
if ($request_filter_place) {
    $requests_query .= " AND hospital LIKE '%" . $conn->real_escape_string($request_filter_place) . "%'";
}
$requests_query .= " ORDER BY request_date DESC";
$requests_result = $conn->query($requests_query);
// Handle filters for Donors
$donor_filter_blood_group = isset($_GET['donor_blood_group']) ? $_GET['donor_blood_group'] : '';
$donor_filter_place = isset($_GET['donor_place']) ? $_GET['donor_place'] : '';
// Build query for donors with filters
$donors_query = "SELECT name, age, address AS place, phone, blood_group
                 FROM donors";
if ($donor_filter_blood_group || $donor_filter_place) {
    $donors_query .= " WHERE 1=1";
    if ($donor_filter_blood_group) {
        $donors_query .= " AND blood_group = '" . $conn->real_escape_string($donor_filter_blood_group) . "'";
    }
    if ($donor_filter_place) {
        $donors_query .= " AND address LIKE '%" . $conn->real_escape_string($donor_filter_place) . "%'";
    }
}
$donors_query .= " ORDER BY created_at DESC";
$donors_result = $conn->query($donors_query);
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
            --radius: 16px; /* Softer rounded corners for modern look */
            --shadow: 0 6px 24px rgba(0, 0, 0, 0.08); /* Deeper shadow for depth */
            --hover-shadow: 0 8px 32px rgba(0, 0, 0, 0.12); /* Hover effect shadow */
            --transition: all 0.3s ease; /* Smooth transitions */
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
            overflow-x: hidden; /* Prevent horizontal scroll */
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
            position: sticky;
            top: 0;
            z-index: 10;
            transition: var(--transition);
        }
        .logo {
            font-size: 28px;
            font-weight: 700;
            color: var(--accent);
        }
        .hero {
            text-align: center;
            padding: 120px 0; /* Increased padding for better hero height */
            background: linear-gradient(to bottom, rgba(211, 47, 47, 0.1), transparent);
            background-image: url('blood.jpg'); /* Add cover image */
            background-size: cover;
            background-position: center;
            background-attachment: fixed; /* Modern parallax effect */
            position: relative;
            color: white; /* Text color for overlay */
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.4)); /* Dark overlay for readability */
            z-index: 1;
        }
        .hero > * {
            position: relative;
            z-index: 2; /* Ensure content is above overlay */
        }
        .hero h1 {
            font-size: 60px; /* Larger for modern impact */
            margin-bottom: 20px;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5); /* Text shadow for contrast */
        }
        .hero p {
            font-size: 24px; /* Slightly larger */
            max-width: 700px;
            margin: 0 auto 40px;
            color: white;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
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
            transition: var(--transition);
            cursor: pointer;
            border: 2px solid white; /* Modern border */
            color: white;
            background: transparent;
        }
        .btn-primary {
            background: var(--accent);
            border-color: var(--accent);
        }
        .btn-primary:hover {
            background: white;
            color: var(--accent);
            box-shadow: var(--hover-shadow);
            transform: translateY(-2px); /* Lift effect */
        }
        .btn-secondary {
            background: transparent;
            border-color: white;
        }
        .btn-secondary:hover {
            background: white;
            color: var(--accent);
            box-shadow: var(--hover-shadow);
            transform: translateY(-2px); /* Lift effect */
        }
        .section {
            padding: 60px 0;
            transition: var(--transition);
        }
        h2 {
            font-size: 32px;
            margin-bottom: 30px;
            text-align: center;
            color: var(--accent);
            position: relative;
        }
        h2::after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background: var(--accent);
            margin: 10px auto 0;
            border-radius: 2px; /* Modern underline */
        }
        .blood-stock-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }
        .stock-card {
            background: var(--card-bg);
            padding: 20px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
        }
        .stock-card:hover {
            box-shadow: var(--hover-shadow);
            transform: translateY(-5px); /* Modern lift on hover */
        }
        .stock-card h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .stock-card p {
            font-size: 18px;
            color: var(--muted);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-bg);
            box-shadow: var(--shadow);
            border-radius: var(--radius);
            overflow: hidden;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
            transition: var(--transition);
        }
        th {
            background: rgba(211, 47, 47, 0.1);
            font-weight: 600;
        }
        tr:hover {
            background: rgba(211, 47, 47, 0.05); /* Subtle hover for table rows */
        }
        .donors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .donor-card {
            background: var(--card-bg);
            padding: 20px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        .donor-card:hover {
            box-shadow: var(--hover-shadow);
            transform: translateY(-5px); /* Lift on hover */
        }
        .donor-card h3 {
            font-size: 20px;
            margin-bottom: 10px;
            font-weight: 700; /* Bold name */
        }
        .donor-card p {
            font-size: 16px;
            margin-bottom: 5px;
        }
        .donor-card .blood-group {
            font-weight: 700; /* Bold blood group */
            color: var(--accent); /* Accent color for visibility */
        }
        footer {
            background: var(--card-bg);
            padding: 20px 0;
            text-align: center;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
        }
        /* Filter Forms */
        .filter-form {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        .filter-form select, .filter-form input[type="text"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: var(--radius);
            font-size: 16px;
            transition: var(--transition);
        }
        .filter-form select:focus, .filter-form input[type="text"]:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(211, 47, 47, 0.2); /* Modern focus ring */
        }
        .filter-form button {
            padding: 10px 20px;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
        }
        .filter-form button:hover {
            background: #b71c1c;
            transform: translateY(-2px);
        }
        /* Modal Styles (fixed for better visibility and scrolling) */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
            padding: 20px;
            transition: opacity 0.3s ease;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: var(--radius);
            width: 500px;
            max-width: 90%;
            box-shadow: var(--shadow);
            max-height: 80vh;
            overflow-y: auto;
            transform: scale(0.95);
            transition: transform 0.3s ease;
        }
        .modal.show .modal-content {
            transform: scale(1); /* Modern scale-in animation */
        }
        .close-btn {
            float: right;
            font-size: 24px;
            cursor: pointer;
            color: #aaa;
            transition: var(--transition);
        }
        .close-btn:hover {
            color: #000;
            transform: rotate(90deg); /* Fun rotate on hover */
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 10px;
            text-align: center;
        }
        .success { background: #e8f5e9; color: #2e7d32; }
        .error { background: #ffebee; color: #c62828; }
        .modal-content label {
            display: block;
            margin: 10px 0 5px;
            font-weight: 600;
        }
        .modal-content input, .modal-content select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            transition: var(--transition);
        }
        .modal-content input:focus, .modal-content select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(211, 47, 47, 0.2);
        }
        .modal-content button {
            width: 100%;
            padding: 12px;
            background: #d32f2f;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 20px;
            transition: var(--transition);
        }
        .modal-content button:hover {
            background: #b71c1c;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">LifePulse</div>
        </div>
    </header>
    <div class="hero">
        <div class="container">
            <h1>Save <span id="changing-text">Lives</span></h1>
            <p id="type-text"></p>
            <div class="cta-buttons">
                <button class="btn btn-primary" onclick="openModal('donorModal')">Register as Donor</button>
                <button class="btn btn-secondary" onclick="openModal('requestModal')">Request Blood</button>
            </div>
        </div>
    </div>
    <!-- Donor Registration Modal -->
    <div id="donorModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('donorModal')">&times;</span>
            <h2>Register as Donor</h2>
            <div id="donorMessage" class="message" style="display: none;"></div>
            <form id="donorForm">
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
                <button type="submit">Register</button>
            </form>
        </div>
    </div>
    <!-- Blood Request Modal -->
    <div id="requestModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('requestModal')">&times;</span>
            <h2>Request Blood</h2>
            <div id="requestMessage" class="message" style="display: none;"></div>
            <form id="requestForm">
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
                <button type="submit">Submit Request</button>
            </form>
        </div>
    </div>
    <section class="section">
        <div class="container">
            <h2>Blood Stock Availability</h2>
            <div class="blood-stock-grid">
                <?php
                // Fetch blood stock
                $stock_query = "SELECT blood_group, units_available FROM blood_stock";
                $stock_result = $conn->query($stock_query);
                $blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                $stock_data = array_fill_keys($blood_groups, 0);
                if ($stock_result->num_rows > 0) {
                    while ($row = $stock_result->fetch_assoc()) {
                        $stock_data[$row['blood_group']] = $row['units_available'];
                    }
                }
                foreach ($blood_groups as $group) {
                    echo '<div class="stock-card">
                            <h3>' . $group . '</h3>
                            <p>' . $stock_data[$group] . ' units</p>
                          </div>';
                }
                ?>
            </div>
        </div>
    </section>
    <section class="section" style="background: #fff;">
        <div class="container">
            <h2>Pending Blood Requests</h2>
            <!-- Filter Form for Requests -->
            <form class="filter-form" method="GET">
                <select name="request_blood_group">
                    <option value="">All Blood Groups</option>
                    <option value="A+" <?php if ($request_filter_blood_group == 'A+') echo 'selected'; ?>>A+</option>
                    <option value="A-" <?php if ($request_filter_blood_group == 'A-') echo 'selected'; ?>>A-</option>
                    <option value="B+" <?php if ($request_filter_blood_group == 'B+') echo 'selected'; ?>>B+</option>
                    <option value="B-" <?php if ($request_filter_blood_group == 'B-') echo 'selected'; ?>>B-</option>
                    <option value="AB+" <?php if ($request_filter_blood_group == 'AB+') echo 'selected'; ?>>AB+</option>
                    <option value="AB-" <?php if ($request_filter_blood_group == 'AB-') echo 'selected'; ?>>AB-</option>
                    <option value="O+" <?php if ($request_filter_blood_group == 'O+') echo 'selected'; ?>>O+</option>
                    <option value="O-" <?php if ($request_filter_blood_group == 'O-') echo 'selected'; ?>>O-</option>
                </select>
                <input type="text" name="request_place" placeholder="Filter by Place/Hospital" value="<?php echo htmlspecialchars($request_filter_place); ?>">
                <button type="submit">Filter</button>
            </form>
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
    </section>
    <section class="section">
        <div class="container">
            <h2>Registered Donors</h2>
            <!-- Filter Form for Donors -->
            <form class="filter-form" method="GET">
                <select name="donor_blood_group">
                    <option value="">All Blood Groups</option>
                    <option value="A+" <?php if ($donor_filter_blood_group == 'A+') echo 'selected'; ?>>A+</option>
                    <option value="A-" <?php if ($donor_filter_blood_group == 'A-') echo 'selected'; ?>>A-</option>
                    <option value="B+" <?php if ($donor_filter_blood_group == 'B+') echo 'selected'; ?>>B+</option>
                    <option value="B-" <?php if ($donor_filter_blood_group == 'B-') echo 'selected'; ?>>B-</option>
                    <option value="AB+" <?php if ($donor_filter_blood_group == 'AB+') echo 'selected'; ?>>AB+</option>
                    <option value="AB-" <?php if ($donor_filter_blood_group == 'AB-') echo 'selected'; ?>>AB-</option>
                    <option value="O+" <?php if ($donor_filter_blood_group == 'O+') echo 'selected'; ?>>O+</option>
                    <option value="O-" <?php if ($donor_filter_blood_group == 'O-') echo 'selected'; ?>>O-</option>
                </select>
                <input type="text" name="donor_place" placeholder="Filter by Place" value="<?php echo htmlspecialchars($donor_filter_place); ?>">
                <button type="submit">Filter</button>
            </form>
            <div class="donors-grid">
                <?php
                if ($donors_result->num_rows > 0) {
                    while ($row = $donors_result->fetch_assoc()) {
                        echo '<div class="donor-card">
                                <h3>' . htmlspecialchars($row['name']) . '</h3>
                                <p>Age: ' . htmlspecialchars($row['age'] ?: 'Not specified') . '</p>
                                <p class="blood-group">Blood Group: ' . htmlspecialchars($row['blood_group'] ?: 'Not specified') . '</p>
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
    <script>
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10); // For animation
        }
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                document.getElementById(modalId === 'donorModal' ? 'donorMessage' : 'requestMessage').style.display = 'none';
            }, 300); // Match transition time
        }
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal(event.target.id);
            }
        };
        // Handle Donor Form
        document.getElementById('donorForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('add_donor', '1');
            fetch('donor/add_donor.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                const messageDiv = document.getElementById('donorMessage');
                messageDiv.style.display = 'block';
                if (data.ok) {
                    messageDiv.className = 'message success';
                    messageDiv.textContent = data.message || '✅ Donor registered successfully!';
                    this.reset();
                } else {
                    messageDiv.className = 'message error';
                    messageDiv.textContent = data.error || '❌ Failed to register';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const messageDiv = document.getElementById('donorMessage');
                messageDiv.style.display = 'block';
                messageDiv.className = 'message error';
                messageDiv.textContent = '❌ Network or parse error: ' + error.message;
            });
        });
        // Handle Request Form
        document.getElementById('requestForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('add_request', '1');
            fetch('requests/add_requests.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                const messageDiv = document.getElementById('requestMessage');
                messageDiv.style.display = 'block';
                if (data.ok) {
                    messageDiv.className = 'message success';
                    messageDiv.textContent = data.message || '✅ Request submitted successfully!';
                    this.reset();
                } else {
                    messageDiv.className = 'message error';
                    messageDiv.textContent = data.error || '❌ Failed to submit request';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const messageDiv = document.getElementById('requestMessage');
                messageDiv.style.display = 'block';
                messageDiv.className = 'message error';
                messageDiv.textContent = '❌ Network or parse error: ' + error.message;
            });
        });
        // Existing scripts for text animation
        document.addEventListener("DOMContentLoaded", function () {
            const textElement = document.getElementById("changing-text");
            const words = ["Lives", "Humankind", "Hope"];
            let index = 0;
            function changeText() {
                textElement.style.animation = "fadeOutUp 0.5s ease-in forwards";
                setTimeout(() => {
                    index = (index + 1) % words.length;
                    textElement.textContent = words[index];
                    textElement.style.animation =
                        "fadeUp 0.6s cubic-bezier(0.23, 1, 0.32, 1) forwards";
                }, 500);
            }
            setInterval(changeText, 3000);
        });
        document.addEventListener("DOMContentLoaded", function () {
            const texts = [
                "Join our community of donors and help those in need.",
                "Request blood easily in emergency situations.",
                "Register today and become a life saver."
            ];
            const textElement = document.getElementById("type-text");
            let textIndex = 0;
            let charIndex = 0;
            let isDeleting = false;
            function typeEffect() {
                const currentText = texts[textIndex];
                if (!isDeleting) {
                    textElement.textContent = currentText.substring(0, charIndex + 1);
                    charIndex++;
                    if (charIndex === currentText.length) {
                        setTimeout(() => isDeleting = true, 1500);
                    }
                } else {
                    textElement.textContent = currentText.substring(0, charIndex - 1);
                    charIndex--;
                    if (charIndex === 0) {
                        isDeleting = false;
                        textIndex = (textIndex + 1) % texts.length;
                    }
                }
                setTimeout(typeEffect, isDeleting ? 50 : 80);
            }
            typeEffect();
        });
    </script>
</body>
</html>