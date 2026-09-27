# Little Steps Daycare Centre - Management System

A web-based management system developed to streamline daycare operations, child enrollment, staff assignments, and billing for **Little Steps Daycare Centre**. Built with a native **PHP (PDO)** backend, **MySQL/MariaDB**, and a responsive frontend interface.

---

## 📌 Project Overview

**Little Steps Daycare System** bridges the communication and operational gap between daycare administrators, parents/guardians, and certified caregivers. The platform handles end-to-end workflows including child enrollment with document verification, calendar-based day booking with dynamic fee calculation, transparent financial tracking, caregiver scheduling, and comprehensive child developmental logs.

---

## 🚀 Key Features by User Role

### 1. 🛡️ Administrator Portal
* **Live Operational Metrics**: Interactive dashboard calculating total children enrolled, registered caregivers, gross billed amounts, total collections, outstanding balances, and real-time collection rates.
* **Collapsible Management Sidebar**: Responsive navigation with persistent state toggle.
* **Staff Onboarding**: Admin-controlled caregiver registration with credential tracking, experience verification, profile photo uploads, and CSRF token protection.
* **Caregiver-Child Assignment**: Interactive modal to link children directly to primary and supporting caregivers (`child_caregiver` junction table).
* **Payment & Invoice Oversight**: Complete audit trail of invoices, manual payment status reconciliation, and verification of digital receipt documents.

### 2. 👨‍👩‍👧 Parent & Guardian Portal
* **Account Onboarding**: Self-service registration with secure credential storage via `password_hash()`.
* **Child Enrollment**: Multi-part registration requiring legal guardian verification and file uploads (Birth Certificates, Immunization records, and optional Medical reports).
* **Automated Day Booking**: Interactive multi-date calendar picker powered by Flatpickr, calculating fee schedules dynamically ($20/day rate).
* **Financial Checkout & Billing**: Real-time review of unsettled balances, digital checkout simulation, automated status clearing, and printable booking receipts.
* **Comprehensive Child Dossier**: Access to longitudinal tracking logs:
  * **Learning & Development**: Academic milestone reviews and progress notes.
  * **Dietary Records**: Daily meal plans, nutritional logging, and allergen alerts.
  * **Transit History**: Arrival (pick-up) and departure (drop-off) tracking with timestamped notification logs.
  * **Daily Engagement**: Activity and creative engagement monitoring.

### 3. 👩‍⚕️ Caregiver / Staff Portal
* **Dedicated Authentication**: Isolated login pathway cross-referencing user credentials and staff profile credentials.
* **Staff Profile Dashboard**: Overview of professional credentials, certifications, tenure, and specialty domains.
* **Assigned Dependents Roster**: Direct access to dossiers and emergency medical background for children under their direct supervision.
* **Public Staff Directory**: Searchable caregiver roster searchable by name and specialization.

---

## 🛠️ Technology Stack

| Layer | Technology |
| :--- | :--- |
| **Backend** | Native PHP 8.x (Object-Oriented Database Interaction via PDO) |
| **Database** | MySQL / MariaDB (Relational schema with Foreign Key Constraints) |
| **Frontend** | Semantic HTML5, Custom CSS3 (BEM naming, CSS Grid, Flexbox) |
| **Scripting** | Vanilla JavaScript (ES6+, DOM Manipulation, LocalStorage State Sync) |
| **Third-Party Libraries** | • **Flatpickr** (Multi-Date Selection Calendar)<br>• **Font Awesome 6** & **Unicons** (Vector UI Icons)<br>• **AOS (Animate On Scroll)** (Hero page animations)<br>• **FPDF v1.9** (PDF Document Engine) |
| **Server Environment**| Apache (XAMPP / WampServer) |

---

## 🗄️ Database Architecture

The system operates on the relational database `day_care`, featuring normalized structures and strict referential integrity (`ON DELETE CASCADE / RESTRICT`):

* `users`: Core authentication table (Admins, Parents, Caregivers) storing hashed credentials.
* `caregivers`: Professional staff attributes (experience, qualifications, certifications).
* `child`: Detailed dependent profiles (DOB, medical history, document uploads).
* `child_caregiver`: Junction table resolving many-to-many child-to-caregiver assignments.
* `bill_payment`: Invoice ledger tracking itemized descriptions, total amounts, paid balances, and payment statuses (`Paid`, `Pending`, `Overdue`).
* `learning`: Academic progress tracking logs.
* `meal`: Nutrition and allergen management logs.
* `pickdrop`: Transit safety timestamps and drop-off verification.
* `activity`: Classroom engagement and creative activity journals.
* `notification`: System alerts and parent broadcast records.

---

## 💻 Installation & Setup (Local Environment)

### Prerequisites
* **XAMPP** (PHP 8.0 or higher + Apache + MySQL/MariaDB)
* Git installed on your system

### Step-by-Step Installation

1. **Clone the Repository** into your local web root directory:
   ```bash
   cd C:/xampp/htdocs
   git clone https://github.com/draunidalowainise17-beep/littlesteps.git