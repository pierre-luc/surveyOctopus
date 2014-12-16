<?php
namespace octopus\app\views\layouts;
use octopus\core\Router;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>dashboard | survey octopus</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Loading Bootstrap -->
    <link href="<?php echo Router::root( 'css/vendor/bootstrap.min.css' );?>" rel="stylesheet">

    <!-- Loading Flat UI -->
    <link href="<?php echo Router::root( 'css/flat-ui.min.css' );?>" rel="stylesheet">

    <!-- Loading dashboard skin -->
    <link href="<?php echo Router::root( 'css/dashboard.css' );?>" rel="stylesheet">

    <link rel="shortcut icon" href="<?php echo Router::root( 'img/favicon.ico' );?>">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
    <!--[if lt IE 9]>
    <script src="<?php echo Router::root( 'js/vendor/html5shiv.js' );?>"></script>
    <script src="<?php echo Router::root( 'js/vendor/respond.min.js' );?>"></script>
    <![endif]-->

    <!-- jQuery (necessary for Flat UI's JavaScript plugins) -->
    <script src="<?php echo Router::root( 'js/vendor/jquery.min.js' );?>"></script>

</head>
<body>

    <div class="container-fluid">
        <div class="row">
            <nav class="navbar navbar-inverse navbar-embossed" role="navigation">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-01">
                        <span class="sr-only">Toggle navigation</span>
                    </button>
                    <a class="navbar-brand" href="<?= Router::root( 'dashboard' );?>">Survey Octopus</a>
                </div>
                <div class="navbar-collapse collapse in" id="navbar-collapse-01">
                    <ul class="nav navbar-nav navbar-left">
                        <li><a href="<?= Router::generate( 'survey/add' );?>">Créer un sondage<span class="navbar-unread">1</span></a></li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li><a href="<?= Router::generate( 'user/disconnect' );?>">Déconnexion<span class="navbar-unread">1</span></a></li>
                    </ul>
                </div>
            </nav><!-- /navbar -->
        </div>
        <?= $content_for_layout ?>
    </div>

    <!-- /.container -->

    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="<?php echo Router::root( 'js/vendor/video.js' );?>"></script>
    <script src="<?php echo Router::root( 'js/flat-ui.min.js' );?>"></script>
    <script src="<?php echo Router::root( 'js/flat-ui.extend.js' );?>"></script>

</body>
</html>

