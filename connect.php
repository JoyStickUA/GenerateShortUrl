<?php

$user = "root";
$pass = '';
$host = 'nix';
$dbname = 'nix';
try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("set names utf8");
}
catch(PDOException $e) {
    echo $e->getMessage();
}