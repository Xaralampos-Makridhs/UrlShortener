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

// Variable used to store login error messages
$error = null;

// Check if the login form was submitted using POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get email value from submitted form data
    $email = $_POST['email'] ?? '';

    // Get password value from submitted form data
    $password = $_POST['password'] ?? '';

    // Attempt to log in the user
    $loggedIn = $auth->login($email, $password);

    // If login is successful
    if ($loggedIn) {

        // Redirect user to dashboard page
        header('Location: dashboard.php');
        exit;
    }

    // Display error message if login fails
    $error = 'Invalid email or password.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <!-- Page title displayed in browser tab -->
    <title>Login</title>
</head>
<body>

<!-- Main page heading -->
<h1>Log in</h1>

<?php if ($error): ?>

    <!-- Display login error message -->
    <p style="color: red;">
        <?= htmlspecialchars($error) ?>
    </p>

<?php endif; ?>

<!-- Login form -->
<form method="POST" action="login.php">

    <div>

        <!-- Email input field -->
        <label>Email</label><br>

        <input
                type="email"
                name="email"
                required
        >
    </div>

    <br>

    <div>

        <!-- Password input field -->
        <label>Password</label><br>

        <input
                type="password"
                name="password"
                required
        >
    </div>

    <br>

    <!-- Form submit button -->
    <button type="submit">
        Login
    </button>

</form>

<br>

<p>
    <!-- Link to user registration page -->
    Don't have an account?

    <a href="register.php">Register</a>
</p>

</body>
</html>