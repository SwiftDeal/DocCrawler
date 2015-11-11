<?php

// initialize seo
include("seo.php");

$seo = new SEO(array(
    "title" => "Search for a Top Doctor",
    "keywords" => "get reviews and search for a top doc online",
    "description" => "Welcome to search for a top doc",
    "author" => "SearchForaTopDoc.com",
    "robots" => "INDEX,FOLLOW",
    "photo" => CDN . "img/logo.png"
));

Framework\Registry::set("seo", $seo);
