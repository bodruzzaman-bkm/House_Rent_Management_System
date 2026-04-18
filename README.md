# House Rent Management System
**Course:** CSE-370 (Database Systems) | **Institution:** BRAC University

## 📖 Project Overview
The House Rent Management System is a centralized web application designed to bridge the gap between flat owners and tenants, tailored for local rental structures. It serves a dual purpose: acting as a searchable marketplace for available properties and functioning as a comprehensive management dashboard for active tenancies. 

Owners can easily list their flats, confirm tenants, generate itemized monthly utility bills, and track maintenance issues. Tenants are provided with a dedicated portal to search for housing using specific filters, securely view their monthly rental and utility breakdowns, and report physical flat issues directly to the owner. The system ensures transparency by tracking advance security deposits and generating digital payment receipts.

---

## 🚀 Key Features

### 👤 Member 1: User Management & Marketplace Module
* **Role-Based Registration & Login:** A secure authentication system routing Flat Owners and Tenants to their respective dedicated dashboards using PHP sessions.
* **To-Let Advertisement Posting (Owner):** A dynamic form for Owners to list new flats, capturing details like location area, square footage, bedrooms, and asking rent. Flats are saved with a default 'Available' status.
* **Advanced Search & Filter (Tenant):** A public-facing gallery allowing Tenants to search for 'Available' flats. Includes a robust filter engine using SQL `WHERE` and `AND` clauses to narrow results by area, maximum budget, and size.

### 👤 Member 2: Tenancy Confirmation & Support Module
* **Rental Request & Confirmation System:** Tenants can express interest via an "I am interested" button. Owners review these requests and click "Confirm" to officially link the Tenant to the flat, automatically updating the flat's status to 'Rented'.
* **Advance Payment (Security Deposit) Log:** A transparent ledger where Owners input the initial security deposit/advance money received. This ledger is permanently visible on the Tenant's dashboard.
* **Maintenance & Issue Reporting:** A "Complain Box" feature allowing Tenants to report physical flat issues (e.g., plumbing, electrical). Owners track these active tickets and mark them as 'Solved' upon resolution.

### 👤 Member 3: Core Billing & Financial Module
* **Monthly Bill Generation (Owner):** A billing engine allowing Owners to input monthly fluctuating utilities (gas, electricity, water, service charges). The backend calculates the total payable amount alongside the base rent.
* **Tenant Billing Dashboard:** A detailed financial view for Tenants, fetching their current month's bill via SQL `JOIN`s to display a clear, itemized breakdown of rent and specific utilities.
* **Payment Confirmation & Digital Receipt:** A management tool for Owners to mark pending bills as 'Paid'. This action automatically generates a digital payment receipt accessible from the Tenant's portal.

---

## 📊 Database Schema (EER Diagram)
Below is the Enhanced Entity-Relationship (EER) diagram illustrating the database architecture, including the inheritance (IS-A) relationships for users and the associative entities linking owners, tenants, and flats.

![System EER Diagram](eer_diagram.png)

---

## 🛠️ Technologies Used
* **Frontend:** HTML5, CSS3, JavaScript
* **Backend:** PHP (PDO)
* **Database:** MySQL
* **Version Control:** Git & GitHub

## ⚙️ Setup Instructions
1. Clone this repository to your local machine (place it in the `htdocs` folder if using XAMPP).
2. Import the database schema located in the `database/` folder (e.g., `house_rent_db.sql`) into your phpMyAdmin.
3. Configure your database connection credentials inside the `config/db_connect.php` file.
4. Launch your local server and access the project via your browser at `localhost/your-repository-name`.