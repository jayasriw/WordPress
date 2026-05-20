var JOBPORTAL_SERVICE_STRIPE = JOBPORTAL_SERVICE_STRIPE || {};
(function ($) {
  "use strict";

  JOBPORTAL_SERVICE_STRIPE = {
    init: function () {
      this.setupForm();
    },

    setupForm: function () {
      var self = this,
        $form = $(".jobportal-candidate-stripe-form");
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
    JOBPORTAL_SERVICE_STRIPE.init();

    if (typeof jobportal_payment_vars !== "undefined") {
      var ajax_url = jobportal_payment_vars.ajax_url;
      var processing_text = jobportal_payment_vars.processing_text;

      $("#jobportal_payment_candidate_package").on("click", function (event) {
        var payment_method = $(
          "input[name='jobportal_candidate_payment_method']:checked"
        ).val();
        var candidate_package_id = $(
          "input[name='jobportal_candidate_package_id']"
        ).val();
        var coupon_amount = $("input[name='coupon_amount']").val();
        if (payment_method == "paypal") {
          jobportal_candidate_paypal_payment_per_package(
            candidate_package_id,
            coupon_amount
          );
        } else if (payment_method == "stripe") {
          $("#jobportal_stripe_candidate_per_package button").trigger("click");
        } else if (payment_method == "razor") {
          jobportal_razor_package_candidate_addons(
            candidate_package_id,
            coupon_amount
          );
        } else if (payment_method == "wire_transfer") {
          jobportal_candidate_wire_transfer_per_package(
            candidate_package_id,
            coupon_amount
          );
        } else if (payment_method == "woocheckout") {
          jobportal_candidate_woocommerce_payment_per_package(
            candidate_package_id,
            coupon_amount
          );
        }
      });

      var jobportal_candidate_paypal_payment_per_package = function (
        candidate_package_id,
        coupon_amount = 0
      ) {
        $.ajax({
          type: "POST",
          url: ajax_url,
          data: {
            action: "jobportal_candidate_paypal_payment_per_package_ajax",
            candidate_package_id: candidate_package_id,
            coupon_amount: coupon_amount,
            jobportal_candidate_security_payment: $(
              "#jobportal_candidate_security_payment"
            ).val(),
          },
          beforeSend: function () {
            $("#jobportal_payment_candidate_package").append(
              '<div class="jobportal-loading-effect"><span class="jobportal-dual-ring"></span></div>'
            );
          },
          success: function (data) {
            window.location.href = data;
          },
        });
      };

      function jobportal_razor_package_candidate_addons(
        candidate_package_id,
        coupon_amount = 0
      ) {
        $.ajax({
          type: "POST",
          url: ajax_url,
          data: {
            action: "jobportal_razor_package_candidate_create_order",
            candidate_package_id: candidate_package_id,
            coupon_amount: coupon_amount,
            jobportal_candidate_security_payment: $(
              "#jobportal_candidate_security_payment"
            ).val(),
            total_price: $(".package-project")
              .find('input[name="total_price"]')
              .val(),
          },
          beforeSend: function () {
            $("#jobportal_payment_candidate_package").append(
              '<div class="jobportal-loading-effect"><span class="jobportal-dual-ring"></span></div>'
            );
          },
          success: function (order) {
            order = JSON.parse(order);
            // Payment was closed without handler getting called
            order.modal = {
              ondismiss: function () {
                setDisabled("jobportal_payment_project", false);
              },
            };

            order.handler = function (payment) {
              document.getElementById("razorpay_payment_id").value =
                payment.razorpay_payment_id;
              document.getElementById("razorpay_signature").value =
                payment.razorpay_signature;
              // document.razorpayform.submit();

              var form_data = $("#jobportal_razor_paymentform").serializeArray();
              $.ajax({
                url: ajax_url,
                data: {
                  action: "jobportal_candidate_razor_package_verify",
                  candidate_package_id: $(
                    'input[name="jobportal_candidate_package_id"]'
                  ).val(),
                  razorpay_payment_id: $("#razorpay_payment_id").val(),
                  razorpay_order_id: order.order_id,
                  razorpay_signature: $("#razorpay_signature").val(),
                },
                type: "POST",
                success: function (response) {
                  if (response) {
                    window.location.href = response;
                  }
                },
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

      var jobportal_stripe_candidate_per_package = function (candidate_package_id) {
        $.ajax({
          type: "POST",
          url: ajax_url,
          data: {
            action: "jobportal_candidate_paypal_payment_per_package_ajax",
            candidate_package_id: candidate_package_id,
            jobportal_candidate_security_payment: $(
              "#jobportal_candidate_security_payment"
            ).val(),
          },
          beforeSend: function () {
            $("#jobportal_payment_candidate_package").append(
              '<div class="jobportal-loading-effect"><span class="jobportal-dual-ring"></span></div>'
            );
          },
          success: function (data) {
            window.location.href = data;
          },
        });
      };

      var jobportal_candidate_wire_transfer_per_package = function (
        candidate_package_id,
        coupon_amount = 0
      ) {
        $.ajax({
          type: "POST",
          url: ajax_url,
          data: {
            action: "jobportal_candidate_wire_transfer_per_package_ajax",
            candidate_package_id: candidate_package_id,
            coupon_amount: coupon_amount,
            jobportal_candidate_security_payment: $(
              "#jobportal_candidate_security_payment"
            ).val(),
          },
          beforeSend: function () {
            $("#jobportal_payment_candidate_package").append(
              '<div class="jobportal-loading-effect"><span class="jobportal-dual-ring"></span></div>'
            );
          },
          success: function (data) {
            window.location.href = data;
          },
        });
      };

      $("#jobportal_free_candidate_package").on("click", function () {
        var candidate_package_id = $(
          "input[name='jobportal_candidate_package_id']"
        ).val();
        $.ajax({
          type: "POST",
          url: ajax_url,
          data: {
            action: "jobportal_candidate_free_package_ajax",
            candidate_package_id: candidate_package_id,
            jobportal_candidate_security_payment: $(
              "#jobportal_candidate_security_payment"
            ).val(),
          },
          beforeSend: function () {
            $("#jobportal_payment_candidate_package").append(
              '<div class="jobportal-loading-effect"><span class="jobportal-dual-ring"></span></div>'
            );
          },
          success: function (data) {
            window.location.href = data;
          },
        });
      });

      var jobportal_candidate_woocommerce_payment_per_package = function (
        candidate_package_id,
        coupon_amount = 0
      ) {
        $.ajax({
          type: "POST",
          url: ajax_url,
          data: {
            action: "jobportal_candidate_woocommerce_payment_per_package_ajax",
            candidate_package_id: candidate_package_id,
            coupon_amount: coupon_amount,
            jobportal_candidate_security_payment: $(
              "#jobportal_candidate_security_payment"
            ).val(),
          },
          beforeSend: function () {
            $("#jobportal_payment_candidate_package").append(
              '<div class="jobportal-loading-effect"><span class="jobportal-dual-ring"></span></div>'
            );
          },
          success: function (data) {
            window.location.href = data;
          },
        });
      };
    }
  });
})(jQuery);
