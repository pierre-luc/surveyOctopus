<?php
namespace octopus\app\views\install;
use octopus\core\Router;
?>

<div class="login" style="">
    <div class="login-screen">
        <div class="login-icon">
            <img src="<?= Router::generate( 'img/icons/svg/clipboard.svg' );?>" alt="Welcome to Mail App">
            <h4><?= $appname?><small>Installation</small></h4>
        </div>

        <p class="lead">Installation en cours</p>
        <p>
            Le processus d'installation crée la base de données. Cela peut prendre un moment.
        </p>
        <img id="preloader" src="<?= Router::generate('img/preloader/barloader.gif') ?>"
             alt=""/>
    </div>
</div>
<script>
    $(document).ready(function(){
        var url = "<?= Router::generate( 'install/databaseInstallationProcess' )?>";
        $.post( url, {}, function( data ) {
            $( '#preloader').remove();
            $( '.login-screen').append(
                '<p id="message"></p>'
            );
            if ( data.status == 'success' ) {
                $( '#message' ).html( "Installation réussie.");
                $( '#message').addClass( 'palette palette-emerald' );
                $( '.login-screen').append(
                    '<div id="details"></div>'
                );
                $( '#details' ).html(
                    "<p>L'installation est terminée. Le premier compte que vous allez créer sera le compte administrateur.</p>"


                    + '<div class="row"><div class="col-md-4 col-md-offset-8">'
                    +         '<a class="btn btn-block btn-lg btn-inverse" href="<?= Router::generate( 'user/signup' );?>">'

                    +           '<span>Terminer</span>'
                    +           '<span class="fui-arrow-right"></span>'
                    +         '</a>'


                    + '</div></div>'
                );
                $( '#details').addClass( 'palette palette-nephritis');
            } else {
                console.log(data);
                $( '#message' ).html( "Une erreur est survenue durant l'installation." );
                $( '#message').addClass( 'palette palette-alizarin');
                $( '.login-screen').append(
                    '<div id="details"></div>'
                );
                $( '#details' ).html(
                     "<p>Plusieurs raisons peuvent être à l'origine de cette erreur. Elles peuvent être:</p>"

                    + "<ul>"
                    +   "<li>Les détails de connexion de l'étape précédente sont erronés</li>"
                    +   "<li>Le fichier SQL de la base de données est introuvable ou n'a pas les droits en lecture</li>"
                    +   "<li>Des requêtes SQL sont incorrectes.</li>"
                    + "</ul>"
                    + '<div class="row"><div class="col-md-12">'
                    +    '<ul class="pager">'
                    +      '<li class="previous">'
                    +        '<a href="<?= Router::generate( 'install' );?>">'
                    +           '<span class="fui-arrow-left"></span>'
                    +           '<span>&Eacute;tape précédente</span>'
                    +        '</a>'
                    +      '</li>'
                    +      '<li class="next">'
                    +         '<a href="<?= Router::generate( 'install/databaseInstallation' );?>">'
                    +           '<span><img src="<?= Router::generate( 'img/icons/svg/pencils.svg' );?>" width="20" height="20">&nbsp;&nbsp;</span>'
                    +           '<span>Relancer</span>'
                    +         '</a>'
                    +      '</li>'
                    +    '</ul>'
                    + '</div></div>'
                );
                $( '#details').addClass( 'palette palette-pomegranate');
            }
        }, "json");
    });
</script>



