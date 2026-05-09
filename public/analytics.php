<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../Services/AuthService.php';
require_once __DIR__ . '/../LinkServices/ShortLinkService.php';
require_once __DIR__ . '/../LinkServices/ClickTrackingService.php';

$auth = new AuthService($conn);
$shortLinkService = new ShortLinkService($conn);
$clickTrackingService = new ClickTrackingService($conn);

if (!$auth->check()) {
    header('Location: login.php');
    exit;
}

$user = $auth->user();

$linkId = isset($_GET['link_id']) ? (int) $_GET['link_id'] : 0;

if ($linkId <= 0 || !$user) {
    header('Location: dashboard.php');
    exit;
}

$links = $shortLinkService->getUserLinks((int) $user['id']);

$currentLink = null;

foreach ($links as $link) {
    if ((int) $link['id'] === $linkId) {
        $currentLink = $link;
        break;
    }
}

if (!$currentLink) {
    header('Location: dashboard.php');
    exit;
}

$totalClicks = $clickTrackingService->getTotalClicks($linkId);
$recentClicks = $clickTrackingService->getRecentClicks($linkId, 10);
$clicksByBrowser = $clickTrackingService->getClicksByBrowser($linkId);
$clicksByDevice = $clickTrackingService->getClicksByDevice($linkId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analytics</title>
</head>
<body>

<h1>Analytics</h1>

<p>
    <a href="dashboard.php">Dashboard</a>
</p>

<h2>
    <?= htmlspecialchars($currentLink['title'] ?: $currentLink['short_code']) ?>
</h2>

<p>
    <strong>Short URL:</strong>
    <a href="index.php?code=<?= urlencode($currentLink['short_code']) ?>" target="_blank">
        <?= htmlspecialchars($currentLink['short_code']) ?>
    </a>
</p>

<p>
    <strong>Original URL:</strong>
    <a href="<?= htmlspecialchars($currentLink['original_url']) ?>" target="_blank">
        <?= htmlspecialchars($currentLink['original_url']) ?>
    </a>
</p>

<h3>Total Clicks</h3>

<p><?= (int) $totalClicks ?></p>

<h3>Clicks By Browser</h3>

<?php if (empty($clicksByBrowser)): ?>

    <p>No browser data yet.</p>

<?php else: ?>

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
                <td><?= htmlspecialchars($row['browser'] ?? 'Unknown') ?></td>
                <td><?= (int) $row['total'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>

<h3>Clicks by Device</h3>

<?php if (empty($clicksByDevice)): ?>

    <p>No device data yet.</p>

<?php else: ?>

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
                <td><?= htmlspecialchars($row['device'] ?? 'Unknown') ?></td>
                <td><?= (int) $row['total'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>

<h3>Recent Clicks</h3>

<?php if (empty($recentClicks)): ?>

    <p>No clicks yet.</p>

<?php else: ?>

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
                <td><?= htmlspecialchars($click['ip_address'] ?? '-') ?></td>
                <td><?= htmlspecialchars($click['browser'] ?? 'Unknown') ?></td>
                <td><?= htmlspecialchars($click['device'] ?? 'Unknown') ?></td>
                <td><?= htmlspecialchars($click['referer'] ?? '-') ?></td>
                <td><?= htmlspecialchars($click['clicked_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>

</body>
</html>