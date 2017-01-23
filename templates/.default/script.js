$(document).ready(function () {

    $("input[type=text], textarea").bind("change keyup", function (e) {
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

    $(".line_row_w66 .tabs_type").click(function () {
        $(".line_row_w66 .tabs_type").removeClass("active");
        $(this).addClass("active");
    });

    $('input[type=file]').on('change', function (e) {
        var wrapper$ = $(e.target).parent('.js-file-input'),
            output$ = $('img.uploaded', wrapper$),
            fileinput$ = $(e.target);

        $('#' + fileinput$.attr('id') + '_hidden').val('');

        // если это изображение - то отображаем превью
        if (!!e.target.files[0].name.split('.').pop().toLowerCase().match(/^(jpg|gif|bmp|png|jpeg)$/)) {
            output$.attr('src', URL.createObjectURL(event.target.files[0]));

            $('#myModal .modal-body').html('<img>');
            $('#myModal .modal-body img').attr('src', URL.createObjectURL(event.target.files[0]));

            var Dark = new Darkroom('#myModal .modal-body img', {maxHeight: 500});
            $('#myModal').data('initiator', fileinput$.attr('id'));
            $('#myModal').modal();
        }

        $('.media_del', wrapper$).show();

    });


    $('#myModal').on('hidden.bs.modal', function (e) {
        var img_input_id = $('#myModal').data('initiator');
        if (!!$('#myModal img').length && !!$('#myModal img').attr('src').match(/base64/)) {
            $('#' + img_input_id + '_hidden').val($('#myModal img').attr('src'));
        }
    });

    $('.js-file-input .js-del').on('click', function (e) {
        var wrapper$ = $(e.target).parents('.js-file-input'),
            input$ = $('input[type=file]', wrapper$),
            img$ = $('img.uploaded', wrapper$);
        input$.val('');
        $('#' + input$.attr('id') + '_hidden').val('');
        img$.attr('src', img$.data('src'));
        $('.js-del', wrapper$).hide();
    });

    $('.js-file-input').on('click', function (e) {
        var t$ = $(e.target);
        if (t$.attr('type') == 'file') return;
        if (t$.parents('.js-del').length) return;
        e.preventDefault();
        e.stopPropagation();
        if (!t$.hasClass('js-file-input')) t$ = t$.parent('.js-file-input');
        $('input[type=file]', t$).trigger('click');
    });

    $.fn.FormAdd = function (options) {
        var $wrapper = this,
            $agreement_wrapper = $('#id_orders');

        if ($agreement_wrapper.length) {
            $('input[type=checkbox]').on('change', function (e) {
                if ($(e.target).is(':checked')) {
                    $('.epic_big_btn').removeAttr('disabled');
                } else {
                    $('.epic_big_btn').attr('disabled', 'disabled');
                }
            })
        }

        $('.js-section', $wrapper).on('change', function (e) {
            var $t = $(e.target);
            if (!$t.val()) return;
            $.ajax('', {
                data: {id: $t.val(), component: options.component, ajax: 'Y', action: 'get_subsections'},
                method: 'POST',
                dataType: 'json',
                success: function (data) {
                    $('.js-subsection', $wrapper).html('');
                    for (var key in data) {
                        var el = $('<option value=\'' + key + '\'>' + data[key]['VALUE'] + '</option>');
                        $('.js-subsection', $wrapper).append(el);
                    }
                }
            })
        });

        $('.js-radiobutton label', $wrapper).on('click', function (e) {
            var $target = $(e.target),
                $wrapper = $target.parents('.js-radiobutton');

            if ($target.is('input')) return true;

            $target = $target.parents('label').length ? $target.parents('label') : $target;

            $('input[type=radio]', $wrapper).attr('checked', false);
            $('input[type=radio]', $target).trigger('click');
        });


    };


});
