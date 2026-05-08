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

    $user=$auth->user();

