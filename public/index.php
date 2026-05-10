<?php

// Load the main application bootstrap file
require_once __DIR__ . '/../bootstrap.php';

// Load the service responsible for short link management
require_once __DIR__ . '/../LinkServices/ShortLinkService.php';

// Load the service responsible for click tracking
require_once __DIR__ . '/../LinkServices/ClickTrackingService.php';

// Create short link service instance
$shortLinkService = new ShortLinkService($conn);

// Create click tracking service instance
$clickTrackingService = new ClickTrackingService($conn);

// Get short code from URL query parameter
// Example: index.php?code=abc123
$shortCode = $_GET['code'] ?? '';

// Remove extra spaces from the short code
$shortCode = trim($shortCode);

// If no short code is provided, display simple homepage message
if ($shortCode === '') {
    echo 'URL Shortener Home';
    exit;
}

// Search database for the short link using the provided code
$link = $shortLinkService->findByCode($shortCode);

// If the short link does not exist or is inactive/expired
if (!$link) {

    // Return HTTP 404 Not Found status
    http_response_code(404);

    // Display error message
    echo 'Short link not found';

    exit;
}

// Store click tracking information for analytics
$clickTrackingService->track((int) $link['id']);

// Redirect visitor to the original destination URL
// 302 means temporary redirect
header('Location: ' . $link['original_url'], true, 302);

exit;