var PRINT = PRINT || {};
(function ($) {
    "use strict";

    PRINT = {
        init: function () {
            var ajax_url = civi_template_vars.ajax_url;
            $("body").on("click", "#btn-print-candidate", function (e) {
                var candidate_id = $(this).data('candidate-id'),
                    candidate_print_window = window.open('', candidate_print_window, 'scrollbars=0,menubar=0,resizable=1,width=991 ,height=800');
                $.ajax({
                    type: 'POST',
                    url: ajax_url,
                    data: {
                        'action': 'civi_candidate_print_ajax',
                        'nonce': civi_ajax_nonce.civi_candidate_print,
                        'candidate_id': candidate_id,
                        'isRTL': $('body').hasClass('rtl') ? 'true' : 'false'
                    },
                    success: function (html) {
                        candidate_print_window.document.write(html);
                        candidate_print_window.document.close();
                        candidate_print_window.focus();
                    }
                });
            });
        },
    };
    $(document).ready(function () {
        PRINT.init();
    });
})(jQuery);
