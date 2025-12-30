<?php
include 'db_connect.php'; // Connect to LifePulse_DB

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
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 28px;
            font-weight: 700;
            color: var(--accent);
        }

        .header-buttons {
            display: flex;
            gap: 20px;
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

        .btn-primary-header {
    background: var(--accent);
    color: white;
    border: 1px solid var(--accent); /* thinner border */
    padding: 9px 12px;               /* smaller size */
    font-size: 11px;                 /* smaller text */
    border-radius: 6px;
    line-height: 1.2;
}

.btn-primary-header:hover {
    background: transparent;
            color: var(--accent);
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
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        .data-table th {
            background: #b71c1c;           
            font-weight: 600;  
            color: white;            
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
            box-shadow: var(--shadow);
            text-align: center;
            overflow: hidden;
            padding: 0;   
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .donor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .donor-card h3 {
            margin: 0;
            padding: 10px;
            background: #b71c1c;
            color: white;
            font-size: 22px;
        }

        .donor-body {
            padding: 20px;
        }

        .donor-card p {
            font-size: 14px;
            margin: 5px 0;
            color: #000000ff;
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
            .header-container {
                flex-direction: column;
                gap: 15px;
            }
            .header-buttons {
                width: 100%;
                flex-direction: column;
                gap: 10px;
            }
            .header-buttons .btn {
                width: 100%;
                text-align: center;
            }
        }

        /* New Filter Styles */
        .filter-form {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .filter-form select,
        .filter-form input[type="text"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: var(--radius);
            font-size: 14px;
            min-width: 150px;
        }

        .filter-form button {
            padding: 10px 20px;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: background 0.3s;
        }

        .filter-form button:hover {
            background: #b71c1c;
        }

        .hero-title {
    font-family: 'Inter', sans-serif;
    font-size: 48px;
    margin-bottom: 20px;
    color: var(--accent);
    text-align: center;
}

#changing-text {
    display: inline-block;
    font-weight: 700;
    animation: fadeUp 0.6s cubic-bezier(0.23, 1, 0.32, 1);

background: linear-gradient(to bottom, #f8babaff, #fb0c0cff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}


@keyframes fadeUp {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeOutUp {
    0% {
        opacity: 1;
        transform: translateY(0);
    }
    100% {
        opacity: 0;
        transform: translateY(-20px);
    }
}

.typewriter {
    font-size: 20px;
    max-width: 700px;
    margin: 0 auto 40px;
    text-align: center;
}

.cursor {
    display: inline-block;
    margin-left: 2px;
    color: var(--accent);
    animation: blink 1s infinite;
}

@keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0; }
}

.donor-card p.blood-group {
    font-weight: 1000;
    color: var(--accent);
}


/* Programming Hero style pulse ring */
.heartbeat-ph {
    position: relative;
    z-index: 1;
}

.heartbeat-ph::before,
.heartbeat-ph::after {
    content: "";
    position: absolute;
    inset: -5px;
    border-radius: 8px;
    border: 2px solid rgba(255, 0, 0, 0.7);
    animation: phPulse 1.8s infinite;
    pointer-events: none;
}

.heartbeat-ph::after {
    animation-delay: 0.9s;
}

@keyframes phPulse {
    0% {
        transform: scale(1);
        opacity: 0.85;
    }

    /* First heartbeat */
    25% {
        transform: scale(1.05);
        opacity: 0.6;
    }

    /* Slight pull-back (natural heart motion) */
    35% {
        transform: scale(1.10);
        opacity: 0.55;
    }

    /* Second softer beat */
    55% {
        transform: scale(1.20);
        opacity: 0.25;
    }

    /* Smooth fade out */
    100% {
        transform: scale(1.30);
        opacity: 0;
    }
}








    </style>
</head>
<body>

    <header>
        <div class="container header-container">
            <div class="logo">LifePulse</div>
            <div class="header-buttons">
                <a href="donor/add_donor.php" class="btn btn-primary-header heartbeat-ph">Register as Donor</a>

                <a href="Admin/admin_login.php" class="btn btn-primary-header">Admin Login</a>
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <h1 class="hero-title">
    Donate Blood, Save <span id="changing-text">Lives</span>
</h1>

            <p class="typewriter">
    <span id="type-text"></span><span class="cursor">|</span>
</p>

            <div class="cta-buttons">
                <a href="appointments/book_appointment.php" class="btn btn-secondary">Book Appointment</a>
                <a href="requests/add_requests.php" class="btn btn-secondary">Request Blood</a>
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

            <!-- Filter Form for Requests -->
            <form class="filter-form" method="GET">
                <select name="request_blood_group">
                    <option value="">All Blood Groups</option>
                    <option value="A+" <?= $request_filter_blood_group === 'A+' ? 'selected' : ''; ?>>A+</option>
                    <option value="A-" <?= $request_filter_blood_group === 'A-' ? 'selected' : ''; ?>>A-</option>
                    <option value="B+" <?= $request_filter_blood_group === 'B+' ? 'selected' : ''; ?>>B+</option>
                    <option value="B-" <?= $request_filter_blood_group === 'B-' ? 'selected' : ''; ?>>B-</option>
                    <option value="AB+" <?= $request_filter_blood_group === 'AB+' ? 'selected' : ''; ?>>AB+</option>
                    <option value="AB-" <?= $request_filter_blood_group === 'AB-' ? 'selected' : ''; ?>>AB-</option>
                    <option value="O+" <?= $request_filter_blood_group === 'O+' ? 'selected' : ''; ?>>O+</option>
                    <option value="O-" <?= $request_filter_blood_group === 'O-' ? 'selected' : ''; ?>>O-</option>
                </select>
                <input type="text" name="request_place" placeholder="Filter by Hospital/Place" value="<?= htmlspecialchars($request_filter_place); ?>">
                <button type="submit">Filter</button>
            </form>

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

            <!-- Filter Form for Donors -->
            <form class="filter-form" method="GET">
                <select name="donor_blood_group">
                    <option value="">All Blood Groups</option>
                    <option value="A+" <?= $donor_filter_blood_group === 'A+' ? 'selected' : ''; ?>>A+</option>
                    <option value="A-" <?= $donor_filter_blood_group === 'A-' ? 'selected' : ''; ?>>A-</option>
                    <option value="B+" <?= $donor_filter_blood_group === 'B+' ? 'selected' : ''; ?>>B+</option>
                    <option value="B-" <?= $donor_filter_blood_group === 'B-' ? 'selected' : ''; ?>>B-</option>
                    <option value="AB+" <?= $donor_filter_blood_group === 'AB+' ? 'selected' : ''; ?>>AB+</option>
                    <option value="AB-" <?= $donor_filter_blood_group === 'AB-' ? 'selected' : ''; ?>>AB-</option>
                    <option value="O+" <?= $donor_filter_blood_group === 'O+' ? 'selected' : ''; ?>>O+</option>
                    <option value="O-" <?= $donor_filter_blood_group === 'O-' ? 'selected' : ''; ?>>O-</option>
                </select>
                <input type="text" name="donor_place" placeholder="Filter by Place/Address" value="<?= htmlspecialchars($donor_filter_place); ?>">
                <button type="submit">Filter</button>
            </form>

            <div class="donors-grid">
                <?php
                if ($donors_result->num_rows > 0) {
                    while ($row = $donors_result->fetch_assoc()) {
                        echo '<div class="donor-card">
                                <h3>' . htmlspecialchars($row['name']) . '</h3>
                                <div class="donor-body">
                                <p>Age: ' . htmlspecialchars($row['age'] ?: 'Not specified') . '</p>
                                <p>Place: ' . htmlspecialchars($row['place'] ?: 'Not specified') . '</p>
                                <p>Mobile: ' . htmlspecialchars($row['phone']) . '</p>
                                <p class="blood-group">Blood Group: ' . htmlspecialchars($row['blood_group'] ?: 'Not specified') . '</p>
                                </div>
                              </div>';
                    }
                } else {
                    echo '<p style="text-align: center; width: 100%;">No registered donors yet.</p>';
                }
                ?>
            </div>
        </div>
    </section>

    <section id="about-developers" style="
    max-width: 1000px;
    margin: 60px auto;
    padding: 40px 20px;
    background: #ffffffff;
    border-radius: 15px;
    box-shadow: var(--shadow);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #4a4a4a;
">
  <h2 style="text-align: center; color: #c62828; font-size: 2.4rem; font-weight: 800; margin-bottom: 40px; letter-spacing: 1px;">
    About the Developers
  </h2>
  
  <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 25px; margin: 0 auto;">
    
    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(198, 40, 40, 0.15); width: 210px; text-align: center; transition: transform 0.3s ease;" 
         onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
      <img src="Zabed.png" alt="Zabed Mahmud" style="width: 130px; height: 130px; border-radius: 50%; object-fit: cover; margin-bottom: 15px; border: 4px solid #c62828;" />
      <h3 style="margin: 0; color: #c62828; font-size: 1.25rem; font-weight: 700;">Zabed Mahmud</h3>
      <p style="font-weight: 600; color: #c62828; font-size: 0.9rem; margin: 5px 0;">Team Leader</p>
      <p style="font-size: 0.85rem; color: #555; margin: 5px 0;">Full Stack Developer passionate about building impactful projects.</p>
    </div>

    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(198, 40, 40, 0.15); width: 210px; text-align: center; transition: transform 0.3s ease;" 
         onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
      <img src="Ashik.jpeg" alt="Ashik" style="width: 130px; height: 130px; border-radius: 50%; object-fit: cover; margin-bottom: 15px; border: 4px solid #c62828;" />
      <h3 style="margin: 0; color: #c62828; font-size: 1.25rem; font-weight: 700;">Ashikur Rahman</h3>
      <p style="font-weight: 600; color: #c62828; font-size: 0.9rem; margin: 5px 0;">Developer</p>
      <p style="font-size: 0.85rem; color: #555; margin: 5px 0;">Front-end enthusiast focused on user-friendly designs and accessibility.</p>
    </div>

    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(198, 40, 40, 0.15); width: 210px; text-align: center; transition: transform 0.3s ease;" 
         onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
      <img src="Dihan.jpeg" alt="Dil Dihan" style="width: 130px; height: 130px; border-radius: 50%; object-fit: cover; margin-bottom: 15px; border: 4px solid #c62828;" />
      <h3 style="margin: 0; color: #c62828; font-size: 1.25rem; font-weight: 700;">Dil Dihan</h3>
      <p style="font-weight: 600; color: #c62828; font-size: 0.9rem; margin: 5px 0;">Developer</p>
      <p style="font-size: 0.85rem; color: #555; margin: 5px 0;">Back-end specialist ensuring data security and smooth performance.</p>
    </div>
    
    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(198, 40, 40, 0.15); width: 210px; text-align: center; transition: transform 0.3s ease;" 
         onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
      <img src="Sohag.jpeg" alt="Nowshad Sottar" style="width: 130px; height: 130px; border-radius: 50%; object-fit: cover; margin-bottom: 15px; border: 4px solid #c62828;" />
      <h3 style="margin: 0; color: #c62828; font-size: 1.25rem; font-weight: 700;">Nowshad Sottar</h3>
      <p style="font-weight: 600; color: #c62828; font-size: 0.9rem; margin: 5px 0;">Developer</p>
      <p style="font-size: 0.85rem; color: #555; margin: 5px 0;">Dedicated UI/UX designer focused on creating intuitive experiences.</p>
    </div>
  </div>
</section>

    <footer>
        <div class="container">
            &copy; 2025 LifePulse. All rights reserved.
        </div>
    </footer>

    <script>
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
</script>

<script>
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