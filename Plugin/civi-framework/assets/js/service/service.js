var SERVICE = SERVICE || {};
(function ($) {
    "use strict";

    SERVICE = {
        init: function () {
            this.total_price();
            this.submit_addons();
        },

        total_price: function () {
            var packageAddons = $('.package-addons'),
                packageTotal = $('.package-total').find('.number'),
                startPrice = parseFloat($('.package-total').data('start-price')) || 0;

            // Helper function for accurate rounding with validation
            var roundPrice = function(value) {
                var num = parseFloat(value);
                // Validate: handle NaN, Infinity, negative values
                if (isNaN(num) || !isFinite(num) || num < 0) {
                    return 0;
                }
                return Math.round((num + Number.EPSILON) * 100) / 100;
            };

            packageAddons.find('input[type="checkbox"]').on('click', function(){
                var priceTotal = roundPrice(startPrice);
                packageAddons.find('input:checkbox:checked').each(function(){
                    var addonPrice = roundPrice($(this).val());
                    priceTotal = roundPrice(priceTotal + addonPrice);
                });
                packageTotal.text(priceTotal.toFixed(2));

                var priceAddons = 0;
                packageAddons.find('input:checkbox:checked').each(function(){
                    var addonPrice = roundPrice($(this).val());
                    priceAddons = roundPrice(priceAddons + addonPrice);
                });
                $('.service-package-sidebar').find('input[name="price_addons"]').val(priceAddons.toFixed(2));
            });
        },

        submit_addons: function () {
            var ajax_url = civi_template_vars.ajax_url,
                payment_url = civi_addons_vars.payment_url;

            $("body").on("click", "#btn-submit-addons", function (e) {
                var packageWarrper = $('.service-package-sidebar'),
                    service_id = packageWarrper.find('input[name="service_id').val(),
                    price_total_raw = packageWarrper.find('.package-total .number').text(),
                    price_addons_raw = packageWarrper.find('input[name="price_addons').val();

                // Round prices with Number.EPSILON for accuracy and validation
                var roundPrice = function(value) {
                    var num = parseFloat(value);
                    // Validate: handle NaN, Infinity, negative values
                    if (isNaN(num) || !isFinite(num) || num < 0) {
                        return 0;
                    }
                    return Math.round((num + Number.EPSILON) * 100) / 100;
                };
                var price_total = roundPrice(price_total_raw).toFixed(2);
                var price_addons = roundPrice(price_addons_raw).toFixed(2);

                e.preventDefault();
                $.ajax({
                    type: "post",
                    url: ajax_url,
                    dataType: "json",
                    data: {
                        action: "civi_service_addons",
                        service_id: service_id,
                        price_total: price_total,
                        price_addons: price_addons,
                        nonce: civi_ajax_nonce.service_addons,
                    },
                    beforeSend: function () {
                        packageWarrper.find(".btn-loading").fadeIn();
                    },
                    success: function (data) {
                        if (data.success == true) {
                            window.location.href = payment_url;
                        }
                        packageWarrper.find(".btn-loading").fadeOut();
                    },
                });
            });
        },
    };
    $(document).ready(function () {
        SERVICE.init();
    });
})(jQuery);
