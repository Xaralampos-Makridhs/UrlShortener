# URL Shortener Application

A simple and secure URL Shortener web application built with PHP.

This project allows users to create shortened URLs, manage their links, track analytics, and monitor visitor activity through a clean dashboard interface.

---

# Features

## Authentication System

- User registration
- User login
- Secure logout
- Session-based authentication
- Password hashing using `password_hash()`
- Session security protection

---

## URL Shortening

- Create short links from long URLs
- Optional custom short codes
- Automatic unique short code generation
- Optional link titles
- Optional expiration dates
- Link activation/deactivation
- Permanent link deletion

---

## Analytics System

Track detailed click analytics for every short link:

- Total clicks
- Browser statistics
- Device statistics
- Recent click history
- Visitor IP tracking
- Referer tracking
- User-Agent tracking

---

# Technologies Used

- PHP 8+
- MySQL
- PDO
- Composer
- vlucas/phpdotenv

---

# Project Structure

```bash
project-root/
│
├── Config/
│   └── Database.php
│
├── LinkServices/
│   ├── ClickTrackingService.php
│   └── ShortLinkService.php
│
├── Services/
│   └── AuthService.php
│
├── public/
│   ├── analytics.php
│   ├── dashboard.php
│   ├── deactive_link.php
│   ├── delete_link.php
│   ├── index.php
│   ├── login.php
│   ├── logout.php
│   └── register.php
│
├── vendor/
│
├── .env
├── bootstrap.php
├── composer.json
└── README.md
```

---

# Installation

## 1. Clone the repository

```bash
git clone https://github.com/your-username/url-shortener.git
```

---

## 2. Move into the project directory

```bash
cd url-shortener
```

---

## 3. Install dependencies

```bash
composer install
```

---

## 4. Create `.env` file

Example:

```env
DB_HOST=localhost
DB_NAME=urlshortener
DB_USER=root
DB_PASS=password
```

---

# Running the Project

Run the built-in PHP development server:

```bash
php -S localhost:8000 -t public
```

Then open:

```text
http://localhost:8000
```

---

# Security Features

- Prepared SQL statements using PDO
- Password hashing
- Session regeneration on login
- Secure session cookies
- XSS protection using `htmlspecialchars()`
- Input validation
- Session fixation protection

---

# Main Services

## AuthService

Handles:

- User registration
- Login
- Logout
- Session authentication
- Current user retrieval

---

## ShortLinkService

Handles:

- Short link creation
- Unique short code generation
- Link retrieval
- Link deletion
- Link deactivation
- Validation

---

## ClickTrackingService

Handles:

- Click tracking
- Browser detection
- Device detection
- Analytics reporting
- Click history retrieval

---

# License

This project is open-source and available under the MIT License.
