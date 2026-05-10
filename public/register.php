<?php

// Load the main application bootstrap file
require_once __DIR__ . '/../bootstrap.php';

// Load the authentication service
require_once __DIR__ . '/../Services/AuthService.php';

// Create authentication service instance
$auth = new AuthService($conn);

// Check if the user is already logged in
if ($auth->check()) {

    // Redirect logged-in users directly to the dashboard
    header('Location: dashboard.php');
    exit;
}

// Variable used to store registration error messages
$error = null;

// Check if the registration form was submitted using POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get submitted name value
    $name = $_POST['name'] ?? '';

    // Get submitted email value
    $email = $_POST['email'] ?? '';

    // Get submitted password value
    $password = $_POST['password'] ?? '';

    // Get submitted password confirmation value
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Check if password and confirmation password match
    if ($password !== $confirmPassword) {

        // Display error message if passwords do not match
        $error = 'Passwords do not match.';

    } else {

        // Attempt to register the new user account
        $registered = $auth->register($name, $email, $password);

        // If registration succeeds
        if ($registered) {

            // Redirect user to login page
            header('Location: login.php');
            exit;
        }

        // Display error message if registration fails
        $error = 'Could not create account.';
    }
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">

    <!-- Page title displayed in browser tab -->
    <title>Register</title>
</head>
<body>

<!-- Main page heading -->
<h1>Register</h1>

<?php if ($error): ?>

    <!-- Display registration error message -->
    <p style="color: red;">
        <?= htmlspecialchars($error) ?>
    </p>

<?php endif; ?>

<!-- Registration form -->
<form method="POST" action="register.php">

    <div>

        <!-- User name input field -->
        <label>Name</label><br>

        <input
                type="text"
                name="name"
                required
        >
    </div>

    <br>

    <div>

        <!-- User email input field -->
        <label>Email</label><br>

        <input
                type="email"
                name="email"
                required
        >
    </div>

    <br>

    <div>

        <!-- User password input field -->
        <label>Password</label><br>

        <input
                type="password"
                name="password"
                required
        >
    </div>

    <br>

    <div>

        <!-- Password confirmation input field -->
        <label>Confirm Password</label><br>

        <input
                type="password"
                name="confirm_password"
                required
        >
    </div>

    <br>

    <!-- Form submit button -->
    <button type="submit">
        Register
    </button>

</form>

<br>

<p>
    <!-- Link to login page for existing users -->
    Already have an account?

    <a href="login.php">Login</a>
</p>

</body>
</html>