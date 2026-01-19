# LifePulse - Blood Bank Management System

![LifePulse Logo](https://via.placeholder.com/1200x400?text=LifePulse+Blood+Bank) <!-- Replace with actual logo or banner if available -->

[![GitHub Repo stars](https://img.shields.io/github/stars/Zabedfolio/LifePulse?style=social)](https://github.com/Zabedfolio/LifePulse/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/Zabedfolio/LifePulse?style=social)](https://github.com/Zabedfolio/LifePulse/network/members)
[![License](https://img.shields.io/github/license/Zabedfolio/LifePulse?color=blue)](https://github.com/Zabedfolio/LifePulse/blob/main/LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E=7.4-blueviolet)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/database-MySQL-orange)](https://www.mysql.com/)

LifePulse is a comprehensive Blood Bank Management System designed for efficient donor registration, blood request handling, and inventory management. Built as a Database Management System project, it enables users to register as donors, request blood, and allows admins to manage operations seamlessly. Hosted locally using XAMPP.

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Installation](#installation)
- [Usage](#usage)
- [Database Schema](#database-schema)
- [Screenshots](#screenshots)
- [Contributing](#contributing)
- [License](#license)
- [Contact](#contact)

## Features

- **Donor Management**: Register new donors, edit details, delete records, and view donor lists with filters (blood group, location).
- **Blood Requests**: Submit blood requests, approve/reject as admin, and track status (pending/completed).
- **Blood Stock Inventory**: Automatically update stock levels when donors register or requests are fulfilled.
- **Admin Dashboard**: Overview of total donors, requests, blood units given, and charts for monthly/yearly analytics.
- **Appointment Booking**: Schedule donor appointments (integrated with donor profiles).
- **User-Friendly Interface**: Modern UI with responsive design, animations, and filters for donors/requests.
- **Security**: Admin login, session management, and basic input sanitization.

## Tech Stack

- **Backend**: PHP (with MySQLi for database interactions)
- **Database**: MySQL (via XAMPP)
- **Frontend**: HTML5, CSS3 (with custom styles), JavaScript (for animations and charts via Chart.js)
- **Server**: XAMPP (Apache + MySQL)
- **Libraries**: Chart.js for visualizations

## Installation

### Prerequisites
- XAMPP (or any local server with PHP and MySQL support)
- Git (optional, for cloning the repo)

### Steps
1. **Clone the Repository**:
   git clone https://github.com/Zabedfolio/LifePulse.git
cd LifePulse

2. **Set Up XAMPP**:
- Download and install XAMPP from [apachefriends.org](https://www.apachefriends.org/).
- Start Apache and MySQL modules from the XAMPP Control Panel.

3. **Import Database**:
- Open phpMyAdmin[](http://localhost/phpmyadmin).
- Create a new database named `LifePulse_DB`.
- Import the SQL file from `/database/LifePulse_DB.sql` (if available; otherwise, use the schema below to create tables manually).

4. **Configure Database Connection**:
- Edit `db_connect.php` with your MySQL credentials (default: root with no password).

5. **Run the Application**:
- Move the project folder to `xampp/htdocs/`.
- Access via browser: http://localhost/LifePulse/index.php.

## Usage

### Public Users
- **Register as Donor**: Go to `/add_donor.php` and fill in details.
- **Request Blood**: Use `/add_requests.php` to submit a blood request.
- **View Donors/Requests**: Homepage (`index.php`) shows filtered lists of donors and pending requests.

### Admins
- **Login**: Access `/admin_login.php` (default credentials: username `admin`, password `admin` – change in database for security).
- **Dashboard**: View stats, charts, and manage donors/requests.
- **Manage Donors**: Add/edit/delete via `/manage_donors.php`.
- **Manage Requests**: Approve/reject via `/manage_requests.php`.

For API-like endpoints (e.g., `/get_donors.php`), use tools like Postman for testing.

## Database Schema

Database: `LifePulse_DB`

- **admins**: `admin_id` (PK, auto), `username`, `password`
- **appointments**: `appointment_id` (PK, auto), `donor_id` (FK), `appointment_date`, `status`
- **blood_requests**: `request_id` (PK, auto), `patient_name`, `blood_group`, `units_needed`, `hospital`, `contact`, `status`, `request_date`
- **blood_stock**: `blood_group` (PK), `units_available`
- **donors**: `donor_id` (PK, auto), `name`, `age`, `gender`, `blood_group`, `phone`, `email`, `address`, `last_donation_date`, `created_at`

Run the following SQL to set up (or use provided dump):
```sql
CREATE DATABASE LifePulse_DB;
USE LifePulse_DB;

-- admins table
CREATE TABLE admins (
 admin_id INT AUTO_INCREMENT PRIMARY KEY,
 username VARCHAR(50) NOT NULL,
 password VARCHAR(255) NOT NULL
);

-- appointments table
CREATE TABLE appointments (
 appointment_id INT AUTO_INCREMENT PRIMARY KEY,
 donor_id INT NOT NULL,
 appointment_date DATE NOT NULL,
 status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
 FOREIGN KEY (donor_id) REFERENCES donors(donor_id) ON DELETE CASCADE
);

-- blood_requests table
CREATE TABLE blood_requests (
 request_id INT AUTO_INCREMENT PRIMARY KEY,
 patient_name VARCHAR(100) NOT NULL,
 blood_group VARCHAR(5) NOT NULL,
 units_needed INT NOT NULL,
 hospital VARCHAR(100),
 contact VARCHAR(100) NOT NULL,
 status ENUM('pending', 'completed') DEFAULT 'pending',
 request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- blood_stock table
CREATE TABLE blood_stock (
 blood_group VARCHAR(5) PRIMARY KEY,
 units_available INT DEFAULT 0
);

-- donors table
CREATE TABLE donors (
 donor_id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 age INT,
 gender ENUM('Male', 'Female', 'Other'),
 blood_group VARCHAR(5) NOT NULL,
 phone VARCHAR(20) NOT NULL,
 email VARCHAR(100),
 address VARCHAR(255),
 last_donation_date DATE,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (change password in production)
INSERT INTO admins (username, password) VALUES ('admin', 'admin'); -- Plaintext for demo; hash in real use

Contributing
Contributions are welcome! Follow these steps:

Fork the repository.
Create a new branch (git checkout -b feature/YourFeature).
Commit your changes (git commit -m 'Add YourFeature').
Push to the branch (git push origin feature/YourFeature).
Open a Pull Request.

Please ensure code follows PHP best practices and includes comments.
