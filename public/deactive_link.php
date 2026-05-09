<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../Services/AuthService.php';
require_once __DIR__ . '/../LinkServices/ShortLinkService.php';

$auth = new AuthService($conn);
$shortLinkService = new ShortLinkService($conn);

if (!$auth->check()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$user = $auth->user();

$linkId = isset($_POST['link_id'])
        ? (int) $_POST['link_id']
        : 0;

if ($linkId > 0 && $user) {
    $shortLinkService->deactivate(
            $linkId,
            (int) $user['id']
    );
}

header('Location: dashboard.php');
exit;