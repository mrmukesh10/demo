# NexCore Solutions — Employee Portal

A secure PHP + MySQL web application with user registration, login, profile dashboard, and profile update functionality.

---

## Pages

| Page | File | Description |
|------|------|-------------|
| Register | `register.php` | Create a new account |
| Login | `login.php` | Sign in to your account |
| Dashboard | `dashboard.php` | View your profile details |
| Update | `update.php` | Edit your profile and password |
| Logout | `logout.php` | Sign out |

---

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache (XAMPP recommended for local setup)

---

## Installation

### Step 1 — Place Files
Copy the project folder into your XAMPP htdocs directory:
```
C:\xampp\htdocs\nexcore\
```

### Step 2 — Create the Database
1. Open `http://localhost/phpmyadmin`
2. Click **New** → type `nexcore_db` → click **Create**
3. Click on `nexcore_db` → go to the **SQL** tab
4. Open `setup.sql`, copy everything, paste it in the SQL box → click **Go**

### Step 3 — Configure Database
Open `db.php` and update these lines with your credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nexcore_db');
```

### Step 4 — Open the App
Go to this URL in your browser:
```
http://localhost/nexcore/register.php
```

---

## File Structure

```
nexcore/
├── db.php           → Database config + session + CSRF helpers
├── setup.sql        → Run once to create database and table
├── register.php     → User registration page
├── login.php        → Login page
├── dashboard.php    → Profile display page
├── update.php       → Profile edit page
├── logout.php       → Session logout
└── style.css        → Shared design system
```

---

## Security Features

- Passwords hashed with bcrypt (cost 12)
- All queries use prepared statements (no SQL injection)
- CSRF tokens on every form
- Session regeneration on login
- XSS protection via htmlspecialchars() on all output
- httponly session cookies

---

## Tech Stack

- **Backend:** PHP 7.4+
- **Database:** MySQL with mysqli
- **Frontend:** HTML5, CSS3 (no framework)
- **Fonts:** Google Fonts (Inter + Space Grotesk)

---

## Live Deployment

To host this online so others can use it:

**Free — InfinityFree**
1. Sign up at https://infinityfree.com
2. Upload all files via their File Manager
3. Create a MySQL database from their Control Panel
4. Import `setup.sql` via their phpMyAdmin
5. Update `db.php` with their database credentials

**Paid — Hostinger (~Rs. 69/month)**
1. Buy Single Web Hosting at https://hostinger.in
2. Upload files to `public_html` via cPanel File Manager
3. Create MySQL database via cPanel
4. Import `setup.sql` and update `db.php`

---

## Note

Before deploying live, open `db.php` and uncomment this line to enable secure cookies over HTTPS:
```php
ini_set('session.cookie_secure', 1);
```

---

## Author

NexCore Solutions Dev Team
