<?php

// Load the main application bootstrap file
require_once __DIR__ . '/../bootstrap.php';

// Load the authentication service
require_once __DIR__ . '/../Services/AuthService.php';

// Load the service responsible for creating and managing short links
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

// Get the currently logged-in user
$user = $auth->user();

// Message shown when an action is successful
$message = null;

// Message shown when an error occurs
$error = null;

// Check if the form was submitted using POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get original URL from submitted form data
    $originalUrl = $_POST['original_url'] ?? '';

    // Get optional title from submitted form data
    $title = $_POST['title'] ?? null;

    // Get optional custom short code from submitted form data
    $customCode = $_POST['custom_code'] ?? null;

    // Get optional expiration date from submitted form data
    $expiresAt = $_POST['expires_at'] ?? null;

    // Convert empty title value to null
    if ($title === '') {
        $title = null;
    }

    // Convert empty custom code value to null
    if ($customCode === '') {
        $customCode = null;
    }

    // Convert empty expiration date value to null
    if ($expiresAt === '') {
        $expiresAt = null;
    }

    // Create the short link using the service
    $created = $shortLinkService->create(
            (int) $user['id'],
            $originalUrl,
            $title,
            $customCode,
            $expiresAt
    );

    // Set success or error message based on create result
    if ($created) {
        $message = 'Short link created successfully.';
    } else {
        $error = 'Could not create short link.';
    }
}

// Get all links that belong to the current user
$links = $shortLinkService->getUserLinks((int) $user['id']);

?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">

    <!-- Page title shown in browser tab -->
    <title>Dashboard - URL Shortener</title>
</head>
<body>

<!-- Main dashboard heading -->
<h1>Dashboard URL Shortener</h1>

<p>
    Welcome,

    <!-- Display logged-in user's name safely -->
    <?= htmlspecialchars($user['name']) ?>
</p>

<p>
    <!-- Link used to log out the current user -->
    <a href="logout.php">Log Out</a>
</p>

<?php if ($message): ?>

    <!-- Success message shown after successful action -->
    <p style="color: green;">
        <?= htmlspecialchars($message) ?>
    </p>

<?php endif; ?>

<?php if ($error): ?>

    <!-- Error message shown if something fails -->
    <p style="color: red;">
        <?= htmlspecialchars($error) ?>
    </p>

<?php endif; ?>

<!-- Form section for creating a new short link -->
<h2>Create Short Link</h2>

<!-- Submit form data to the same dashboard page -->
<form method="POST" action="dashboard.php">

    <div>
        <!-- Required original URL field -->
        <label>Original URL</label><br>
        <input
                type="url"
                name="original_url"
                required
        >
    </div>

    <br>

    <div>
        <!-- Optional title field for easier link identification -->
        <label>Title</label><br>
        <input
                type="text"
                name="title"
        >
    </div>

    <br>

    <div>
        <!-- Optional custom short code field -->
        <label>Custom Code</label><br>
        <input
                type="text"
                name="custom_code"
                placeholder="optional"
        >
    </div>

    <br>

    <div>
        <!-- Optional expiration date and time field -->
        <label>Expires At</label><br>
        <input
                type="datetime-local"
                name="expires_at"
        >
    </div>

    <br>

    <!-- Submit button that creates the short link -->
    <button type="submit">
        Create
    </button>

</form>

<hr>

<!-- Section displaying all links created by the current user -->
<h2>Your Links</h2>

<?php if (empty($links)): ?>

    <!-- Message shown when the user has not created any links yet -->
    <p>No links yet.</p>

<?php else: ?>

    <!-- Table containing all user links -->
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
            <th>Actions</th>
        </tr>
        </thead>

        <tbody>

        <?php foreach ($links as $link): ?>

            <!-- One table row per short link -->
            <tr>

                <td>
                    <!-- Link that opens the short URL redirect page -->
                    <a
                            href="index.php?code=<?= urlencode($link['short_code']) ?>"
                            target="_blank"
                    >
                        <?= htmlspecialchars($link['short_code']) ?>
                    </a>
                </td>

                <td>
                    <!-- Link that opens the original destination URL -->
                    <a
                            href="<?= htmlspecialchars($link['original_url']) ?>"
                            target="_blank"
                    >
                        <?= htmlspecialchars($link['original_url']) ?>
                    </a>
                </td>

                <td>
                    <!-- Display link title safely, or empty text if no title exists -->
                    <?= htmlspecialchars($link['title'] ?? '') ?>
                </td>

                <td>
                    <!-- Display whether the short link is active or inactive -->
                    <?= $link['is_active'] ? 'Active' : 'Inactive' ?>
                </td>

                <td>
                    <!-- Display expiration date if available, otherwise show dash -->
                    <?= htmlspecialchars($link['expires_at'] ?? '-') ?>
                </td>

                <td>
                    <!-- Display creation timestamp safely -->
                    <?= htmlspecialchars($link['created_at']) ?>
                </td>

                <td>
                    <!-- Link to analytics page for this short link -->
                    <a href="analytics.php?link_id=<?= (int) $link['id'] ?>">
                        Analytics
                    </a>
                </td>

                <td>

                    <?php if ($link['is_active']): ?>

                        <!-- Form used to deactivate an active link -->
                        <form
                                method="POST"
                                action="deactive_link.php"
                                style="display:inline;"
                        >

                            <!-- Hidden input containing the link ID -->
                            <input
                                    type="hidden"
                                    name="link_id"
                                    value="<?= (int) $link['id'] ?>"
                            >

                            <!-- Submit button for link deactivation -->
                            <button type="submit">
                                Deactivate
                            </button>

                        </form>

                    <?php endif; ?>

                    <!-- Form used to permanently delete a link -->
                    <form
                            method="POST"
                            action="delete_link.php"
                            style="display:inline;"
                    >

                        <!-- Hidden input containing the link ID -->
                        <input
                                type="hidden"
                                name="link_id"
                                value="<?= (int) $link['id'] ?>"
                        >

                        <!-- Submit button for link deletion -->
                        <button type="submit">
                            Delete
                        </button>

                    </form>

                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

<?php endif; ?>

</body>
</html>