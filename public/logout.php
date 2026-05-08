<?php
    require_once __DIR__.'/../bootstrap.php';
    require_once __DIR__.'/../Services/AuthService.php';

    $auth=new AuthService($conn);
    $auth->logout();
    header('Location: login.php');
    exit;
