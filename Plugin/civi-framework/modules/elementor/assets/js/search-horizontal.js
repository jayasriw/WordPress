(function ($) {
    "use strict";

    var HorizontalSearchHandler = function ($scope, $) {
        var search_form = $scope.find('.civi-search-horizontal');
        var filter_search = search_form.find('#search-horizontal_filter_search');

        if (!filter_search.hasClass('civi-ajax-ui')) {
            filter_search.autocomplete({
                minLength: 0,
                delay: 500,
                source: function (request, response) {
                    var spinner = search_form.find('.ui-autocomplete-spinner');
                    spinner.show();

                    $.ajax({
                        url: theme_vars.ajax_url,
                        dataType: "json",
                        data: {
                            action: 'civi_ajax_ui',
                            q: request.term || '',
                            taxonomy: filter_search.attr('data-taxonomy'),
                        },
                        success: function (data) {
                            if (Array.isArray(data)) {
                                response(data);
                            } else if (data && Array.isArray(data.results)) {
                                response(data.results);
                            } else {
                                response([]);
                            }
                        },
                        complete: function () {
                            spinner.hide();
                        }
                    });
                },
                autoFocus: false
            });

            filter_search.on("focus", function () {
                if ($(this).val().length >= 0) {
                    $(this).autocomplete("search", $(this).val());
                }
            });
        }

        search_form.find('.civi-ajax-select2').select2({
            minimumInputLength: 0,
            ajax: {
                url: theme_vars.ajax_url,
                dataType: 'json',
                delay: 500,
                data: function (params) {
                    return {
                        action: "civi_ajax_select2",
                        q: params.term || '',
                        page: params.page || 1,
                        taxonomy: $(this).attr('data-taxonomy'),
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.results || [],
                        pagination: data.pagination || { more: false }
                    };
                },
                cache: true
            },
            width: '100%'
        });

        search_form.find('.civi-select2').each(function(){
            var $el = $(this);
            var placeholder = $el.attr('data-placeholder') || '';
            $el.select2({
                minimumResultsForSearch: 0,
                allowClear: false,
                placeholder: placeholder,
                width: '100%'
            });
        });

        search_form.find(".civi-clear-top-filter").on("click", function () {
            filter_search.val("");
            search_form.find(".input-search-location").val("");

            search_form.find(".civi-select2, .civi-ajax-select2").val("").trigger("change");

            var updateSelect2Level = function ($options) {
                $options.each(function () {
                    var option_val = $(this).val();
                    var level = $(this).attr('data-level');
                    $('.select2-results li[id$="' + option_val + '"]').attr('data-level', level);
                });
            };

            $('.select2.select2-container').off('click.updateLevel').on('click.updateLevel', function () {
                updateSelect2Level($(this).prev().find('option'));
            });
            $('.civi-form-location .icon-arrow i').off('click.updateLevel').on('click.updateLevel', function () {
                updateSelect2Level($(this).closest('.civi-form-location').find('select.civi-select2 option'));
            });

            $("body").off("mousedown.civiFormLocation").on("mousedown.civiFormLocation", ".civi-form-location .icon-arrow i", function (e) {
                e.preventDefault();
                var form = $(".archive-layout .civi-form-location");
                var field_select = form.find('select.civi-select2');
                var select2_container = form.find('.select2.select2-container');
                if (select2_container.hasClass('select2-container--open')) {
                    field_select.select2("close");
                } else {
                    field_select.val(null).trigger("change");
                    field_select.select2("open");
                }
            });
        });
    };

    $(window).on("elementor/frontend/init", function () {
        elementorFrontend.hooks.addAction(
            "frontend/element_ready/civi-search-horizontal.default",
            HorizontalSearchHandler
        );
    });
})(jQuery);
