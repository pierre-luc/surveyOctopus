var manageSurvey = {
    question: {
        list: [],
        choice: function() {

        }
    }
};



$( document ).ready( function(){
    $( '#btnChoiceQuestion').click( function() {
        var html = '<div class="row question"><div class="login-form">'
                    + '<div class="container-fluid">'
                    +   '<div class="row">'
                    +       '<p>Question à choix</p>'
                    +       '<input maxlength="80" name="question1" type="text" class="form-control login-field" value="" placeholder="Texte de la question" id="question1">'
                    +       '<p>Réponses:</p>'
                    +       '<input name="tagsinput" class="tagsinput" data-role="tagsinput" value="Clean, Fresh, Modern, Unique" />'
                    +   '</div>'
                    + '</div>'
                 + '</div></div>';
        $( '#questions').append( html );
        $(".tagsinput").tagsinput();
        $('.bootstrap-tagsinput input[type=text]').removeAttr( 'style' );
    } );

    $( '#btnNumValueQuestion').click( function() {
        var html = '<div class="row question"><div class="login-form">'
            + '<div class="container-fluid">'
            +   '<div class="row">'
            +       '<p style="color:#2C3E50;">Question à valeur numérique</p>'
            +   '</div>'
            + '</div>'
            + '</div></div>';
        $( '#questions').append( html );
    } );
} );