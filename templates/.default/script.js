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
            $('.js-edit', wrapper$).show();

        } else {
            $('.uploaded', wrapper$).hide();
            $('.filename', wrapper$).html(e.target.files[0].name);
        }

        $('.js-del', wrapper$).show();

    });


    $('.js-file-input .js-edit').on('click', function (e) {
        var wrapper$ = $(e.target).parents('.js-file-input'),
            fileinput$ = $('input[type=file]', wrapper$);


        $('#myModal .modal-body').html('<img>');
        $('#myModal .modal-body img').attr('src', URL.createObjectURL(fileinput$[0].files[0]));

        var Dark = new Darkroom('#myModal .modal-body img', {maxHeight: 500});
        $('#myModal').data('initiator', fileinput$.attr('id'));
        $('#myModal').modal();
    });


    $('#myModal').on('hidden.bs.modal', function (e) {
        var img_input_id = $('#myModal').data('initiator'),
            wrapper$ = $('#' + img_input_id).parents('.js-file-input');
        if (!!$('#myModal img').length && !!$('#myModal img').attr('src').match(/base64/)) {
            $('#' + img_input_id + '_hidden').val($('#myModal img').attr('src'));
            $('.uploaded', wrapper$).attr('src', $('#myModal img').attr('src'));
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
        $('.js-edit', wrapper$).hide();
        img$.show();
        if ($('.filename', wrapper$).length) $('.filename', wrapper$).html('');

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


        $(".modal-fullscreen").on('show.bs.modal', function () {
            setTimeout(function () {
                $(".modal-backdrop").addClass("modal-backdrop-fullscreen");
            }, 0);
        });
        $(".modal-fullscreen").on('hidden.bs.modal', function () {
            $(".modal-backdrop").addClass("modal-backdrop-fullscreen");
        });


    };


    $('.js_video_del').on('click', function (e) {
        e.stopPropagation();
        e.preventDefault();

        var $target = $(e.target),
            $wraper = $target.parents('.js-video-url-input');

        $('input', $wraper).val('');
        $('.video_thumb', $wraper).attr('src', window.element_add_path + '/img/upload_05.jpg');
        $target.hide();

        return false;
    });


    $.fn.VideoInput = function () {
        var $el = $(this),
            t = this,
            video_id,
            video_service;

        $('.js-video-url-input').on('click', function (e) {
            var $line_row = $(e.target).parents('.media_border');
            t.input = $('input', $line_row);

            video_id = '';
            video_service = '';
            if (!t.input.val().length) {
                $('#myModalVideo .js-video-url').val('');
                $('#myModalVideo iframe').remove();
            } else {
                $('#myModalVideo .js-video-url').val(t.input.val());
            }


            $('#myModalVideo').modal();
        });

        $('.js-check', $el).on('click', function (e) {
            var $target = $(e.target),
                $input_url = $('.js-video-url', $el),
                url = $input_url.val().replace(/^http[s]:\/\//, '');

            $input_url.val(url);


            var $form_group = $target.parents('.form-group'),
                $modal_body = $target.parents('.modal-body');

            if ($('iframe', $modal_body).length) {
                $('iframe', $modal_body).remove();
            }

            if (url.match(/youtube/)) {


                video_service = 'youtube';
                if (url.split('v=').length > 1) {
                    video_id = url.split('v=')[1];
                } else {
                    return 0;
                }


                var ampersandPosition = video_id.indexOf('&');
                if (ampersandPosition != -1) {
                    video_id = video_id.substring(0, ampersandPosition);
                }


                $form_group.after('<iframe width="' + $form_group.outerWidth() + '" height="' + $form_group.outerWidth() * 0.7 +
                    '" src="https://www.youtube.com/embed/' + video_id + '" frameborder="0" allowfullscreen></iframe>')

            } else if (url.match(/vimeo/)) {
                video_service = 'vimeo';

                video_id = url.match(/(?:www\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/).pop();

                $form_group.after('<iframe width="' + $form_group.outerWidth() + '" height="' + $form_group.outerWidth() * 0.7 +
                    '" src="https://player.vimeo.com/video/' + video_id + '" frameborder="0" allowfullscreen></iframe>');

            } else if (url.match(/rutube/)) {
                video_service = 'rutube';
                video_id = url.match(/(?:www\.)?rutube.ru\/(?:video\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)([0-9a-fA-F]+)(?:$|\/|\?)/).pop();

                $.ajax({
                    type: 'GET',
                    url: 'http://rutube.ru/api/oembed/?url=https://rutube.ru/video/' + video_id + '/&format=jsonp',
                    jsonp: 'callback',
                    dataType: 'jsonp',
                    success: function (data) {
                        var iframe = $(data.html).attr('width', $form_group.outerWidth()).attr('height', $form_group.outerWidth() * 0.7);
                        $form_group.after(iframe);
                    }
                });
            }


        });


        $('.js-apply', $el).on('click', function (e) {

            $('#myModalVideo .js-check').trigger('click');


            var $target = $(e.target),
                url = $('.js-video-url').val(),
                $line_row = t.input.parents('.media_border');

            t.input.val(url);


            if (video_service == 'youtube') {
                $('.video_thumb', $line_row).attr('src', 'https://img.youtube.com/vi/' + video_id + '/0.jpg');
            } else if (video_service == 'vimeo') {
                $.ajax({
                    type: 'GET',
                    url: 'http://vimeo.com/api/v2/video/' + video_id + '.json',
                    jsonp: 'callback',
                    dataType: 'jsonp',
                    success: function (data) {
                        var thumbnail_src = data[0].thumbnail_large;
                        $('.video_thumb', $line_row).attr('src', thumbnail_src);
                    }
                });
            } else if (video_service == 'rutube') {

                $.ajax({
                    type: 'GET',
                    url: 'http://rutube.ru/api/oembed/?url=https://rutube.ru/video/' + video_id + '/&format=jsonp',
                    jsonp: 'callback',
                    dataType: 'jsonp',
                    success: function (data) {
                        var thumbnail_src = data.thumbnail_url;
                        $('.video_thumb', $line_row).attr('src', thumbnail_src);
                    }
                });

            }


            $('.media_del', $line_row).show();

        })

    };


    $('#myModalVideo').VideoInput();


});
