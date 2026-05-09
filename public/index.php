<?php
    require_once __DIR__.'/../bootstrap.php';
    require_once __DIR__.'/../LinkServices/ShortLinkService.php';
    require_once __DIR__.'/../LinkServices/ShortLinkService.php';

    $shortLinkService=new ShortLinkService($conn);
    $clickTrackingService=new ClickTrackingService($conn);


    $path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
    $shortCode=trim($path,'/');

    if($shortCode===''){
        echo "URL Shortener Home";
        exit;
    }

    $link=$shortLinkService->findByCode($shortCode);
    $clickTrackingService->track((int)$link['id']);

    if(!$link){
        http_response_code(404);
        echo "Short link not found";
        exit;
    }

    header('Location: '.$link['original_url'],true,302);
    exit;
