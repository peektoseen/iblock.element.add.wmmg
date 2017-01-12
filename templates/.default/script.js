$(document).ready(function() {

    $("input[type=text], textarea").bind("change keyup", function(e) {
        var e$ = $(e.target);
        var wrap$ = e$.parents('.line_row');
        var progress$ = $('progress', wrap$);
        var counter$ = $('.js-counter', wrap$);
        var max = e$.data('cols');

        progress$.val(e$.val().length / max * 100);
        if (e$.val().length >= max) {
            e$.val(e$.val().substr(0, max));
            counter$.html("<span class='c_red'>" + e$.val().length + "</span>");
        } else {
            counter$.html(e$.val().length);
        }
    });
/*

    $("#text_obr").bind("change keyup", function() {
        $("#counter2_pr").val($(this).val().length / 500 * 100);
        if ($(this).val().length >= 500) {
            $(this).val($(this).val().substr(0, 500));
            $("#counter2").html("<span class='c_red'>" + $(this).val().length + "</span>");
        } else {
            $("#counter2").html($(this).val().length);
        }
    });
*/

    $(".line_row_w66 .tabs_type").click(function() {
        $(".line_row_w66 .tabs_type").removeClass("active");
        $(this).addClass("active");
    });


    $('input[type=file]').on('change', function (e) {
        var wrapper$ = $(e.target).parent('.js-file-input'),
            output$ = $('img.uploaded', wrapper$);

        // если это изображение - то отображаем превью
        if(!!e.target.files[0].name.split('.').pop().toLowerCase().match(/^(jpg|gif|bmp|png|jpeg)$/)) {
            output$.attr('src', URL.createObjectURL(event.target.files[0]));
        }

        $('.media_del', wrapper$).show();

    });

    $('.js-file-input .js-del').on('click', function (e) {
        var wrapper$ = $(e.target).parents('.js-file-input'),
            input$ = $('input[type=file]', wrapper$),
            img$ = $('img.uploaded', wrapper$);
        input$.val('');
        img$.attr('src', img$.data('src'));
        $('.js-del', wrapper$).hide();
    });

    $('.js-file-input').on('click', function(e){
        var t$ = $(e.target);
        if(t$.attr('type') == 'file') return;
        if(t$.parents('.js-del').length) return;
        e.preventDefault();
        e.stopPropagation();
        if(!t$.hasClass('js-file-input')) t$ = t$.parent('.js-file-input');
        $('input[type=file]', t$).trigger('click');
    })

});
