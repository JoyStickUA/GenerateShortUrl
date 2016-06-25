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
                <div class="form-group col-lg-4 col-lg-offset-1 col-md-5 col-sm-4 col-xs-12">
                    <input type="text" name="longUrl" placeholder="Paste a link to shorten it" class="form-control">
                </div>
                <div class="form-group col-lg-3 col-md-4 col-sm-4 col-xs-12">
                    <input type="text" name="timeUrl" placeholder="Time url" class="form-control">
                </div>
                <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                    <button type="submit" class="btn btn-primary reduce">Shorten</button>
                </div>
            </form>
        </div>

        <?php
        try {
            if ($_GET) {
                if ($curl = curl_init()) {
                    curl_setopt($curl, CURLOPT_URL, 'nix/redirect.php?link=' . $short);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    $out = curl_exec($curl);
                    echo $out;
                    curl_close($curl);
                }
            }

            if ($_POST) {
                $short = new ShortUrl($db);
                $short_url = $short->urlToShortCode($_POST['longUrl'], $_POST['timeUrl']);
                echo '<div class="col-lg-10 col-lg-offset-1 col-xs-12">
                    <a href="/redirect.php?link=' . $short_url . '" class="short_url">' . $_SERVER['HTTP_HOST'] . '/' . $short_url . '</a>';
                $counter = $short->shortCounter($short_url);
                echo '<img src="assets/img/Shape.png" class="counter_img"><span class="counter">' . $counter['counter'] . '</span></div>';
            }
        } catch (Exception $e) {
            echo '<div class="col-xs-12 col-md-12 col-lg-offset-1 col-lg-10">
                <div class="alert alert-danger">' . $e->getMessage() . '</div>
            </div>';
        } finally {

        }
        ?>

    </div>
</div>
</body>
</html>
