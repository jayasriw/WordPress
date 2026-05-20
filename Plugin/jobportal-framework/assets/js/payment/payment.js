var JOBPORTAL_STRIPE = JOBPORTAL_STRIPE || {};
(function ($) {
  "use strict";

  JOBPORTAL_STRIPE = {
    init: function () {
      this.setupForm();
    },

    setupForm: function () {
      var self = this,
        $form = $(".jobportal-stripe-form");
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
      var ajax_url = jobportal_payment_vars.ajax_url;
      var processing_text = jobportal_payment_vars.processing_text;

      $("#jobportal_payment_package").on("click", function (event) {
        var payment_method = $(
          "input[name='jobportal_payment_method']:checked"
        ).val();
        var package_id = $("input[name='jobportal_package_id']").val();
        var coupon_amount = $("input[name='coupon_amount']").val();
        if (payment_method == "paypal") {
          jobportal_paypal_payment_per_package(package_id, coupon_amount);
        } else if (payment_method == "stripe") {
          jobportal_stripe_create_invoice_per_package(package_id, coupon_amount);
        } else if (payment_method == "razor") {
          jobportal_razor_package_addons(package_id, coupon_amount);
        } else if (payment_method == "wire_transfer") {
          jobportal_wire_transfer_per_package(package_id, coupon_amount);
        } else if (payment_method == "woocheckout") {
          jobportal_woocommerce_payment_per_package(package_id, coupon_amount);
        }
      });

      var jobportal_paypal_payment_per_package = function (
        package_id,
        coupon_amount = 0
      ) {
        $.ajax({
          type: "POST",
          url: ajax_url,
          data: {
            action: "jobportal_paypal_payment_per_package_ajax",
            package_id: package_id,
            coupon_amount: coupon_amount,
            jobportal_security_payment: $("#jobportal_security_payment").val(),
          },
          beforeSend: function () {
            $("#jobportal_payment_package").append(
              '<div class="jobportal-loading-effect"><span class="jobportal-dual-ring"></span></div>'
            );
          },
          success: function (data) {
            window.location.href = data;
          },
        });
      };

      var jobportal_stripe_create_invoice_per_package = function (
        package_id,
        coupon_amount = 0
      ) {
        $.ajax({
          type: "POST",
          url: ajax_url,
          dataType: "json",
          data: {
            action: "jobportal_stripe_create_invoice_per_package",
            package_id: package_id,
            coupon_amount: coupon_amount,
            jobportal_security_payment: $("#jobportal_security_payment").val(),
          },
          beforeSend: function () {
            $("#jobportal_payment_package").append(
              '<div class="jobportal-loading-effect"><span class="jobportal-dual-ring"></span></div>'
            );
          },
          success: function (response) {
            if (response && response.success) {
              $("#stripe_invoice_id").val(response.data.invoice_id);
              $("#jobportal_stripe_per_package button").trigger("click");
            } else {
              window.location.reload();
            }
          },
          error: function () {
            window.location.reload();
          },
        });
      };

      function jobportal_razor_package_addons(package_id, coupon_amount = 0) {
        $.ajax({
          type: "POST",
          url: ajax_url,
          data: {
            action: "jobportal_razor_package_create_order",
            package_id: package_id,
            coupon_amount: coupon_amount,
            jobportal_employer_security_payment: $(
              "#jobportal_employer_security_payment"
            ).val(),
            total_price: $(".package-project")
              .find('input[name="total_price"]')
              .val(),
          },
          beforeSend: function () {
            $("#jobportal_payment_employer_package").append(
              '<div class="jobportal-loading-effect"><span class="jobportal-dual-ring"></span></div>'
            );
          },
          success: function (order) {
            order = JSON.parse(order);
            var pending_invoice_id = order.invoice_id || "";
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
                  action: "jobportal_razor_payment_verify",
                  package_id: $('input[name="jobportal_package_id"]').val(),
                  razorpay_payment_id: $("#razorpay_payment_id").val(),
                  razorpay_order_id: order.order_id,
                  razorpay_signature: $("#razorpay_signature").val(),
                  invoice_id: pending_invoice_id,
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

      var jobportal_wire_transfer_per_package = function (
        package_id,
        coupon_amount = 0
      ) {
        $.ajax({
          type: "POST",
          url: ajax_url,
          data: {
            action: "jobportal_wire_transfer_per_package_ajax",
            package_id: package_id,
            coupon_amount: coupon_amount,
            jobportal_security_payment: $("#jobportal_security_payment").val(),
          },
          beforeSend: function () {
            $("#jobportal_payment_package").append(
              '<div class="jobportal-loading-effect"><span class="jobportal-dual-ring"></span></div>'
            );
          },
          success: function (data) {
            window.location.href = data;
          },
        });
      };

      $("#jobportal_free_package").on("click", function () {
        var package_id = $("input[name='jobportal_package_id']").val();
        $.ajax({
          type: "POST",
          url: ajax_url,
          data: {
            action: "jobportal_free_package_ajax",
            package_id: package_id,
            jobportal_security_payment: $("#jobportal_security_payment").val(),
          },
          beforeSend: function () {
            $("#jobportal_free_package").append(
              '<div class="jobportal-loading-effect"><span class="jobportal-dual-ring"></span></div>'
            );
          },
          success: function (data) {
            window.location.href = data;
          },
        });
      });

      var jobportal_woocommerce_payment_per_package = function (
        package_id,
        coupon_amount = 0
      ) {
        $.ajax({
          type: "POST",
          url: ajax_url,
          data: {
            action: "jobportal_woocommerce_payment_per_package_ajax",
            package_id: package_id,
            coupon_amount: coupon_amount,
            jobportal_security_payment: $("#jobportal_security_payment").val(),
          },
          beforeSend: function () {
            $("#jobportal_payment_package").append(
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
