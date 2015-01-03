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
    title: null,
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
            manageSurvey.title = $( '#title' ).val();
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
            var q = new manageSurvey.question.questionNumeric();
            q.render();
        } );

        manageSurvey.load();
    },

    load: function() {
        $('#manage_preloader').css('visibility', 'visible');

        manageSurvey.title = $( '#title' ).val();

        $.post( manageSurvey.getQuestionsUrl, {}, function( res ) {
            manageSurvey.enable();
            $('#manage_preloader').css('visibility', 'hidden');
            if ( res.status == 'success' ) {
                if ( res.questions ) {
                    for (var i in res.questions ) {
                        switch ( res.questions[ i ].type ) {
                            case 'choice':
                                var q = new manageSurvey.question.questionChoice();
                                q.setToken( res.questions[ i ].token );
                                q.setOrder( res.questions[ i ].orderNum );
                                q.setText( res.questions[ i ].text );
                                q.setAnswers( res.questions[ i ].criteres );
                                q.render();
                            break;
                            case 'numeric':
                                var q = new manageSurvey.question.questionNumeric();
                                q.setToken( res.questions[ i ].token );
                                q.setOrder( res.questions[ i ].orderNum );
                                q.setText( res.questions[ i ].text );
                                q.setAnswers( res.questions[ i ].criteres );
                                q.render();
                                break;
                            default:
                                // rien à faire
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
                'token': manageSurvey.question.list[i].getToken(),
                'isDeleted': manageSurvey.question.list[i].isDeleted()
            };
        }
        $(manageSurvey).trigger( 'cantSaved' );


        $.post( manageSurvey.saveUrl, {title:manageSurvey.title, data:data}, function( res ) {
            manageSurvey.enable();
            $('#manage_preloader').css('visibility', 'hidden');
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

manageSurvey.question.questionChoice = function( token ) {
    this.index = ++manageSurvey.question.lastIndex;
    manageSurvey.question.list[ this.index ] = this;
    this.questionText = '';
    this.answers = null;
    this.order = this.index;
    this.type = 'choice';
    this.token = token ? token : null;
    this.deleted = false;
};

manageSurvey.question.questionChoice.prototype.isDeleted = function() {
    return this.deleted;
};

manageSurvey.question.questionChoice.prototype.delete = function() {
  this.deleted = true;
  $( '#question_' + this.index ).off( 'changeOrder' );
  $( '#question_' + this.index ).remove();
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

manageSurvey.question.questionChoice.prototype.updateOrder = function() {
    $( '#question_' + this.index + ' b').html( this.getOrder() + ":" );
    $( '#question_' + this.index ).attr( 'data-order', this.getOrder() );
};

manageSurvey.question.questionChoice.prototype.render = function() {
    var num = this.index;
    var answers = this.answers ? this.answers.join(',') : '';
    var text = this.questionText ? this.questionText : '';
    var html = '<div id="question_' + num + '" class="row question dragdrop" data-order="' + this.order + '"><div class="login-form">'
        + '<div class="container-fluid">'
        +   '<div class="row">'
        +       '<div class="container-fluid">'
        +           '<div class="row">'
        +               '<div class="col-md-11">'
        +                   '<p><b>' + num + ':</b> Question à choix</p>'
        +               '</div>'
        +               '<div class="col-md-1">'
        +                   '<a href="#" class="remove-btn login-field-icon fui-cross"></a>'
        +               '</div>'
        +           '</div>'
        +           '<div class="row">'
        +               '<input maxlength="80" name="question_' + num + '" type="text" class="form-control login-field" value="' + text + '" placeholder="Texte de la question" id="questionText_' + num + '">'
        +               '<p>Réponses:</p>'
        +               '<input name="tagsinput" class="tagsinput" data-role="tagsinput" value="' + answers + '" placeholder="Réponse" />'
        +           '</div>'
        +       '</div>'
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

    $( '#question_' + num + ' .remove-btn').click(function(){
        manageSurvey.question.list[ num ].delete();
        $(manageSurvey).trigger( 'changed' );
    });

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

/**
 * Classe questionNumeric
 */
manageSurvey.question.questionNumeric = function( token ) {
    this.index = ++manageSurvey.question.lastIndex;
    manageSurvey.question.list[ this.index ] = this;
    this.questionText = '';
    this.answers = null;
    this.order = this.index;
    this.type = 'numeric';
    this.token = token ? token : null;
    this.deleted = false;
};

manageSurvey.question.questionNumeric.prototype.isDeleted = function() {
    return this.deleted;
};

manageSurvey.question.questionNumeric.prototype.delete = function() {
  this.deleted = true;
  $( '#question_' + this.index ).off( 'changeOrder' );
  $( '#question_' + this.index ).remove();
};

manageSurvey.question.questionNumeric.prototype.getId = function() {
    return this.index;
};

manageSurvey.question.questionNumeric.prototype.setId = function( id ) {
    this.index = id;
};

manageSurvey.question.questionNumeric.prototype.setAnswers = function( answers ) {
    this.answers = answers;
}

manageSurvey.question.questionNumeric.prototype.getToken = function() {
    return this.token;
};

manageSurvey.question.questionNumeric.prototype.setToken = function( token ) {
    this.token = token ? token : null;
    $('#question_' + this.id).attr( 'data-token', this.token );
};

manageSurvey.question.questionNumeric.prototype.getOrder = function() {
    return this.order;
};

manageSurvey.question.questionNumeric.prototype.setText = function( text ) {
    this.questionText = text ? text : this.questionText;
};

manageSurvey.question.questionNumeric.prototype.getText = function() {
    return this.questionText;
};

manageSurvey.question.questionNumeric.prototype.getType = function() {
    return this.type;
};


manageSurvey.question.questionNumeric.prototype.getAnswers = function() {
    return this.answers;
};

manageSurvey.question.questionNumeric.prototype.setOrder = function( order ) {
    this.order = order;
};

manageSurvey.question.questionNumeric.prototype.updateOrder = function() {
    $( '#question_' + this.index + ' b').html( this.getOrder() + ":" );
    $( '#question_' + this.index ).attr( 'data-order', this.getOrder() );
};

manageSurvey.question.questionNumeric.prototype.render = function() {
    var num = this.index;
    var answers = this.answers ? this.answers.join(',') : '';
    var text = this.questionText ? this.questionText : '';
    var html = '<div id="question_' + num + '" class="row question dragdrop" data-order="' + this.order + '"><div class="login-form">'
        + '<div class="container-fluid">'
        +   '<div class="row">'
        +       '<div class="container-fluid">'
        +           '<div class="row">'
        +               '<div class="col-md-11">'
        +                   '<p><b>' + num + ':</b> Question à valeur numérique</p>'
        +               '</div>'
        +               '<div class="col-md-1">'
        +                   '<a href="#" class="remove-btn login-field-icon fui-cross"></a>'
        +               '</div>'
        +           '</div>'
        +           '<div class="row">'
        +               '<input maxlength="80" name="question_' + num + '" type="text" class="form-control login-field" value="' + text + '" placeholder="Texte de la question" id="questionText_' + num + '">'
        +               '<p>Interval:</p>'
        +               '<div class="form-group has-error">'
        +                 '<input type="text" class="form-control min" placeholder="Min">'
        +               '</div>'
        +               '<div class="form-group has-error">'
        +                   '<input type="text" class="form-control max" placeholder="Max">'
        +               '</div>'
        +           '</div>'
        +       '</div>'
        +   '</div>'
        + '</div>'
        + '</div></div>';
    $( '#questions').append( html );

    var self = this;

    $( '#question_' + num + ' .remove-btn').click(function(){
        manageSurvey.question.list[ num ].delete();
        $(manageSurvey).trigger( 'changed' );
    });

    $( '#questionText_' + num).keyup(function(){
        self.questionText = $( '#questionText_' + num).val();
        $(manageSurvey).trigger( 'changed' );
    });

    var foo = function( self ) {
        var min = parseInt( $( '#question_' + num + ' .min').val() );
        var max = parseInt( $( '#question_' + num + ' .max').val() );
        self.answers = [];
        self.answers.push( min );
        self.answers.push( max );
        if ( min < max ) {
            $( '#question_' + num + ' .min' ).parent().removeClass( 'has-error' );
            $( '#question_' + num + ' .min' ).parent().addClass( 'has-success' );
            $( '#question_' + num + ' .max' ).parent().removeClass( 'has-error' );
            $( '#question_' + num + ' .max' ).parent().addClass( 'has-success' );
        } else {
            $( '#question_' + num + ' .min' ).parent().addClass( 'has-error' );
            $( '#question_' + num + ' .min' ).parent().removeClass( 'has-success' );
            $( '#question_' + num + ' .max' ).parent().addClass( 'has-error' );
            $( '#question_' + num + ' .max' ).parent().removeClass( 'has-success' );
        }
        $(manageSurvey).trigger( 'changed' );
    };

    $( '#question_' + num + ' .min').keyup(function(){
        foo( self );
    });

    $( '#question_' + num + ' .max').keyup(function(){
        foo( self );
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