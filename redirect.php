<?php

include "connect.php";
include "ShortUrl.php";

try {
    $link = strip_tags(trim($_GET['link']));
    $short = new ShortUrl($db);
    $long_url = $short->shortCodeToUrl($link);
    header('Location: ' . $long_url);
} catch (Exception $e) {
    echo '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
}


