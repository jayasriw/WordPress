var DOWNLOADCV = DOWNLOADCV || {};
(function ($) {
    "use strict";

    DOWNLOADCV = {
        init: function () {
            var ajax_url = civi_template_vars.ajax_url;
            var package_expires = civi_template_vars.package_expires;
            $("body").on("click", "#btn-download-cv-candidate", function () {
                $.ajax({
                    type: 'POST',
                    url: ajax_url,
                    data: {
                        'action': 'civi_candidate_download_cv',
                        'nonce': civi_ajax_nonce.download_cv_candidate
                    },
                    dataType: 'json',
                    success: function (data) {
                        if(data.message){
                            alert(package_expires);
                            window.location.reload();
                        }
                    },
                });
            });
        },
    };
    $(document).ready(function () {
        DOWNLOADCV.init();
    });
})(jQuery);
