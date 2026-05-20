(function ($) {
    "use strict";

    var VerticalSearchHandler = function ($scope, $) {
        var search_vertical = $scope.find('.civi-search-vertical');
        var search_form = search_vertical.find('.form-search-vertical');

        search_form.each(function() {
            var post_type = $(this).find('input[name="post_type"]').val();
            var filter_search = $(this).find('.search-vertical-' + post_type);
            var available = filter_search.data("key");

            if (!filter_search.hasClass('civi-ajax-ui')) {
                filter_search.autocomplete({
                    source: available,
                    minLength: 0,
                    autoFocus: false,
                    focus: true,
                }).focus(function() {
                    $(this).data("uiAutocomplete").search($(this).val());
                });
            }
        });

        //tabs
        function tab_dashboard(obj) {
            search_vertical.find(".tab-dashboard ul li").removeClass("active");
            $(obj).addClass("active");
            var id = $(obj).find("a").attr("href");
            search_vertical.find(".tab-info").hide();
            $(id).show();
        }

        search_vertical.find(".tab-list li").on('click', function () {
            tab_dashboard(this);
            return false;
        });

        tab_dashboard($(".tab-list li:first-child"));

        search_vertical.find('.civi-ajax-select2').select2({
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

        search_vertical.find('.civi-select2').select2({
            minimumResultsForSearch: -1,
            width: '100%'
        });

        search_vertical.find('.civi-clear-top-filter').on('click', function() {
            var form = $(this).closest('.form-search-vertical');
            form.find('input[type="text"]').val('');
            form.find('.civi-select2, .civi-ajax-select2').val('').trigger('change');
        });
    };

    $(window).on("elementor/frontend/init", function () {
        elementorFrontend.hooks.addAction(
            "frontend/element_ready/civi-search-vertical.default",
            VerticalSearchHandler
        );
    });
})(jQuery);
