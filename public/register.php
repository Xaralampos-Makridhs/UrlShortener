<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../Services/AuthService.php';

$auth = new AuthService($conn);

if ($auth->check()) {
    header('Location: dashboard.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $registered = $auth->register($name, $email, $password);

        if ($registered) {
            header('Location: login.php');
            exit;
        }

        $error = 'Could not create account.';
    }
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
</head>
<body>

<h1>Register</h1>

<?php if ($error): ?>
    <p style="color: red;">
        <?= htmlspecialchars($error) ?>
    </p>
<?php endif; ?>

<form method="POST" action="register.php">
    <div>
        <label>Name</label><br>
        <input type="text" name="name" required>
    </div>

    <br>

    <div>
        <label>Email</label><br>
        <input type="email" name="email" required>
    </div>

    <br>

    <div>
        <label>Password</label><br>
        <input type="password" name="password" required>
    </div>

    <br>

    <div>
        <label>Confirm Password</label><br>
        <input type="password" name="confirm_password" required>
    </div>

    <br>

    <button type="submit">Register</button>
</form>

<br>

<p>
    Already have an account?
    <a href="login.php">Login</a>
</p>

</body>
</html>