# FundACause

A modern charity/donations platform where people can create fundraising campaigns, upload media, and accept PayPal donations. Built with **PHP**, **JavaScript (AJAX)**, and **Tailwind CSS**.

## Features

- **Campaign Creation** — Upload multiple images/videos, describe your cause, enter the beneficiary name, and set a fundraising goal
- **Unique Campaign URLs** — Each campaign gets an 8-digit hex ID (e.g., `/a1b2c3d4`)
- **PayPal Integration** — Donors are redirected to PayPal to make payments to the campaign creator's PayPal email
- **Donation Tracking** — Real-time progress bars, supporter counts, and recent donor lists
- **Image/Video Carousel** — Swipeable media gallery with counter (powered by Swiper.js)
- **Responsive Design** — Mobile-first with hamburger menu, full-width donate button on mobile
- **Admin Dashboard** — Campaign management with create, edit, delete, and stats
- **User Management** — Admins can activate, disable, and promote/demote users
- **Approval Workflow** — New users are set to "pending" until an admin approves them
- **Role-Based Access** — Admin and user roles (only admins see the Manage Users page)
- **User Authentication** — Secure login & registration with password hashing and CSRF protection
- **Password Change** — Logged-in users can update their password from the dashboard
- **Password Recovery** — Forgot-password flow generates a time-limited reset link (1 hour)
- **Static Pages** — Mission, Terms of Service, Privacy Policy

## Tech Stack

- **Backend:** PHP 7.4+ with PDO/MySQL
- **Frontend:** Tailwind CSS (CDN), Vanilla JavaScript, Swiper.js
- **Database:** MySQL 5.7+ / MariaDB
- **Payments:** PayPal Donations (redirect flow)

## Installation

### Requirements
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB
- Apache with mod_rewrite enabled
- PHP extensions: pdo, pdo_mysql

### Quick Setup

1. **Clone/copy** the project to your web server directory:
   ```
   /var/www/html/fundacause/   (Linux)
   /Applications/MAMP/htdocs/fundacause/   (macOS with MAMP)
   ```

2. **Configure database** — Edit `config/database.php` with your MySQL credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'fundacause');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('SITE_URL', 'http://localhost/fundacause');
   ```

3. **Run installer** — Navigate to `http://localhost/fundacause/install.php` in your browser. This creates the database, tables, and a default admin user.

   > If you are upgrading from an older version, drop and recreate the `fundacause` database (or run a migration) so the new columns exist: `beneficiary_name` in campaigns, and `role`, `status`, `recovery_token`, `recovery_expires` in users.

4. **Login** with default credentials:
   - Username: `admin`
   - Password: `admin123`

5. **Delete `install.php`** after installation for security.

### Manual Database Setup (Alternative)
```bash
mysql -u root -p < config/schema.sql
```

## Project Structure

```
fundacause/
├── .htaccess              # URL routing (clean URLs)
├── index.php              # Homepage
├── campaign.php           # Public campaign page
├── donation-success.php   # Post-donation thank you page
├── install.php            # Database installer (delete after setup)
├── config/
│   ├── database.php       # DB connection & site config
│   └── schema.sql         # Database schema
├── includes/
│   ├── auth.php           # Authentication functions
│   ├── helpers.php        # Utility functions
│   ├── header.php         # Public header/nav template
│   └── footer.php         # Public footer template
├── admin/
│   ├── index.php          # Admin dashboard
│   ├── login.php          # Login page
│   ├── register.php       # Registration page
│   ├── logout.php         # Logout handler
│   ├── users.php          # User management (admin only)
│   ├── change-password.php # Change password form
│   ├── forgot-password.php # Forgot password (generates reset link)
│   ├── reset-password.php # Reset password via token
│   ├── campaign-create.php # Create campaign form
│   └── campaign-edit.php  # Edit campaign form
├── api/
│   ├── donate.php         # Record donation (AJAX)
│   ├── campaign-delete.php # Delete campaign (AJAX)
│   └── campaign-data.php  # Get campaign data (AJAX)
├── pages/
│   ├── mission.php        # Our Mission page
│   ├── terms.php          # Terms of Service
│   └── privacy.php        # Privacy Policy
├── assets/
│   └── logo.svg           # Site logo
└── uploads/
    └── campaigns/         # Uploaded campaign media
```

## URL Structure

| URL | Description |
|-----|-------------|
| `/` | Homepage with active campaigns |
| `/[hex_id]` | Campaign page (e.g., `/a1b2c3d4`) |
| `/mission` | Our Mission page |
| `/terms` | Terms of Service |
| `/privacy` | Privacy Policy |
| `/admin/` | Admin dashboard |
| `/admin/login.php` | Admin login |
| `/admin/users.php` | User management (admin) |
| `/admin/change-password.php` | Change password |
| `/admin/forgot-password.php` | Forgot password |
| `/admin/reset-password.php` | Reset password via token |

## PayPal Integration

The platform uses PayPal's donation redirect flow:
1. Donor clicks "Donate Now" and selects an amount
2. They're redirected to PayPal with the campaign creator's email pre-filled
3. After payment, they return to the success page
4. The donation is recorded via AJAX

For production, consider implementing PayPal IPN (Instant Payment Notification) for more reliable tracking.

## Security

- Passwords are hashed with `password_hash()` (bcrypt)
- CSRF tokens on all forms
- Prepared statements for all SQL queries
- HTML output escaping with `htmlspecialchars()`
- File upload validation (type, size)

## License

MIT
