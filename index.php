<?php
include "connect.php";
include "ShortUrl.php";
?>
<html>
<head>
    <title>Test | Nix Solution</title>
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <meta name="viewport" content="width=device-width">
</head>
<body class="clearfix">
<div class="container">
    <div class="row">
        <h1>SHORTEN. SHARE. MEASURE</h1>
        <p class="lead">leading link management platform.</p>
        <div class="form_container clearfix">
            <form action="" method="post">
                    <div class="form-group col-lg-7 col-lg-offset-1 col-md-9 col-sm-8 col-xs-12">
                        <input type="text" name="longUrl" placeholder="Paste a link to shorten it" class="form-control col-lg-7">
                    </div>
                <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                    <button type="submit" class="btn btn-primary reduce">Shorten</button>
                </div>
<!--                    Ссылка: <input type="text" name="longUrl"/><br/>-->
<!--                    <input type="submit" value="Уменьшить"/>-->
            </form>
        </div>

<?php
if($_GET) {
    if ($curl = curl_init()) {
        curl_setopt($curl, CURLOPT_URL, 'nix/redirect.php?link='.$short);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $out = curl_exec($curl);
        echo $out;
        curl_close($curl);
    }
}

if ($_POST) {
    $short = new ShortUrl($db);
    $short_url = $short->urlToShortCode($_POST['longUrl']);
    echo '<div class="col-lg-10 col-lg-offset-1 col-xs-12">
        <a href="/redirect.php?link='.$short_url.'" class="short_url">'.$_SERVER['HTTP_HOST'].'/'.$short_url.'</a>';
    $counter = $short->shortCounter($short_url);
    echo '<img src="assets/img/Shape.png" class="counter_img"><span class="counter">'.$counter['counter'].'</span>';
}
?>

    </div>
</div>
</body>
</html>
<!--//$sql = "SELECT * FROM min_link";-->
<!--//$result = $db->query($sql);-->
<!--//-->
<!--//$sql = "SELECT * FROM min_link";-->
<!--//$result = $db->query($sql);-->
<!--//$links = $result->fetchAll(PDO::FETCH_ASSOC);-->
<!--//-->
<!--//echo '<h1>link</h1>';-->
<!--//foreach ($links as $link) {-->
<!--//    echo $link['id']. "Long:". $link['long']. "Short:" . $link['short']."<br>";-->
<!--//}-->
<!---->
<!--    //echo '<pre>';-->
<!--    //print_r($_SERVER);-->
<!--    //echo '</pre>';-->
<!---->
<!---->
<!--//    $sql = "INSERT INTO short_urls (long_url, short_code) VALUES (:long, :short)";-->
<!--//    $stmt = $db->prepare($sql);-->
<!--//-->
<!--//    $long = $_POST['longUrl'];-->
<!--//    $short = 'short';-->
<!--//-->
<!--//    $stmt->bindValue(':long', $long);-->
<!--//    $stmt->bindValue(':short', $short);-->
<!--//    $stmt->execute();-->
<!--//-->
<!--//    $stmt->rowCount();-->
<!--//    $db->lastInsertId();-->
<!--////$stmt -> execute(array(':long' => $long, ':short' => $short));-->
<!---->
<!---->
<!--//    echo '<pre>';-->
<!--//    print_r(parse_url($long_url));-->
<!--//    echo '</pre>';-->
<!---->
<!--//    echo '<pre>';-->
<!--//    echo htmlspecialchars(print_r($_POST, true));-->
<!--//    echo '</pre>';-->
