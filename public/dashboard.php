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

$user = $auth->user();

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $originalUrl = $_POST['original_url'] ?? '';
    $title = $_POST['title'] ?? null;
    $customCode = $_POST['custom_code'] ?? null;
    $expiresAt = $_POST['expires_at'] ?? null;

    if ($title === '') {
        $title = null;
    }

    if ($customCode === '') {
        $customCode = null;
    }

    if ($expiresAt === '') {
        $expiresAt = null;
    }

    $created = $shortLinkService->create(
            (int) $user['id'],
            $originalUrl,
            $title,
            $customCode,
            $expiresAt
    );

    if ($created) {
        $message = 'Short link created successfully.';
    } else {
        $error = 'Could not create short link.';
    }
}

$links = $shortLinkService->getUserLinks((int) $user['id']);
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - URL Shortener</title>
</head>
<body>

<h1>Dashboard URL Shortener</h1>

<p>Welcome, <?= htmlspecialchars($user['name']) ?></p>

<p>
    <a href="logout.php">Log Out</a>
</p>

<?php if ($message): ?>
    <p style="color: green;">
        <?= htmlspecialchars($message) ?>
    </p>
<?php endif; ?>

<?php if ($error): ?>
    <p style="color: red;">
        <?= htmlspecialchars($error) ?>
    </p>
<?php endif; ?>

<h2>Create Short Link</h2>

<form method="post" action="dashboard.php">
    <div>
        <label>Original URL</label><br>
        <input type="url" name="original_url" required>
    </div>

    <br>

    <div>
        <label>Title</label><br>
        <input type="text" name="title">
    </div>

    <br>

    <div>
        <label>Custom Code</label><br>
        <input type="text" name="custom_code" placeholder="optional">
    </div>

    <br>

    <div>
        <label>Expires At</label><br>
        <input type="datetime-local" name="expires_at">
    </div>

    <br>

    <button type="submit">Create</button>
</form>

<hr>

<h2>Your Links</h2>

<?php if (empty($links)): ?>

    <p>No links yet.</p>

<?php else: ?>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
        <tr>
            <th>Short Code</th>
            <th>Original URL</th>
            <th>Title</th>
            <th>Status</th>
            <th>Expires At</th>
            <th>Created At</th>
            <th>Analytics</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($links as $link): ?>
            <tr>
                <td>
                    <a href="/<?= htmlspecialchars($link['short_code']) ?>" target="_blank">
                        <?= htmlspecialchars($link['short_code']) ?>
                    </a>
                </td>

                <td>
                    <a href="<?= htmlspecialchars($link['original_url']) ?>" target="_blank">
                        <?= htmlspecialchars($link['original_url']) ?>
                    </a>
                </td>

                <td>
                    <?= htmlspecialchars($link['title'] ?? '') ?>
                </td>

                <td>
                    <?= $link['is_active'] ? 'Active' : 'Inactive' ?>
                </td>

                <td>
                    <?= htmlspecialchars($link['expires_at'] ?? '-') ?>
                </td>

                <td>
                    <?= htmlspecialchars($link['created_at']) ?>
                </td>

                <td>
                    <a href="analytics.php?link_id=<?= (int) $link['id'] ?>">
                        Analytics
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>

</body>
</html>