<?php
include "connect.php";
include "ShortUrl.php";
include "header.php";
$action = $_GET['action'];



try {
    if ($action == 'create') {
    $longUrl = $_GET['longUrl'];
    $timeUrl = $_GET['timeUrl'];

    $short = new ShortUrl($db);
    $short_url = $short->urlToShortCode($longUrl, $timeUrl);
    header('Location: controller.php?action=show&link='.$short_url);
    } elseif ($action == 'show') {

        $short = new ShortUrl($db);
        $short_url = $_GET['link'];
        $counter = $short->shortCounter($short_url); ?>
        <div class="col-lg-10 col-lg-offset-1 col-xs-12">
            <a href="/redirect.php?link=<?php echo $short_url ?>" class="short_url"><?php echo $_SERVER['HTTP_HOST'] . '/' . $short_url ?></a>
            <img src="assets/img/Shape.png" class="counter_img"><span class="counter"><?php echo $counter['counter'] ?></span>
            <br>
            <a href="/" class="short_url text-center to_main">На главную</a>
        </div>
    <?php }
//            if ($_GET) {
//                if ($curl = curl_init()) {
//                    curl_setopt($curl, CURLOPT_URL, 'nix/redirect.php?link=' . $short);
//                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//                    $out = curl_exec($curl);
//                    echo $out;
//                    curl_close($curl);
//                }
//            }
//
//            if ($_POST) {
//                $short = new ShortUrl($db);
//                $short_url = $short->urlToShortCode($_POST['longUrl'], $_POST['timeUrl']);
//                echo '<div class="col-lg-10 col-lg-offset-1 col-xs-12">
//                    <a href="/redirect.php?link=' . $short_url . '" class="short_url">' . $_SERVER['HTTP_HOST'] . '/' . $short_url . '</a>';
//                $counter = $short->shortCounter($short_url);
//                echo '<img src="assets/img/Shape.png" class="counter_img"><span class="counter">' . $counter['counter'] . '</span></div>';
//            }
} catch (Exception $e) { ?>
    <div class="col-xs-12 col-md-12 col-lg-offset-1 col-lg-10">
        <div class="alert alert-danger"><?php echo $e->getMessage(); ?></div>
        <a href="/" class="short_url">На главную</a>
    </div>
<?php
}
include "footer.php";