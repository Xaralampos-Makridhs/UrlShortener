<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../LinkServices/ShortLinkService.php';
require_once __DIR__ . '/../LinkServices/ClickTrackingService.php';


$shortLinkService = new ShortLinkService($conn);
$clickTrackingService = new ClickTrackingService($conn);

$shortCode = $_GET['code'] ?? '';
$shortCode = trim($shortCode);

if ($shortCode === '') {
    echo 'URL Shortener Home';
    exit;
}

$link = $shortLinkService->findByCode($shortCode);

if (!$link) {
    http_response_code(404);
    echo 'Short link not found';
    exit;
}

$clickTrackingService->track((int) $link['id']);

header('Location: ' . $link['original_url'], true, 302);
exit;