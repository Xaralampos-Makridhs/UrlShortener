<?php

// Load the main application bootstrap file
require_once __DIR__ . '/../bootstrap.php';

// Load the authentication service
require_once __DIR__ . '/../Services/AuthService.php';

// Load the service responsible for managing short links
require_once __DIR__ . '/../LinkServices/ShortLinkService.php';

// Load the service responsible for click tracking and analytics
require_once __DIR__ . '/../LinkServices/ClickTrackingService.php';

// Create authentication service instance
$auth = new AuthService($conn);

// Create short link service instance
$shortLinkService = new ShortLinkService($conn);

// Create click tracking service instance
$clickTrackingService = new ClickTrackingService($conn);

// Check if the user is logged in
if (!$auth->check()) {
    // Redirect unauthenticated users to the login page
    header('Location: login.php');
    exit;
}

// Get the currently logged-in user
$user = $auth->user();

// Get link ID from URL query parameter
// If link_id is missing, use 0 as fallback
$linkId = isset($_GET['link_id']) ? (int) $_GET['link_id'] : 0;

// Validate link ID and user data
if ($linkId <= 0 || !$user) {
    // Redirect to dashboard if link ID is invalid or user is missing
    header('Location: dashboard.php');
    exit;
}

// Get all links that belong to the current user
$links = $shortLinkService->getUserLinks((int) $user['id']);

// Variable that will store the selected link
$currentLink = null;

// Search through user's links to find the requested link
foreach ($links as $link) {
    // Compare current link ID with requested link ID
    if ((int) $link['id'] === $linkId) {
        // Store matching link
        $currentLink = $link;
        break;
    }
}

// If the requested link does not belong to the user, redirect away
if (!$currentLink) {
    header('Location: dashboard.php');
    exit;
}

// Get total number of clicks for this short link
$totalClicks = $clickTrackingService->getTotalClicks($linkId);

// Get the 10 most recent clicks for this short link
$recentClicks = $clickTrackingService->getRecentClicks($linkId, 10);

// Get click statistics grouped by browser
$clicksByBrowser = $clickTrackingService->getClicksByBrowser($linkId);

// Get click statistics grouped by device type
$clicksByDevice = $clickTrackingService->getClicksByDevice($linkId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <!-- Page title displayed in the browser tab -->
    <title>Analytics</title>
</head>
<body>

<!-- Main page heading -->
<h1>Analytics</h1>

<p>
    <!-- Link back to user dashboard -->
    <a href="dashboard.php">Dashboard</a>
</p>

<h2>
    <!-- Display link title if available, otherwise display short code -->
    <?= htmlspecialchars($currentLink['title'] ?: $currentLink['short_code']) ?>
</h2>

<p>
    <strong>Short URL:</strong>

    <!-- Link to the short URL redirect page -->
    <a href="index.php?code=<?= urlencode($currentLink['short_code']) ?>" target="_blank">
        <!-- Display short code safely -->
        <?= htmlspecialchars($currentLink['short_code']) ?>
    </a>
</p>

<p>
    <strong>Original URL:</strong>

    <!-- Link to the original destination URL -->
    <a href="<?= htmlspecialchars($currentLink['original_url']) ?>" target="_blank">
        <!-- Display original URL safely -->
        <?= htmlspecialchars($currentLink['original_url']) ?>
    </a>
</p>

<!-- Total clicks section -->
<h3>Total Clicks</h3>

<!-- Display total clicks as integer -->
<p><?= (int) $totalClicks ?></p>

<!-- Browser statistics section -->
<h3>Clicks By Browser</h3>

<?php if (empty($clicksByBrowser)): ?>

    <!-- Message displayed when no browser data exists -->
    <p>No browser data yet.</p>

<?php else: ?>

    <!-- Table showing clicks grouped by browser -->
    <table border="1" cellspacing="0" cellpadding="8">
        <thead>
        <tr>
            <th>Browser</th>
            <th>Total</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($clicksByBrowser as $row): ?>
            <tr>
                <!-- Display browser name safely -->
                <td><?= htmlspecialchars($row['browser'] ?? 'Unknown') ?></td>

                <!-- Display number of clicks for this browser -->
                <td><?= (int) $row['total'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>

<!-- Device statistics section -->
<h3>Clicks by Device</h3>

<?php if (empty($clicksByDevice)): ?>

    <!-- Message displayed when no device data exists -->
    <p>No device data yet.</p>

<?php else: ?>

    <!-- Table showing clicks grouped by device type -->
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
        <tr>
            <th>Device</th>
            <th>Total</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($clicksByDevice as $row): ?>
            <tr>
                <!-- Display device type safely -->
                <td><?= htmlspecialchars($row['device'] ?? 'Unknown') ?></td>

                <!-- Display number of clicks for this device -->
                <td><?= (int) $row['total'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>

<!-- Recent clicks section -->
<h3>Recent Clicks</h3>

<?php if (empty($recentClicks)): ?>

    <!-- Message displayed when there are no clicks yet -->
    <p>No clicks yet.</p>

<?php else: ?>

    <!-- Table showing recent click details -->
    <table border="1" cellspacing="0" cellpadding="8">
        <thead>
        <tr>
            <th>IP</th>
            <th>Browser</th>
            <th>Device</th>
            <th>Referer</th>
            <th>Clicked At</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($recentClicks as $click): ?>
            <tr>
                <!-- Display visitor IP address safely -->
                <td><?= htmlspecialchars($click['ip_address'] ?? '-') ?></td>

                <!-- Display detected browser safely -->
                <td><?= htmlspecialchars($click['browser'] ?? 'Unknown') ?></td>

                <!-- Display detected device safely -->
                <td><?= htmlspecialchars($click['device'] ?? 'Unknown') ?></td>

                <!-- Display referring page safely -->
                <td><?= htmlspecialchars($click['referer'] ?? '-') ?></td>

                <!-- Display click timestamp safely -->
                <td><?= htmlspecialchars($click['clicked_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>

</body>
</html>