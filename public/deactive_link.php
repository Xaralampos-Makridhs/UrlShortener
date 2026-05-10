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
    // Redirect users back to dashboard if request method is not POST
    header('Location: dashboard.php');
    exit;
}

// Get the currently logged-in user
$user = $auth->user();

// Get link ID from submitted form data
// If link_id is missing, use 0 as fallback
$linkId = isset($_POST['link_id']) ? (int) $_POST['link_id'] : 0;

// Deactivate the link only if the link ID is valid and the user exists
if ($linkId > 0 && $user) {
    // Call service method to deactivate the selected link for this user
    $shortLinkService->deactivate(
        $linkId,
        (int) $user['id']
    );
}

// Redirect user back to dashboard after the action is completed
header('Location: dashboard.php');
exit;