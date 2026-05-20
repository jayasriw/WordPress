var GLF = GLF || {};

(function ($) {
  "use strict";

  // Performance: Throttle function for scroll events
  function throttle(func, limit) {
    var inThrottle;
    return function() {
      var args = arguments;
      var context = this;
      if (!inThrottle) {
        func.apply(context, args);
        inThrottle = true;
        setTimeout(function() { inThrottle = false; }, limit);
      }
    };
  }

  GLF.element = {
    init: function () {
      GLF.element.click_outside();
      GLF.element.package_coupon();
      GLF.element.payment_method();
      GLF.element.select2();
      GLF.element.sticky_element();
      GLF.element.click_to_demo();
      GLF.element.toggle_panel();
      GLF.element.toggle_payout();
      GLF.element.toggle_faq();
      GLF.element.toggle_social();
      GLF.element.toggle_content();
      GLF.element.nav_scroll();
      GLF.element.filter_toggle();
      GLF.element.slick_carousel();
      GLF.element.back_top_top();

      GLF.element.click_outside(".input-field", ".focus-result");
      GLF.element.click_outside(".location-field", ".focus-result");
      GLF.element.click_outside(".type-field", ".focus-result");

      $(".toggle-select").on("click", ".toggle-show", function () {
        $(this).closest(".toggle-select").find(".toggle-list").slideToggle();
      });
      GLF.element.click_outside(".toggle-select", ".toggle-list", "slide");
    },

    scroll_to: function (element) {
      var offset = $(element).offset().top;
      $("html, body").animate(
        {
          scrollTop: offset - 100,
        },
        500
      );
    },

    click_to_demo: function () {
      $(".menu a").on("click", function (e) {
        var id = $(this).attr("href");
        if (id == "#demo") {
          e.preventDefault();
          scroll_to(id);
        }
      });
    },

    click_outside: function (element, child, type) {
      $(document).on("click", function (event) {
        var $this = $(element);
        if ($this !== event.target && !$this.has(event.target).length) {
          if (type) {
            if (child) {
              $this.find(child).slideUp();
            } else {
              $this.slideUp();
            }
          } else {
            if (child) {
              $this.find(child).hide();
            } else {
              $this.hide();
            }
          }
        }
      });
    },

    package_coupon: function () {
      $(".package-coupon-form").on("submit", function (e) {
        e.preventDefault();
        var $this = $(this);
        var $form = $(this).serialize();
        var coupon_code = $(this)
          .closest(".payment-wrap")
          .find("input[name='coupon_code']");
        var coupon_amount = $(this)
          .closest(".payment-wrap")
          .find("input[name='coupon_amount']");
        var civi_coupon = $(this).find('input[name="civi_coupon"]');
        if (coupon_code.val() != "" && coupon_code.val() == civi_coupon.val()) {
          $(".package-coupon-result").removeClass("success");
          $(".package-coupon-result").removeClass("error");
          $(".package-coupon-result").addClass("warning");
          $(".package-coupon-result").html(theme_vars.coupon_applied);
          return false;
        }

        $.ajax({
          type: "POST",
          url: theme_vars.ajax_url,
          data: $form,
          dataType: "json",
          beforeSend: function () {
            $this.addClass("loading");
            $(".package-coupon-result").html("");
            $(".package-coupon-result").removeClass("success");
            $(".package-coupon-result").removeClass("error");
            $(".package-coupon-result").removeClass("warning");
          },
          success: function (data) {
            $this.removeClass("loading");
            if (data.success) {
              coupon_code.val(data.coupon_code);
              coupon_amount.val(data.coupon_amount);
              $(".package-coupon-result").addClass("success");
              $(".package-coupon-result").html(data.message);
              $(".civi-total-price")
                .find(".old-price")
                .html(data.package_price_format);
              $(".civi-total-price")
                .find(".price")
                .html(data.package_price_with_coupon);
              $(".civi-stripe-form")
                .find('input[name="payment_money"]')
                .val(data.stripe_price);
              // Update the amount in civi_stripe_vars
              if (typeof civi_stripe_vars !== "undefined") {
                if (
                  typeof civi_stripe_vars.civi_stripe_per_package !==
                  "undefined"
                ) {
                  civi_stripe_vars.civi_stripe_per_package.params.amount =
                    data.stripe_price;
                }
                if (
                  typeof civi_stripe_vars.civi_stripe_candidate_per_package !==
                  "undefined"
                ) {
                  civi_stripe_vars.civi_stripe_candidate_per_package.params.amount =
                    data.stripe_price;
                }
              }
            } else {
              coupon_code.val("");
              coupon_amount.val("");
              $(".civi-total-price").find(".old-price").html("");
              $(".civi-total-price")
                .find(".price")
                .html(data.package_price_format);
              $(".civi-stripe-form")
                .find('input[name="payment_money"]')
                .val(data.stripe_price);
              // Update the amount in civi_stripe_vars
              if (typeof civi_stripe_vars !== "undefined") {
                if (
                  typeof civi_stripe_vars.civi_stripe_per_package !==
                  "undefined"
                ) {
                  civi_stripe_vars.civi_stripe_per_package.params.amount =
                    data.stripe_price;
                }
                if (
                  typeof civi_stripe_vars.civi_stripe_candidate_per_package !==
                  "undefined"
                ) {
                  civi_stripe_vars.civi_stripe_candidate_per_package.params.amount =
                    data.stripe_price;
                }
              }
              $(".package-coupon-result").addClass("error");
              $(".package-coupon-result").html(data.message);
            }
          },
        });
      });
    },

    payment_method: function () {
      $(".civi-payment-method-wrap .radio").on("click", function () {
        $(".civi-payment-method-wrap .radio").removeClass("active");
        $(this).addClass("active");
      });
    },

    select2: function () {
      var select2 = "";

      $(".civi-select2").each(function () {
        var option = $(this).find("option");
        if (theme_vars.enable_search_box_dropdown == "1") {
          if (option.length > theme_vars.limit_search_box) {
            select2 = $(this).select2();
          } else {
            select2 = $(this).select2({
              minimumResultsForSearch: -1,
            });
          }
        } else {
          select2 = $(this).select2({
            minimumResultsForSearch: -1,
          });
        }
      });

      $('.civi-ajax-select2').select2({
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

      if ($(".elementor-editor-active").length) {
        elementorFrontend.hooks.addAction(
          "frontend/element_ready/widget",
          function ($scope) {
            $scope.find(".civi-select2").select2();
          }
        );
      }

      $(".civi-select2.prefix-code").each(function () {
        var group = $(this).closest(".tel-group");
        var rendered = $(this).find("option:selected").val();
        group
          .find(".select2-selection__rendered")
          .removeClass(function (index, className) {
            var classNames = className.split(" ");
            return classNames
              .filter(function (name) {
                return name !== "select2-selection__rendered";
              })
              .join(" ");
          })
          .addClass(rendered);
      });

      var codeFirst = $(".prefix-code")
        .find("option:selected")
        .attr("data-dial-code");
      var valFirst = $(".tel-group").find('input[type="tel"]').val();
      if (valFirst == "") {
        $(".tel-group").find('input[type="tel"]').val(codeFirst);
      }
      $(".civi-select2.prefix-code").on("select2:select", function () {
        var group = $(this).closest(".tel-group");
        var rendered = $(this).find("option:selected").val();
        var code = $(this).find("option:selected").attr("data-dial-code");
        group
          .find(".select2-selection__rendered")
          .removeClass(function (index, className) {
            var classNames = className.split(" ");
            return classNames
              .filter(function (name) {
                return name !== "select2-selection__rendered";
              })
              .join(" ");
          })
          .addClass(rendered);
        group.find('input[type="tel"]').val(code);
      });

      // Autocomplete for civi-ajax-ui
      $('.civi-ajax-ui').autocomplete({
        minLength: 0,
        delay: 500,
        source: function (request, response) {
          var spinner = $(this.element).closest('.form-group').find('.ui-autocomplete-spinner');
          if (spinner.length === 0) {
            spinner = $('<div class="ui-autocomplete-spinner" style="display: none;"><i class="fal fa-spinner fa-spin"></i></div>');
            $(this.element).closest('.form-group').append(spinner);
          }
          spinner.show();

          $.ajax({
            url: theme_vars.ajax_url,
            dataType: "json",
            data: {
              action: 'civi_ajax_ui',
              q: request.term || '',
              taxonomy: $(this.element).attr('data-taxonomy'),
            },
            success: function (data) {
              if (Array.isArray(data)) {
                response(data);
              } else {
                response([]);
              }
            },
            error: function(xhr, status, error) {
              response([]);
            },
            complete: function () {
              spinner.hide();
            }
          });
        },
        autoFocus: false
      });

      $('.civi-ajax-ui').on("focus", function () {
        if ($(this).val().length >= 0) {
          $(this).autocomplete("search", $(this).val());
        }
      });

      // Select2 for civi-ajax-select2
      $('.civi-ajax-select2').each(function() {
        if (!$(this).hasClass('select2-hidden-accessible')) {
          $(this).select2({
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
              error: function(xhr, status, error) {
              },
              cache: true
            },
            width: '100%'
          });
        }
      });

      $('.civi-select2').each(function() {
        if (!$(this).hasClass('select2-hidden-accessible')) {
          $(this).select2({
            minimumResultsForSearch: -1,
            width: '100%'
          });
        }
      });

      $('.civi-clear-top-filter').on('click', function() {
        var form = $(this).closest('form');
        form.find('input[type="text"]').val('');
        form.find('.civi-select2, .civi-ajax-select2').val('').trigger('change');
      });
    },

    // Clean query parameters from the current archive URL after AJAX load
    clean_archive_url: function () {
      try {
        if (typeof window !== 'undefined' && window.history && typeof window.history.replaceState === 'function') {
          var base = window.location.pathname || '/';
          var hash = window.location.hash || '';
          var origin = window.location.origin || (window.location.protocol + '//' + window.location.host);
          var newUrl = origin + base + hash;
          window.history.replaceState(null, '', newUrl);
        }
      } catch (e) {}
    },

    sticky_element: function () {
      // Cache DOM elements for performance
      var $uxperSticky = $(".uxper-sticky");
      var offset = "";
      if ($uxperSticky.length > 0) {
        offset = $uxperSticky.offset().top;
      }
      var has_wpadminbar = $("#wpadminbar").length;
      var height_sticky = $uxperSticky.height();
      var wpadminbar = 0;
      var lastScroll = 0;
      if (has_wpadminbar > 0) {
        wpadminbar = $("#wpadminbar").height();
        $uxperSticky.addClass("has-wpadminbar");
      }

      var lastScrollTop = 0;
      var lastStickyState = null;
      // Use throttled scroll handler for better performance
      $(window).on('scroll', throttle(function (event) {
        var st = $(this).scrollTop();
        var shouldBeSticky = st < lastScrollTop && st >= height_sticky + wpadminbar;

        // Only update DOM if state changed
        if (shouldBeSticky !== lastStickyState) {
          if (shouldBeSticky) {
            $uxperSticky.addClass("on");
          } else {
            $uxperSticky.removeClass("on");
          }
          lastStickyState = shouldBeSticky;
        }
        lastScrollTop = st;
      }, 16)); // ~60fps

      $(".block-archive-sidebar").each(function () {
        var _this = $(this);
        if (_this.hasClass("has-sticky")) {
          _this.removeClass("has-sticky");
          _this.parents(".widget-area-init").addClass("has-sticky");
        }
      });
    },

    toggle_panel: function () {
      $(".block-panel").on("click", ".block-tab", function () {
        var parent = $(this).closest(".block-panel");
        if (parent.hasClass("active")) {
          parent.removeClass("active");
          parent.find(".block-content").slideUp(300);
        } else {
          $(".entry-property-element .block-panel").removeClass("active");
          $(".entry-property-element .block-panel .block-content").slideUp(300);
          parent.addClass("active");
          parent.find(".block-content").slideDown(300);
        }
      });
    },

    toggle_payout: function () {
      $(".civi-payout-dashboard").on(
        "click",
        ".payout-item .title",
        function (e) {
          e.preventDefault();
          $(this).toggleClass("active");
          $(this).parent().find(".content").slideToggle();
        }
      );
    },

    toggle_faq: function () {
      $(".service-faq-details").on("click", ".faq-header", function (e) {
        e.preventDefault();
        $(this).parent().find(".faq-content").slideToggle();
      });
    },

    toggle_social: function () {
      $(".toggle-social").on("click", ".btn-share", function (e) {
        e.preventDefault();
        $(this).parent().toggleClass("active");
        $(this).parent().find(".social-share").slideToggle(300);
      });

      // Facebook
      $("body").on("click", ".social-share .facebook", function (e) {
        e.preventDefault();
        var url = $(this).data("url");
        var facebookUrl = "https://www.facebook.com/sharer.php?u=" + encodeURIComponent(url);
        window.open(facebookUrl, "_blank");
      });

      // Twitter
      $("body").on("click", ".social-share .twitter", function (e) {
        e.preventDefault();
        var url = $(this).data("url");
        var twitterUrl = "https://twitter.com/share?url=" + encodeURIComponent(url);
        window.open(twitterUrl, "_blank");
      });

      // LinkedIn
      $("body").on("click", ".social-share .linkedin", function (e) {
        e.preventDefault();
        var url = $(this).data("url");
        var title = $(this).data("title") || "";
        var linkedinUrl = "https://www.linkedin.com/shareArticle?mini=true&url=" + encodeURIComponent(url);
        if (title) {
          linkedinUrl += "&title=" + encodeURIComponent(title);
        }
        window.open(linkedinUrl, "_blank");
      });

      // Tumblr
      $("body").on("click", ".social-share .tumblr", function (e) {
        e.preventDefault();
        var url = $(this).data("url");
        var name = $(this).data("name") || "";
        var description = $(this).data("description") || "";
        var tumblrUrl = "https://www.tumblr.com/share/link?url=" + encodeURIComponent(url);
        if (name) {
          tumblrUrl += "&name=" + encodeURIComponent(name);
        }
        if (description) {
          tumblrUrl += "&description=" + encodeURIComponent(description);
        }
        window.open(tumblrUrl, "_blank");
      });

      // Pinterest
      $("body").on("click", ".social-share .pinterest", function (e) {
        e.preventDefault();
        var url = $(this).data("url");
        var description = $(this).data("description") || "";
        var media = $(this).data("media") || "";
        var pinterestUrl = "https://pinterest.com/pin/create/button/?url=" + encodeURIComponent(url);
        if (description) {
          pinterestUrl += "&description=" + encodeURIComponent(description);
        }
        if (media) {
          pinterestUrl += "&media=" + encodeURIComponent(media);
        }
        window.open(pinterestUrl, "_blank", "scrollbars=yes,width=800,height=400");
      });

      // WhatsApp
      $("body").on("click", ".social-share .whatapp", function (e) {
        e.preventDefault();
        var url = $(this).data("url");
        var description = $(this).data("description") || "";
        var whatsappUrl = "https://api.whatsapp.com/send?text=" + encodeURIComponent(url);
        if (description) {
          whatsappUrl += "&description=" + encodeURIComponent(description);
        }
        window.open(whatsappUrl, "_blank", "scrollbars=yes,width=800,height=400");
      });
    },

    toggle_content: function () {
      var h_desc = $(
        ".single-jobs .jobs-content .inner-content .entry-visibility"
      ).height();
      if (h_desc > 130) {
        $(".single-jobs .jobs-content").addClass("on");
      }

      $(".show-more").on("click", function (e) {
        e.preventDefault();
        $(this).parents(".jobs-area").addClass("active");
      });

      $(".hide-all").on("click", function (e) {
        e.preventDefault();
        $(this).parents(".jobs-area").removeClass("active");
      });

      $(".open-toggle").on("click", function (e) {
        e.preventDefault();
        $(this).parent().toggleClass("active");
      });

      $(document).on("click", function (event) {
        var $this = $(".form-toggle");
        if ($this !== event.target && !$this.has(event.target).length) {
          $this.removeClass("active");
        }
      });

      $("body").on("click", ".area-booking .minus", function (e) {
        var input = $(this)
          .parents(".product-quantity")
          .find(".input-text.qty");
        var name = $(this)
          .parents(".product-quantity")
          .find(".input-text.qty")
          .attr("name");
        var val = parseInt(input.val()) - 1;
        if (input.val() > 0) input.attr("value", val);
        $(this)
          .parents(".area-booking")
          .find(".open-toggle")
          .addClass("active");
        if (val > 0) {
          $(this)
            .parents(".area-booking")
            .find("." + name + " span")
            .text(parseInt(val));
        } else {
          $(this)
            .parents(".area-booking")
            .find("." + name + " span")
            .text(0);
        }
      });
    },

    nav_scroll: function () {
      $('.nav-scroll a[href^="#"]').on("click", function (event) {
        event.preventDefault();
        var target = $(this.getAttribute("href"));
        var has_wpadminbar = 0;
        if ($("#wpadminbar").height()) {
          has_wpadminbar = $("#wpadminbar").height();
        }
        if (target.length) {
          var top = target.offset().top - 15 - has_wpadminbar;
          $("html, body").stop().animate(
            {
              scrollTop: top,
            },
            500
          );
        }

        $(".nav-scroll li").removeClass("active");
        $(this).parent().addClass("active");
      });

      // Cache DOM elements for performance
      var $groupFields = $(".group-field");
      var $navScrollLinks = $(".nav-scroll a");
      var lastActiveId = null;

      // Use throttled scroll handler
      $(window).on('scroll', throttle(function () {
        var scrollDistance = $(window).scrollTop();
        var newActiveId = null;

        // Find the current active section
        $groupFields.each(function (i) {
          if ($(this).offset().top <= scrollDistance + 50) {
            newActiveId = "#" + $(this).attr("id");
          }
        });

        // Only update DOM if active section changed
        if (newActiveId !== lastActiveId && newActiveId) {
          $navScrollLinks.parent().removeClass("active");
          $navScrollLinks.filter('[href="' + newActiveId + '"]').parent().addClass("active");
          lastActiveId = newActiveId;
        }
      }, 100));
    },

    filter_toggle: function () {
      $(".btn-canvas-filter").on("click", function (event) {
        event.preventDefault();
        $("body").css("overflow", "hidden");
        $("body").addClass("open-popup");
        $(this).toggleClass("active");
        $(".archive-filter").toggleClass("open-canvas");
      });

      $(".archive-filter").on(
        "click",
        ".btn-close,.bg-overlay,.show-result .civi-button",
        function (e) {
          e.preventDefault();
          $("body").css("overflow", "inherit");
          $("body").removeClass("open-popup");
          $(this).parents(".archive-filter").removeClass("open-canvas");
          $(".btn-canvas-filter").removeClass("active");
        }
      );
    },

    slick_carousel: function () {
      var rtl = false;
      if ($("body").hasClass("rtl")) {
        rtl = true;
      }
      $(".civi-slick-carousel").each(function () {
        var slider = $(this);
        var defaults = {
          slidesToShow: 1,
          slidesToScroll: 1,
          arrows: true,
          prevArrow:
            '<div class="gl-prev slick-arrow"><i class="far fa-angle-left large"></i></div>',
          nextArrow:
            '<div class="gl-next slick-arrow"><i class="far fa-angle-right large"></i></div>',
          dots: false,
          fade: false,
          infinite: false,
          centerMode: false,
          adaptiveHeight: true,
          pauseOnFocus: true,
          pauseOnHover: true,
          swipe: true,
          draggable: true,
          rtl: rtl,
          autoplay: false,
          autoplaySpeed: 250,
          speed: 250,
        };

        if (slider.hasClass("slick-nav")) {
          defaults["prevArrow"] =
            '<div class="gl-prev"><i class="far fa-angle-left large"></i></div>';
          defaults["nextArrow"] =
            '<div class="gl-next"><i class="far fa-angle-right large"></i></div>';
        }

        var config = $.extend({}, defaults, slider.data("slick"));
        // Initialize Slider
        slider.slick(config);
      });
    },

    back_top_top: function () {
      // Cache element for performance
      var $backToTop = $("#back-to-top");
      var lastState = null;

      // Use throttled scroll handler
      $(window).on('scroll', throttle(function () {
        var isActive = $(window).scrollTop() > 500;
        // Only update DOM if state changed
        if (isActive !== lastState) {
          if (isActive) {
            $backToTop.addClass("is-active");
          } else {
            $backToTop.removeClass("is-active");
          }
          lastState = isActive;
        }
      }, 100)); // 100ms throttle is enough for this

      $backToTop.on("click", function (e) {
        e.preventDefault();
        $("html, body").animate(
          {
            scrollTop: 0,
          },
          500
        );
      });
    },
  };

  GLF.onReady = {
    init: function () {
      GLF.element.init();
    },
  };

  GLF.onLoad = {
    init: function () { },
  };

  GLF.onResize = {
    init: function () {
      // Resize Window
    },
  };

  $(document).ready(GLF.onReady.init);
  $(window).on('resize', GLF.onResize.init);
  $(window).on('load', GLF.onLoad.init);
})(jQuery);
