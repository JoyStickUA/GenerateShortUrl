<?php

include "connect.php";
include "ShortUrl.php";

$link = strip_tags(trim($_GET['link']));
//echo $link;
$short = new ShortUrl($db);
$long_url = $short->shortCodeToUrl($link);
//echo $long_url;
header('Location: '.$long_url);


