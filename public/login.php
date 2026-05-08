<?php
    require_once __DIR__.'/../bootstrap.php';
    require_once __DIR__.'/../Services/AuthService.php';

    $auth=new AuthService($conn);
    if($auth->check()){
       header('Location: dashboard.php');
       exit;
    }

    $error=null;

    if($_SERVER['REQUEST_METHOD']==='POST') {
        $email = $_POST['email'];
        $password = $_POST['password'] ?? '';

        $loggedIn = $auth->login($email, $password);

        if ($loggedIn) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Invalid email or password";
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h1>Log in</h1>
    <?php if($error):?>
        <p style="color: red;">
            <?=htmlspecialchars($error);?>
        </p>
    <?php endif;?>

    <form method="post">
        <div>
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <br>
        <div>
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <br>
        <button type="submit">Login</button>
    </form>
    <br>
    <p>
        Don't have an account?
        <a href="register.php">Register</a>
    </p>
</body>
</html>