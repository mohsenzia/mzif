<!doctype html>
<html lang="en" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Title</title>
	
    <meta name="keywords" content="">
    <meta name="description" content="">
    
	<link rel="icon" href="<?php echo PUBLIC_PATH ?>/assets/img/favicon.png" sizes="32x32" type="image/png">

    <?php HTML::includeCss('bootstrap.min'); ?>
    <?php HTML::includeCss('fontawsome.min'); ?>
    <?php HTML::includeCss('main'); ?>
    <?php HTML::includeCss('style'); ?>

    <?php HTML::includeJs('jquery-min'); ?>
    <?php HTML::includeJs('bootstrap.min'); ?>
    <?php HTML::includeJs('common'); ?>
    <?php HTML::includeJs('script'); ?>
    <?php HTML::includeJs('ajax'); ?>

    <script>
        var BASE_PATH = '<?php echo BASE_PATH;?>';
    </script>
</head>

<body>

<div id="main-alert" class="alert" role="alert"></div>