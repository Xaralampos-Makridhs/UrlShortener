<?php

// Load the main application bootstrap file
require_once __DIR__ . '/../bootstrap.php';

// Load the authentication service
require_once __DIR__ . '/../Services/AuthService.php';

// Load the service responsible for managing short links
require_once __DIR__ . '/../LinkServices/ShortLinkService.php';

// Create authentication service instance
$auth = new AuthService($conn);

// Create short link service instance
$shortLinkService = new ShortLinkService($conn);

// Check if the user is logged in
if (!$auth->check()) {
    // Redirect unauthenticated users to the login page
    header('Location: login.php');
    exit;
}

// Allow only POST requests for this action
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect users back to dashboard if request method is invalid
    header('Location: dashboard.php');
    exit;
}

// Get the currently logged-in user
$user = $auth->user();

// Get link ID from submitted form data
// If link_id does not exist, use 0 as fallback value
$linkId = isset($_POST['link_id']) ? (int) $_POST['link_id'] : 0;

// Delete the link only if the link ID is valid and the user exists
if ($linkId > 0 && $user) {

    // Call service method to permanently delete the link
    // and its related click tracking records
    $shortLinkService->delete(
        $linkId,
        (int) $user['id']
    );
}

// Redirect the user back to the dashboard page
header('Location: dashboard.php');
exit;