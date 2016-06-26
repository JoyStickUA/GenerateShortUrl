<?php
include "connect.php";
include "ShortUrl.php";
include 'header.php';
?>

<div class="form_container clearfix">
    <div id="random">
        <form action="controller.php?" method="get" class="clearfix">
            <input type="hidden" name="action" placeholder="Time url" value="create">
            <div class="form-group col-lg-4 col-lg-offset-1 col-md-5 col-sm-4 col-xs-12">
                <input type="text" name="longUrl" placeholder="Paste a link to shorten it" class="form-control">
            </div>
            <div class="form-group col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <input type="text" name="timeUrl" placeholder="Time url" class="form-control">
            </div>
            <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                <button type="submit" class="btn btn-lg btn-primary reduce">Shorten</button>
            </div>
        </form>
        <div class="col-xs-12 col-lg-offset-1 col-lg-10">
            <div class="btn btn-lg btn-success create_link" data-id="private" data-tab-id="random">Create a link</div>
        </div>
    </div>

    <div class="hide" id="private">
        <form action="controller.php?" method="get" class="clearfix">
            <input type="hidden" name="action" placeholder="Time url" value="private">
            <div class="form-group col-lg-4 col-md-4 col-sm-4 col-xs-12">
                <input type="text" name="longUrl" placeholder="Paste a link to shorten it" class="form-control">
            </div>
            <div class="form-group col-lg-3 col-md-3 col-sm-4 col-xs-12">
                <input type="text" name="nameUrl" placeholder="Your Link" class="form-control">
            </div>
            <div class="form-group col-lg-2 col-md-2 col-sm-4 col-xs-12">
                <input type="text" name="timeUrl" placeholder="Time url" class="form-control">
            </div>
            <div class="col-lg-3 col-md-3 col-md-offset-0 col-sm-4 col-sm-offset-4 col-xs-12">
                <button type="submit" class="btn btn-lg btn-primary reduce create">Shorten</button>
            </div>
        </form>
        <div class="col-xs-12 col-lg-10">
            <div class="btn btn-lg btn-success create_link" data-id="random" data-tab-id="private">Generate a link</div>
        </div>
    </div>
</div>

<?php
include 'footer.php';
?>
