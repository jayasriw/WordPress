(function ($) {
    "use strict";
    $(document).ready(function () {
        var search_id =  '#' + $('.archive-search-control').attr('id');
        var search_element = $(search_id);

        if (search_element.length && !search_element.hasClass('jobportal-ajax-ui')) {
            var available = search_element.data("key");
            search_element.autocomplete({
                source: available,
                minLength: 0,
                autoFocus: false,
                focus: true,
            }).focus(function () {
                $(this).data("uiAutocomplete").search($(this).val());
            });
        }

        var search_ids =
            "#" + $(".form-search-canvas .jobs-search-canvas").attr("id");
        var availables = $(search_id).data("key");
        $(search_ids)
            .autocomplete({
                source: availables,
                minLength: 0,
                autoFocus: false,
                focus: true,
            })
            .focus(function () {
                $(this).data("uiAutocomplete").search($(this).val());
            });

    });
})(jQuery);
