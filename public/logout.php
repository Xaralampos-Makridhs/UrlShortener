<?php

// Load the main application bootstrap file
require_once __DIR__ . '/../bootstrap.php';

// Load the authentication service
require_once __DIR__ . '/../Services/AuthService.php';

// Create authentication service instance
$auth = new AuthService($conn);

// Log out the currently authenticated user
// This usually destroys the session and removes authentication data
$auth->logout();

// Redirect the user to the login page after logout
header('Location: login.php');

// Stop further script execution
exit;