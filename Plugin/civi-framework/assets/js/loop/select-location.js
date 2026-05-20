jQuery(document).ready(function ($) {
    var ajax_url = civi_template_vars.ajax_url;

    $("select.civi-select-country").on('change', function() {
        var _this = $(this),
            post_type = _this.data('post-type'),
            country = _this.val(),
            state = $('.civi-select-state');

        $.ajax({
            type: "post",
            url: ajax_url,
            dataType: "json",
            data: {
                action: "civi_select_country",
                post_type: post_type,
                country: country,
            },
            beforeSend: function () {
                state.parent('.form-group').addClass('load-spinner');
                state.parent('.form-group').append('<i class="spinner fal fa-spinner fa-spin"></i>');
            },
            success: function (data) {
                if (data.success) {
                    state.parent('.form-group').removeClass('load-spinner');
                    state.parent('.form-group').find('.spinner').removeClass('fal fa-spinner fa-spin');
                    state.find('option:not(:first-child)').remove();
                    state.append(data.state_html);

                    _this.each( function() {
                        if (_this.val() !== '' || $("select.civi-select-state").val() !== '') {
                            $(".civi-nav-filter").addClass("active");
                            _this.closest(".entry-filter").addClass("open");
                            $('.archive-layout').find(".civi-clear-filter").show();
                        } else {
                            $(".civi-nav-filter").removeClass("active");
                            _this.closest(".entry-filter").removeClass("open");
                            $('.archive-layout').find(".civi-clear-filter").hide();
                        }
                    } );
                }
            },
        });
    });

    $("select.civi-select-state").on('change', function() {
        var  _this = $(this),
            post_type = _this.data('post-type'),
            state = _this.val(),
            city = $('.civi-select-city');

        $.ajax({
            type: "post",
            url: ajax_url,
            dataType: "json",
            data: {
                action: "civi_select_state",
                post_type: post_type,
                state: state,
            },
            beforeSend: function () {
                city.parent('.form-group').addClass('load-spinner');
                city.parent('.form-group').append('<i class="spinner fal fa-spinner fa-spin"></i>');
            },
            success: function (data) {
                if (data.success) {
                    city.parent('.form-group').removeClass('load-spinner');
                    city.parent('.form-group').find('.spinner').removeClass('fal fa-spinner fa-spin');
                    city.find('option:not(:first-child)').remove();
                    city.append(data.city_html);

                    _this.each( function() {
                        if (_this.val() !== '' || $("select.civi-select-country").val() !== '') {
                            $(".civi-nav-filter").addClass("active");
                            _this.closest(".entry-filter").addClass("open");
                            $('.archive-layout').find(".civi-clear-filter").show();
                        } else {
                            $(".civi-nav-filter").removeClass("active");
                            _this.closest(".entry-filter").removeClass("open");
                            $('.archive-layout').find(".civi-clear-filter").hide();
                        }
                    } );
                }
            },
        });
    });
});
