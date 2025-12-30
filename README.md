<div align="center">

# LifePulse  
### Blood Bank Management System

A modern, web-based blood bank management system that connects blood donors with patients and hospitals, while enabling administrators to manage donors, requests, appointments, and blood stock efficiently.

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=flat-square&logo=mysql)
![Chart.js](https://img.shields.io/badge/Chart.js-Visualization-FF6384?style=flat-square&logo=chartdotjs)
![XAMPP](https://img.shields.io/badge/XAMPP-Local%20Server-FB7A24?style=flat-square&logo=apache)
![Status](https://img.shields.io/badge/Project-Academic-blue?style=flat-square)

</div>

---

## Overview

**LifePulse** is a web-based blood bank management system developed as part of a **Database Management System (DBMS) course**.  
The project focuses on structured database design, secure data handling, and a user-friendly interface to promote life-saving blood donations.

The system is designed for **local hosting using XAMPP** and demonstrates real-world CRUD operations, admin dashboards, and data visualization.

---

## Key Features

### Donor Registration
- Register donors with personal and medical details
- Blood group, contact information, and last donation date
- Admin-controlled donor management (add, edit, delete)

### Blood Request System
- Patients or hospitals can submit blood requests
- Specify blood group, required units, hospital, and contact details
- Admin can update request status or remove requests

### Admin Dashboard
- Secure admin login
- Dashboard statistics:
  - Total donors
  - Total blood requests
  - Total blood units distributed
- Centralized management panel

### Blood Stock Management
- Real-time tracking of blood availability
- Supported blood groups:
  - A+, A-, B+, B-, AB+, AB-, O+, O-
- Admin-controlled stock updates

### Appointments
- Donors can book appointments (basic implementation)
- Admin can view and manage appointment status

### Statistics & Visualization
- Interactive charts using **Chart.js**
- Blood group distribution
- Monthly and yearly blood usage statistics

### Responsive UI
- Clean and modern interface
- Responsive layout for desktop and mobile
- Cards, tables, and charts for better usability

---

## Tech Stack

### Backend
- PHP
- MySQLi

### Frontend
- HTML5
- CSS3 (CSS variables for theming)
- JavaScript
- Chart.js

### Database
- MySQL  
- Database Name: `LifePulse_DB`

### Server
- XAMPP (Apache + MySQL)

### Security
- Session-based admin authentication
- Basic input sanitization using `htmlspecialchars()`

---

## Database Schema

**Database Name:** `LifePulse_DB`

### Tables

**admins**
- admin_id (PK, AUTO_INCREMENT)
- username
- password

**donors**
- donor_id (PK, AUTO_INCREMENT)
- name
- age
- gender
- blood_group
- phone
- email
- address
- last_donation_date
- created_at

**blood_requests**
- request_id (PK, AUTO_INCREMENT)
- patient_name
- blood_group
- units_needed
- hospital
- contact
- status
- request_date

**blood_stock**
- blood_group (PK)
- units_available

**appointments**
- appointment_id (PK, AUTO_INCREMENT)
- donor_id (FK)
- appointment_date
- status

---

## Installation (Localhost Setup)

1. Install **XAMPP**
2. Clone the repository:
   ```bash
   git clone https://github.com/your-username/LifePulse.git
