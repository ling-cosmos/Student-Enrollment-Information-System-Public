# Student & Enrollment Information System

SEIS is a PHP and MySQL web application for managing students, courses,
academic terms, enrollments, reports, and administrator accounts.

## Features

- Student profiles and records
- Course and capacity management
- Semester-based enrollment and history
- Dashboard statistics, trends, and recent activity
- Enrollment, student, and course reports
- Role-based user administration
- Responsive desktop and mobile interface

## Requirements

- PHP 8.2 or newer with PDO MySQL enabled
- MySQL or MariaDB
- A local web stack such as XAMPP, or PHP's built-in development server

## Local setup

1. Clone the repository and enter its directory.
2. Start MySQL or MariaDB.
3. Create the database by importing `seis.sql` through phpMyAdmin or the
   command line:

   ```bash
   mysql -u root -p < seis.sql
   ```

4. Update the connection values in `config/database.php` when your database
   username or password differs from the XAMPP defaults.
5. Start the application from the repository root:

   ```bash
   php -S localhost:8000
   ```

6. Open <http://localhost:8000>.

No administrator credentials are included in this public repository. Before
first use, create a local administrator record with a unique password generated
through PHP's `password_hash()` function. Never commit real credentials.

## Project structure

```text
admin/       Dashboard and administration pages
assets/      Styles, scripts, and images
auth/        Authentication pages
config/      Application and database configuration
handlers/    Form and database action handlers
includes/    Shared layout and utility code
seis.sql     Database schema and seed data
```

## Security note

The included settings target a local XAMPP installation. For deployment, use a
dedicated database account, a strong password, HTTPS, and environment-specific
configuration. Do not commit production credentials.
