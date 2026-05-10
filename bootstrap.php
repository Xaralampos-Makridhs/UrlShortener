<?php

// Load Composer dependencies
require_once __DIR__ . '/vendor/autoload.php';

// Check if the .env configuration file exists
if (!file_exists(__DIR__ . '/.env')) {

    // Stop execution if the .env file is missing
    throw new Exception('.env file not found');
}

// Create Dotenv instance using the project root directory
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);

// Load environment variables from the .env file
$dotenv->load();

// Load the database configuration class
require_once __DIR__ . '/Config/Database.php';

// Enable HTTP-only session cookies
// This helps prevent JavaScript from accessing the session cookie
ini_set('session.cookie_httponly', 1);

// Enable strict session mode
// This helps prevent session fixation attacks
ini_set('session.use_strict_mode', 1);

// Start session only if no session is already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create database class instance
$database = new Database();

// Get PDO database connection
$conn = $database->getConnection();

// Stop execution if database connection fails
if (!$conn) {
    die('Database connection failed.');
}