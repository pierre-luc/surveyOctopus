<?php
namespace octopus\app\views\layouts;
use octopus\core\Router;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>survey octopus</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Loading Bootstrap -->
    <link href="<?php echo Router::root( 'css/vendor/bootstrap.min.css' );?>" rel="stylesheet">

    <!-- Loading Flat UI -->
    <link href="<?php echo Router::root( 'css/flat-ui.min.css' );?>" rel="stylesheet">

    <link rel="shortcut icon" href="<?php echo Router::root( 'img/favicon.ico' );?>">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
    <!--[if lt IE 9]>
    <script src="<?php echo Router::root( 'js/vendor/html5shiv.js' );?>"></script>
    <script src="<?php echo Router::root( 'js/vendor/respond.min.js' );?>"></script>
    <![endif]-->
</head>
<body>

    <?= $content_for_layout ?>

    <!-- /.container -->

    <!-- jQuery (necessary for Flat UI's JavaScript plugins) -->
    <script src="<?php echo Router::root( 'js/vendor/jquery.min.js' );?>"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="<?php echo Router::root( 'js/vendor/video.js' );?>"></script>
    <script src="<?php echo Router::root( 'js/flat-ui.min.js' );?>"></script>

</body>
</html>

