<p align="center">
  <img src="logo.png" alt="Event System Logo" width="80" />
</p>

<h1 align="center">Event Transaction System</h1>

<p align="center">
  A user-friendly platform to manage events, track user payments, and handle administrative control with ease.
</p>

<hr>

## 📌 Overview

The **Event Transaction System** is a web-based application built for institutions or organizations to manage upcoming events, monitor user participation, and verify payments. It offers a two-sided interface for both administrators and users, ensuring seamless coordination and real-time updates.

---

## 🛠️ Admin Features

- **Secure Admin Login**  
  Restricted access for admins to manage event and user data.

- **Event Management (Add/Edit/Delete)**  
  Admins can create new events, update details, or remove outdated ones.

- **User Verification Tracker**  
  Displays if registered users are "Verified" or "Not Verified", helping admins monitor who can access services.

---

## 👤 User Dashboard

- **User Registration and Login**  
  Users can create an account and log in to access their dashboard.

- **Upcoming Events Table**  
  View all upcoming events with important details:
  - Event ID  
  - Event Name  
  - Event Date  
  - Fee  
  - Status (Paid/Not Paid)  
  - Payment History  

- **Search Bar**  
  Filter events by name or date for quick navigation.

- **Payment Actions**  
  - If **Not Paid**, users can click **Add Payment**.  
  - If **Paid**, users can view a **timestamped receipt** of the transaction.

---

## 🧾 Sample Table (User View)

| Event ID | Event Name       | Event Date           | Fee        | Status    | Your Payment History        |
|----------|------------------|----------------------|------------|-----------|-----------------------------|
| 1        | Music Festival   | 2025-07-10 18:00:00  | ₱200.00    | Paid      | ₱200.00 (2025-05-21)        |
| 4        | Cooking Class    | 2025-05-15 15:00:00  | ₱150.00    | Not Paid  | Add Payment                 |
| 7        | Comedy Night     | 2025-07-02 20:00:00  | ₱40.00     | Not Paid  | Add Payment                 |

---

## ⚙️ Technologies Used

- PHP
- MySQL (or MariaDB)
- HTML/CSS/JavaScript
- Bootstrap (optional for styling)
- Localhost or any PHP-compatible hosting (e.g., XAMPP)

---

## 🚀 Installation

1. Clone or download the repository.
2. Place the project folder in your local server (`htdocs` for XAMPP).
3. Import the SQL database file (if available) into phpMyAdmin.
4. Start Apache and MySQL via your control panel.
5. Access via `http://localhost/your-folder-name`.

---

## ✅ Status

- Admin CRUD ✅  
- User login/register ✅  
- Payment tracking ✅  
- Search/filter ✅  
- User verification tracker ✅  

---

## 🧑‍💻 Developed by **huwamee** **&** **The Tito's**

---

## 📄 License

MIT License © 2025 huwamee  

Permission is hereby granted, free of charge, to any person obtaining a copy  
of this tool and associated documentation files (the “Software”), to deal  
in the Software without restriction, including without limitation the rights  
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell  
copies of the Software, and to permit persons to whom the Software is  
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all  
copies or substantial portions of the Software.

**The Software is provided “as is”, without warranty of any kind, express or  
implied, including but not limited to the warranties of merchantability,  
fitness for a particular purpose and noninfringement. In no event shall the  
authors or copyright holders be liable for any claim, damages or other  
liability, whether in an action of contract, tort or otherwise, arising from,  
out of or in connection with the Software or the use or other dealings in  
the Software.**
