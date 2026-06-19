# 🐟 DPA Data Management System

A web-based Fish Cage Management System designed to help administrators and employees manage aquaculture operations efficiently. The system includes scheduling, inventory monitoring, fish growth tracking, expense management, task monitoring, and operational analytics.

---

## 📌 Features

### 👤 Authentication & User Management

* Secure login and session management
* Role-based access control
* Admin and employee accounts
* Employee approval system
* User management dashboard

### 🗓️ Scheduling System

Manage schedules for:

* Fish Cage Management
* Stocking
* Transfers
* Feedings
* Samplings
* Net Cleaning
* Net Checking
* Net Repairing
* Deliveries

Features include:

* Task assignment
* Status tracking
* Upcoming schedule monitoring
* Past due task tracking

### 🐟 Stocking Management

Track fish stocking operations including:

* Date stocked
* Fish type
* Fingerling source
* Standard fingerling size
* Quantity
* Current inventory
* Assigned employee
* Status monitoring

### 🚚 Delivery Management

* Delivery records
* Quantity delivered
* Sales tracking
* Delivery status monitoring

### 📊 Dashboard Analytics

The dashboard provides:

* Total fish inventory
* Monthly sales
* Monthly expenses
* Net profit
* ROI (Return on Investment)
* Task summaries
* Employee overview
* Fish cage monitoring

### 📈 Reports & Monitoring

Charts and analytics powered by Chart.js:

* Monthly expense reports
* Expense categories
* Fish growth analytics
* Cage transfer health monitoring

### 🔔 Notification System

Automatic notifications for:

* Record updates
* Schedule changes
* Operational activities

---

## 🛠️ Technologies Used

### Frontend

* HTML
* CSS
* JavaScript
* Chart.js
* Font Awesome

### Backend

* PHP

### Database

* MySQL

---

## 📂 System Modules

| Module               | Description                     |
| -------------------- | ------------------------------- |
| Dashboard            | Overview and analytics          |
| Fish Cage Management | Cage maintenance and monitoring |
| Stocking             | Fish stocking operations        |
| Transfers            | Fish transfer records           |
| Feedings             | Feeding schedules               |
| Samplings            | Fish growth sampling            |
| Deliveries           | Sales and delivery management   |
| Expenses             | Expense tracking                |
| User Management      | Employee and admin management   |
| Notifications        | System alerts                   |

---

## 🚀 Installation Guide

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/fish-cage-management-system.git
```

### 2. Move Project to Your Server Directory

For XAMPP:

```bash
htdocs/
```

For Laragon:

```bash
www/
```

### 3. Import the Database

1. Open phpMyAdmin
2. Create a new database
3. Import the provided `.sql` file

### 4. Configure Database Connection

Edit:

```php
/auth/config.php
```

Example:

```php
$conn = mysqli_connect("localhost", "root", "", "fish_cage_db");
```

### 5. Run the Project

Open in browser:

```bash
http://localhost/project-folder-name
```

---

## 🔐 User Roles

### Admin

Can:

* Manage all records
* Manage employees
* View analytics and reports
* Assign tasks
* Edit and delete records

### Employee/User

Can:

* View assigned tasks
* Update task status
* Access assigned schedules

---

## 🎯 Project Objective

This project aims to improve fish cage farming operations by providing:

* Centralized monitoring
* Digital inventory management
* Operational scheduling
* Financial monitoring
* Fish growth analytics

---

## 📚 Academic Purpose

This system was developed as an academic/capstone project focused on improving fish cage farming management through a centralized digital platform.

---

## 👨‍💻 Developer

Jaspher Custodio

---

## 📄 License

This project is for educational purposes only.
