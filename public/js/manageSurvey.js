jQuery.fn.swap = function(b){
    // method from: http://blog.pengoworks.com/index.cfm/2008/9/24/A-quick-and-dirty-swap-method-for-jQuery
    b = jQuery(b)[0];
    var a = this[0];
    var t = a.parentNode.insertBefore(document.createTextNode(''), a);
    b.parentNode.insertBefore(a, b);
    t.parentNode.insertBefore(b, t);
    t.parentNode.removeChild(t);
    return this;
};






var manageSurvey = {
    question: {
        list: [],
        lastIndex: 0
    },

    init: function() {
        $(manageSurvey).on('canSaved', function(){
            $('#btnSave').removeClass('disabled');
        });
        $(manageSurvey).on('cantSaved', function(){
            $('#btnSave').addClass('disabled');
        });
        $('#title').keyup(function(){
            $(manageSurvey).trigger( 'changed' );
        });

        $(manageSurvey).on( 'changed', function(){
            manageSurvey.canSave();
        });

        $('#btnSave').click(function(){
            manageSurvey.save();
        });

        $( '#btnChoiceQuestion').click( function() {
            var q = new manageSurvey.question.questionChoice();
            q.render();
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

        manageSurvey.load();
    },

    load: function() {
        $('#manage_preloader').css('visibility', 'visible');
        $.post( manageSurvey.getQuestionsUrl, {}, function( res ) {
            manageSurvey.enable();
            $('#manage_preloader').css('visibility', 'hidden');
            console.log( res );
            if ( res.status == 'success' ) {
                if ( res.questions ) {
                    for (var i in res.questions ) {
                        if ( res.questions[ i ].type == 'choice' ) {
                            var q = new manageSurvey.question.questionChoice();
                            q.setToken( res.questions[ i ].token );
                            q.setOrder( res.questions[ i ].orderNum );
                            q.setText( res.questions[ i ].text );
                            q.setAnswers( res.questions[ i ].criteres );
                            q.render();
                        }
                    }
                }
            } else {

            }
        }, "json");
    },

    canSave: function() {
        var t = $('#title').val();
        if (t.trim().length == 0 ) {
            $(manageSurvey).trigger( 'cantSaved' );
        } else {
            self.title = t;
            $(manageSurvey).trigger( 'canSaved' );
        }
    },

    save: function(){
        manageSurvey.disable();

        $('#manage_preloader').css('visibility', 'visible');
        var data = [];
        for(var i in manageSurvey.question.list) {
            data[i]={
                'order': manageSurvey.question.list[i].getOrder(),
                'text': manageSurvey.question.list[i].getText(),
                'criteres': manageSurvey.question.list[i].getAnswers(),
                'type': manageSurvey.question.list[i].getType(),
                'token': manageSurvey.question.list[i].getToken()
            };
        }

        console.log(data);
        $(manageSurvey).trigger( 'cantSaved' );


        $.post( manageSurvey.saveUrl, {data:data}, function( res ) {
            manageSurvey.enable();
            $('#manage_preloader').css('visibility', 'hidden');
            console.log( res );
            if ( res.status == 'success' ) {
                for(var i in res.tokens) {
                    manageSurvey.question.list[ res.tokens[ i].order ].setToken(
                      res.tokens[ i ].token
                    );
                }
            } else {

            }
        }, "json");

    },

    disable: function() {
        $( '#title' ).attr( 'disabled', 'disabled' );
        $( '#questions input' ).attr( 'disabled', 'disabled' );
        $( '#questions > .row' ).removeClass( 'dragdrop' );
        $( '#btnChoiceQuestion' ).addClass( 'disabled' );
        $( '#btnNumValueQuestion' ).addClass( 'disabled' );
    },
    enable: function() {
        $( '#title' ).removeAttr( 'disabled' );
        $( '#questions input' ).removeAttr( 'disabled' );
        $( '#questions > .row' ).addClass( 'dragdrop' );
        $( '#btnChoiceQuestion' ).removeClass( 'disabled' );
        $( '#btnNumValueQuestion' ).removeClass( 'disabled' );
    }
};

manageSurvey.question.questionChoice = function( token ) {
    this.index = ++manageSurvey.question.lastIndex;
    manageSurvey.question.list[ this.index ] = this;
    this.questionText = '';
    this.answers = null;
    this.order = this.index;
    this.type = 'choice';
    this.token = token ? token : null;
};

manageSurvey.question.questionChoice.prototype.getId = function() {
    return this.index;
};

manageSurvey.question.questionChoice.prototype.setId = function( id ) {
    this.index = id;
};

manageSurvey.question.questionChoice.prototype.setAnswers = function( answers ) {
    this.answers = answers;
}

manageSurvey.question.questionChoice.prototype.getToken = function() {
    return this.token;
};

manageSurvey.question.questionChoice.prototype.setToken = function( token ) {
    this.token = token ? token : null;
    $('#question_' + this.id).attr( 'data-token', this.token );
};

manageSurvey.question.questionChoice.prototype.getOrder = function() {
    return this.order;
};

manageSurvey.question.questionChoice.prototype.setText = function( text ) {
    this.questionText = text ? text : this.questionText;
};

manageSurvey.question.questionChoice.prototype.getText = function() {
    return this.questionText;
};

manageSurvey.question.questionChoice.prototype.getType = function() {
    return this.type;
};

manageSurvey.question.questionChoice.prototype.getAnswers = function() {
    return this.answers;
};


manageSurvey.question.questionChoice.prototype.setOrder = function( order ) {
    this.order = order;
};

manageSurvey.question.swap = function( a, b ) {
    var e = manageSurvey.question.list[ a ];
    manageSurvey.question.list[ a ] = manageSurvey.question.list[ b ];
    manageSurvey.question.list[ b ] = e;

    for (var i in manageSurvey.question.list) {
        manageSurvey.question.list[ i ].setOrder( i );
    }
    $( '#question_' + manageSurvey.question.list[ a ].getId() ).trigger( 'changeOrder' );
    $( '#question_' + manageSurvey.question.list[ b ].getId() ).trigger( 'changeOrder' );
};

manageSurvey.question.questionChoice.prototype.updateOrder = function() {
    $( '#question_' + this.index + ' b').html( this.getOrder() + ":" );
    $( '#question_' + this.index ).attr( 'data-order', this.getOrder() );
};

manageSurvey.question.dragdropInit = function() {
    $( ".dragdrop" ).draggable({ revert: true, helper: "clone" });

    $( ".dragdrop" ).droppable({
        accept: ".dragdrop",
        activeClass: "ui-state-hover",
        hoverClass: "ui-state-active",
        drop: function( event, ui ) {

            var draggable = ui.draggable, droppable = $(this),
                dragPos = draggable.position(), dropPos = droppable.position();

            draggable.css({
                left: dropPos.left+'px',
                top: dropPos.top+'px'
            });

            droppable.css({
                left: dragPos.left+'px',
                top: dragPos.top+'px'
            });
            draggable.swap(droppable);
            manageSurvey.question.swap(
                $(draggable).attr('data-order'),
                $(droppable).attr('data-order')
            );

        }
    });
};

manageSurvey.question.questionChoice.prototype.render = function() {
    var num = this.index;
    var answers = this.answers ? this.answers.join(',') : '';
    var text = this.questionText ? this.questionText : '';
    var html = '<div id="question_' + num + '" class="row question dragdrop" data-order="' + this.order + '"><div class="login-form">'
        + '<div class="container-fluid">'
        +   '<div class="row">'
        +       '<p><b>' + num + ':</b> Question à choix</p>'
        +       '<input maxlength="80" name="question_' + num + '" type="text" class="form-control login-field" value="' + text + '" placeholder="Texte de la question" id="questionText_' + num + '">'
        +       '<p>Réponses:</p>'
        +       '<input name="tagsinput" class="tagsinput" data-role="tagsinput" value="' + answers + '" placeholder="Réponse" />'
        +   '</div>'
        + '</div>'
        + '</div></div>';
    $( '#questions').append( html );
    $("#question_" + num + " .tagsinput").tagsinput({
        maxTags: 10,
        maxChars: 20
    });

    /*
    $('.bootstrap-tagsinput input[type=text]').removeAttr( 'style' );
    $('.bootstrap-tagsinput input[type=text]').attr( 'maxlength', 20 );
    */
    var self = this;
    $( '#questionText_' + num).keyup(function(){
        self.questionText = $( '#questionText_' + num).val();
        $(manageSurvey).trigger( 'changed' );
    });
    $( '#question_' + num + ' .tagsinput').change(function(){
        self.answers = $( '#question_' + num + ' .tagsinput').tagsinput('items');
        $(manageSurvey).trigger( 'changed' );
    });

    $( '#question_' + num).on( 'changeOrder', function(){
       self.updateOrder();
       $(manageSurvey).trigger( 'changed' );
    });

    manageSurvey.question.dragdropInit();
};



$( document ).ready( function(){
    manageSurvey.init();
} );