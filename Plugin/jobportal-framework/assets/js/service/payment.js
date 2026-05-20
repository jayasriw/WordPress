var JOBPORTAL_STRIPE = JOBPORTAL_STRIPE || {};
(function ($) {
    "use strict";

    JOBPORTAL_STRIPE = {
        init: function () {
            this.setupForm();
        },

        setupForm: function () {
            var self = this,
                $form = $(".jobportal-service-stripe-form");
            if ($form.length === 0) return;
            var formId = $form.attr("id");
            // Set formData array index of the current form ID to match the localized data passed over for form settings.
            var formData = jobportal_stripe_vars[formId];
            // Variable to hold the Stripe configuration.
            var stripeHandler = null;
            var $submitBtn = $form.find(".jobportal-stripe-button");

            if ($submitBtn.length) {
                stripeHandler = StripeCheckout.configure({
                    // Key param MUST be sent hcivi instead of stripeHandler.open().
                    key: formData.key,
                    locale: "auto",
                    token: function (token, args) {
                        $("<input>")
                            .attr({
                                type: "hidden",
                                name: "stripeToken",
                                value: token.id,
                            })
                            .appendTo($form);

                        $("<input>")
                            .attr({
                                type: "hidden",
                                name: "stripeTokenType",
                                value: token.type,
                            })
                            .appendTo($form);

                        if (token.email) {
                            $("<input>")
                                .attr({
                                    type: "hidden",
                                    name: "stripeEmail",
                                    value: token.email,
                                })
                                .appendTo($form);
                        }
                        $form.submit();
                    },
                });

                $submitBtn.on("click", function (event) {
                    event.preventDefault();
                    stripeHandler.open(formData.params);
                });
            }

            // Close Checkout on page navigation:
            window.addEventListener("popstate", function () {
                if (stripeHandler != null) {
                    stripeHandler.close();
                }
            });
        },
    };

    $(document).ready(function () {
        JOBPORTAL_STRIPE.init();

        var show_loading = function ($text) {
            if ($text == "undefined" || $text == "" || $text == null) {
                $text = loading_text;
            }
            var template = wp.template("jobportal-processing-template");
            $("body").append(template({ ico: "fa fa-spinner fa-spin", text: $text }));
        };

        if (typeof jobportal_payment_vars !== "undefined") {
            var ajax_url = jobportal_template_vars.ajax_url;

            $("#jobportal_payment_service").on("click", function (event) {
                var payment_method = $(
                    "input[name='jobportal_payment_method']:checked"
                ).val();
                var service_id = $('.payment-wrap').find("input[name='service_id']").val();
                if (payment_method == "paypal") {
                    jobportal_paypal_payment_service_addons(service_id);
                } else if (payment_method == "stripe") {
                    $("#jobportal_stripe_service_addons button").trigger("click");
                } else if (payment_method == "wire_transfer") {
                    jobportal_wire_transfer_service_addons(service_id);
                } else if (payment_method == 'woocheckout') {
                    jobportal_woocommerce_payment_service_addons(service_id);
                }else if (payment_method == "razor") {
                    jobportal_razor_payment_project_addons(service_id);
                }
            });

            var jobportal_paypal_payment_service_addons = function (service_id) {
                $.ajax({
                    type: "POST",
                    url: ajax_url,
                    data: {
                        action: "jobportal_paypal_payment_service_addons",
                        service_id: service_id,
                        jobportal_service_security_payment: $("#jobportal_service_security_payment").val(),
                    },
                    beforeSend: function () {
                        $("#jobportal_payment_service").append(
                            '<div class="jobportal-loading-effect"><span class="jobportal-dual-ring"></span></div>'
                        );
                    },
                    success: function (data) {
                        window.location.href = data;
                    },
                });
            };

            var jobportal_wire_transfer_service_addons = function (service_id) {
                $.ajax({
                    type: "POST",
                    url: ajax_url,
                    data: {
                        action: "jobportal_wire_transfer_service_addons",
                        service_id: service_id,
                        jobportal_service_security_payment: $("#jobportal_service_security_payment").val(),
                    },
                    beforeSend: function () {
                        $("#jobportal_payment_service").append(
                            '<div class="jobportal-loading-effect"><span class="jobportal-dual-ring"></span></div>'
                        );
                    },
                    success: function (data) {
                        window.location.href = data;
                    },
                });
            };

            function jobportal_razor_payment_project_addons (service_id) {
                let package_addons = [];

                $('.package-addons input[type="checkbox"]:checked').each(function() {
                    let title = $(this).siblings('label').find('.title').text();
                    let deliveryTime = $(this).data('delivery-time');
                    let value = $(this).val();

                    // Push the addon details to the array
                    package_addons.push({
                        title: title,
                        deliveryTime: deliveryTime,
                        value: value
                    });
                });

                $.ajax({
                    type: "POST",
                    url: ajax_url,
                    data: {
                        action: "jobportal_razor_service_create_order",
                        service_id: service_id,
                        jobportal_service_security_payment: $(
                            "#jobportal_service_security_payment"
                        ).val(),
                        total_price: $(".package-service")
                            .find('input[name="total_price"]')
                            .val(),
                    },
                    beforeSend: function () {
                        $("#jobportal_payment_service").append(
                            '<div class="jobportal-loading-effect"><span class="jobportal-dual-ring"></span></div>'
                        );
                    },
                    success: function (order) {
                        order = JSON.parse( order );
                        // Payment was closed without handler getting called
                        order.modal = {
                            ondismiss: function() {
                                setDisabled('jobportal_payment_service', false);
                            },
                        };

                        order.handler = function(payment) {
                            document.getElementById('razorpay_payment_id').value =
                                payment.razorpay_payment_id;
                            document.getElementById('razorpay_signature').value =
                                payment.razorpay_signature;
                            // document.razorpayform.submit();

                            $.ajax({
                                url: ajax_url,
                                data: {
                                    action: "jobportal_razor_service_payment_verify",
                                    razorpay_payment_id: $( '#razorpay_payment_id' ).val(),
                                    razorpay_order_id: order.order_id,
                                    razorpay_signature: $( '#razorpay_signature' ).val(),
                                    package_time: $(".package-service").find( 'input[name="package_time"]' ).val(),
                                    package_time_type: $(".package-service").find( 'input[name="package_time_type"]' ).val(),
                                    price_default: $(".package-service").find( 'input[name="package_price"]' ).val(),
                                    package_des: $(".package-service").find( 'input[name="package_des"]' ).val(),
                                    package_addons: package_addons,
                                    package_new: $(".package-service").find( 'input[name="package_new"]' ).val(),
                                },
                                type: 'POST',
                                success: function(response){
                                    if (response) {
                                        window.location.href = response
                                    }
                                }
                            });
                        };
                        openCheckout(order);
                    },
                });
            }

            // global method
            function openCheckout(order) {
                var razorpayCheckout = new Razorpay(order);
                razorpayCheckout.open();
            }

            var jobportal_woocommerce_payment_service_addons = function (service_id) {
                $.ajax({
                    type: 'POST',
                    url: ajax_url,
                    data: {
                        'action': 'jobportal_woocommerce_payment_service_addons',
                        'service_id': service_id,
                        'jobportal_service_security_payment': $('#jobportal_service_security_payment').val()
                    },
                    beforeSend: function () {
                        $('#jobportal_payment_service').append('<div class="jobportal-loading-effect"><span class="jobportal-dual-ring"></span></div>');
                    },
                    success: function (data) {
                        window.location.href = data;
                    },
                });
            };
        }
    });
})(jQuery);
