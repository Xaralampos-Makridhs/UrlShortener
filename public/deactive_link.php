<?php
    require_once __DIR__.'/../bootstrap.php';
    require_once __DIR__.'/../Services/AuthService.php';
    require_once __DIR__.'/../LinkServices/ShortLinkService.php';

    $auth=new AuthService($conn);
    $shortLinkService=new ShortLinkService($conn);

    if(!$auth->check()){
        header('Location: login.php');
        exit;
    }

    if($_SERVER['REQUEST_METHOD']==='POST'){
        header('Location: dashboard.php');
        exit;
    }

    $user=$auth->user();

    $linkId=isset($_POST['link_id']) ? (int)$user['link_id'] : 0;

    if($linkId>0){
        $shortLinkService->deactivate($linkId,(int)$user['link_id']);
    }

    header('Location: dashboard.php');
    exit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <form method="post">
        <input type="hidden" name="link_id" value="<?= (int) $link['id'] ?>">

        <button type="submit">Deactivate</button>
    </form>
</body>
</html>
