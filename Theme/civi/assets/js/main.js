var CIVI = CIVI || {};

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

  // Remove params login=success from URL and reload page
  const params = new URLSearchParams(window.location.search);
  if (params.get("login") === "success") {
    params.delete("login");
    const newUrl =
      window.location.pathname +
      (params.toString() ? "?" + params.toString() : "");
    window.history.replaceState({}, "", newUrl);
    location.reload();
  }

  var $body = $("body");
  var ajax_url = theme_vars.ajax_url;

  window.verifyLoginCaptcha = function(token) {
    if (!token) {
      return;
    }
    const form = document.querySelector('form.ux-login');
    if (form) {
      const hiddenInput = form.querySelector('.g-recaptcha-response');
      if (hiddenInput) {
        hiddenInput.value = token;
      } else {
        const newInput = document.createElement('input');
        newInput.type = 'hidden';
        newInput.name = 'g_recaptcha_response';
        newInput.className = 'g-recaptcha-response';
        newInput.value = token;
        form.appendChild(newInput);
      }
    }
  };

  window.verifyRegisterCaptcha = function(token) {
    if (!token) {
      return;
    }
    const form = document.querySelector('form.ux-register');
    if (form) {
      const hiddenInput = form.querySelector('.g-recaptcha-response');
      if (hiddenInput) {
        hiddenInput.value = token;
      } else {
        const newInput = document.createElement('input');
        newInput.type = 'hidden';
        newInput.name = 'g_recaptcha_response';
        newInput.className = 'g-recaptcha-response';
        newInput.value = token;
        form.appendChild(newInput);
      }
    }
  };

  CIVI.element = {
    init: function () {
      CIVI.element.rtl();
      CIVI.element.general();
      CIVI.element.retina_logo();
      CIVI.element.auto_close_loading_effect();
      CIVI.element.widget_categories();
      CIVI.element.swiper_carousel();
      CIVI.element.WidgetCiviCarouselHandler();
      CIVI.element.slick_carousel();
      CIVI.element.main_menu();
      CIVI.element.dropdown_select();
      CIVI.element.elementor_header();
      CIVI.element.sticky_header();
      CIVI.element.popup();
      CIVI.element.toggle_popup();
      CIVI.element.nav_tabs();
      CIVI.element.validate_form();
      CIVI.element.forget_password();
      CIVI.element.cookie_notices();
      CIVI.element.user_form_settings();
      CIVI.element.phone_input_handler();
      CIVI.element.check_company_tooltip();
    },

    windowLoad: function () {
      this.page_loading_effect();
      this.handler_animation();
      this.handler_entrance_queue_animation();
    },

    rtl: function () {
      if ($("body").attr("dir") == "rtl") {
        $(".elementor-section-stretched").each(function () {
          var val = $(this).css("left");
          $(this).css("left", "auto");
          $(this).css("right", val);
        });
      }
    },

    general: function () {
      $(".mobile-menu .account .user-show").on("click", function (e) {
        e.preventDefault();
        $(this).parent().toggleClass("active");
      });

      $(".block-search.search-icon").on("click", function (e) {
        e.preventDefault();
        $(".search-form-wrapper.canvas-search").addClass("on");
        $("body").addClass("canvas-search-open");
      });

      $(".canvas-search").on("click", ".btn-close,.bg-overlay", function (e) {
        e.preventDefault();
        $(this).parents(".canvas-search").removeClass("on");
        $("body").removeClass("canvas-search-open");
      });

      $(".block-search.search-input").on(
        "keyup",
        ".input-search",
        function (e) {
          e.preventDefault();
          if ($(this).val().length > 0) {
            $(this).closest(".search-input").addClass("has-clear");
          } else {
            $(this).closest(".search-input").removeClass("has-clear");
          }
        }
      );

      $(".block-search.search-input").on("click", ".icon-clear", function (e) {
        e.preventDefault();
        $(this).closest(".search-input").find(".input-search").val("");
        $(this).closest(".search-input").removeClass("has-clear");
      });

      $("body").on("click", ".civi-categories li", function (event) {
        event.returnValue = true;
      });

      $("body").on("click", ".civi-categories li > a", function (event) {
        event.returnValue = true;
      });
    },

    retina_logo: function () {
      if (
        window.matchMedia("only screen and (min--moz-device-pixel-ratio: 1.5)")
          .matches ||
        window.matchMedia("only screen and (-o-min-device-pixel-ratio: 3/2)")
          .matches ||
        window.matchMedia(
          "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
        ).matches ||
        window.matchMedia("only screen and (min-device-pixel-ratio: 1.5)")
          .matches
      ) {
        $(".site-logo img").each(function () {
          $(this).addClass("logo-retina");
          $(this).attr("src", $(this).data("retina"));
        });
      }
    },

    page_loading_effect: function () {
      $(".page-loading-effect").addClass("visibility");
      $(".civi-jobs-item").removeClass("skeleton-loading");

      setTimeout(function () {
        $(".page-loading-effect").remove();
      }, 2000);
    },

    auto_close_loading_effect: function () {
      setTimeout(function () {
        $(".page-loading-effect").remove();
      }, 2000);
    },

    handler_animation: function () {
      var items = $(".modern-grid").children(".grid-item");

      items.waypoint(
        function () {
          // Fix for different ver of waypoints plugin.
          var _self = this.element ? this.element : this;
          var $self = $(_self);
          $self.addClass("animate");
        },
        {
          offset: "100%",
          triggerOnce: true,
        }
      );
    },

    handler_entrance_queue_animation: function () {
      var animateQueueDelay = 200,
        queueResetDelay;
      $(".civi-entrance-animation-queue").each(function () {
        var itemQueue = [],
          queueTimer,
          queueDelay = $(this).data("animation-delay")
            ? $(this).data("animation-delay")
            : animateQueueDelay;

        $(this)
          .children(".item")
          .waypoint(
            function () {
              // Fix for different ver of waypoints plugin.
              var _self = this.element ? this.element : $(this);

              queueResetDelay = setTimeout(function () {
                queueDelay = animateQueueDelay;
              }, animateQueueDelay);

              itemQueue.push(_self);
              CIVI.element.process_item_queue(
                itemQueue,
                queueDelay,
                queueTimer
              );
              queueDelay += animateQueueDelay;
            },
            {
              offset: "100%",
              triggerOnce: true,
            }
          );
      });
    },

    process_item_queue: function (
      itemQueue,
      queueDelay,
      queueTimer,
      queueResetDelay
    ) {
      clearTimeout(queueResetDelay);
      queueTimer = window.setInterval(function () {
        if (itemQueue !== undefined && itemQueue.length) {
          $(itemQueue.shift()).addClass("animate");
          CIVI.element.process_item_queue();
        } else {
          window.clearInterval(queueTimer);
        }
      }, queueDelay);
    },

    widget_categories: function () {
      $(".widget_categories>ul>li").each(function () {
        if ($(this).find(".children").length > 0) {
          $(this).append('<i class="far fa-plus"></i>');
          $(this).on("click", function () {
            $(this).toggleClass("active");
          });
          $(".widget_categories>ul>li a").on("click", function (e) {
            e.stopPropagation();
          });
        }
      });
    },

    swiper_carousel: function () {
      $(".civi-slider").each(function () {
        if ($(this).hasClass("civi-swiper-linked-yes")) {
          var mainSlider = $(this).children(".civi-main-swiper").CiviSwiper();
          var thumbsSlider = $(this)
            .children(".civi-thumbs-swiper")
            .CiviSwiper();

          mainSlider.controller.control = thumbsSlider;
          thumbsSlider.controller.control = mainSlider;
        } else {
          $(this).CiviSwiper();
        }
      });
    },

    WidgetCiviCarouselHandler: function () {
      $(".civi-carousel-activation").each(function () {
        var carousel_elem = $(this);

        if (carousel_elem.length > 0) {
          var settings = carousel_elem.data("settings");
          var arrows = settings["arrows"];
          var arrow_prev_txt = settings["arrow_prev_txt"];
          var arrow_next_txt = settings["arrow_next_txt"];
          var dots = settings["dots"];
          var autoplay = settings["autoplay"];
          var autoplay_speed = parseInt(settings["autoplay_speed"]) || 3000;
          var animation_speed = parseInt(settings["animation_speed"]) || 300;
          var pause_on_hover = settings["pause_on_hover"];
          var center_mode = settings["center_mode"];
          var center_padding = settings["center_padding"]
            ? settings["center_padding"]
            : "50px";
          var display_columns = parseInt(settings["display_columns"]) || 1;
          var scroll_columns = parseInt(settings["scroll_columns"]) || 1;
          var tablet_width = parseInt(settings["tablet_width"]) || 800;
          var tablet_display_columns =
            parseInt(settings["tablet_display_columns"]) || 1;
          var tablet_scroll_columns =
            parseInt(settings["tablet_scroll_columns"]) || 1;
          var mobile_width = parseInt(settings["mobile_width"]) || 480;
          var mobile_display_columns =
            parseInt(settings["mobile_display_columns"]) || 1;
          var mobile_scroll_columns =
            parseInt(settings["mobile_scroll_columns"]) || 1;
          var carousel_style_ck = parseInt(settings["carousel_style_ck"]) || 1;

          if (carousel_style_ck == 4) {
            carousel_elem.slick({
              arrows: arrows,
              prevArrow:
                '<button class="civi-carosul-prev">' +
                arrow_prev_txt +
                "</button>",
              nextArrow:
                '<button class="civi-carosul-next">' +
                arrow_next_txt +
                "</button>",
              dots: dots,
              customPaging: function (slick, index) {
                var data_title = slick.$slides
                  .eq(index)
                  .find(".civi-data-title")
                  .data("title");
                return "<h6>" + data_title + "</h6>";
              },
              infinite: true,
              autoplay: autoplay,
              autoplaySpeed: autoplay_speed,
              speed: animation_speed,
              fade: false,
              pauseOnHover: pause_on_hover,
              slidesToShow: display_columns,
              slidesToScroll: scroll_columns,
              centerMode: center_mode,
              centerPadding: center_padding,
              responsive: [
                {
                  breakpoint: tablet_width,
                  settings: {
                    slidesToShow: tablet_display_columns,
                    slidesToScroll: tablet_scroll_columns,
                  },
                },
                {
                  breakpoint: mobile_width,
                  settings: {
                    slidesToShow: mobile_display_columns,
                    slidesToScroll: mobile_scroll_columns,
                  },
                },
              ],
            });
          } else {
            carousel_elem.slick({
              arrows: arrows,
              prevArrow:
                '<button class="civi-carosul-prev">' +
                arrow_prev_txt +
                "</button>",
              nextArrow:
                '<button class="civi-carosul-next">' +
                arrow_next_txt +
                "</button>",
              dots: dots,
              infinite: true,
              autoplay: autoplay,
              autoplaySpeed: autoplay_speed,
              speed: animation_speed,
              fade: false,
              pauseOnHover: pause_on_hover,
              slidesToShow: display_columns,
              slidesToScroll: scroll_columns,
              centerMode: center_mode,
              centerPadding: center_padding,
              responsive: [
                {
                  breakpoint: tablet_width,
                  settings: {
                    slidesToShow: tablet_display_columns,
                    slidesToScroll: tablet_scroll_columns,
                  },
                },
                {
                  breakpoint: mobile_width,
                  settings: {
                    slidesToShow: mobile_display_columns,
                    slidesToScroll: mobile_scroll_columns,
                  },
                },
              ],
            });
          }
        }
      });
    },

    slick_carousel: function () {
      var rtl = false;
      if ($("body").hasClass("rtl")) {
        rtl = true;
      }
      $(".slick-carousel").each(function () {
        var slider = $(this);
        var defaults = {
          slidesToShow: 1,
          slidesToScroll: 1,
          arrows: true,
          prevArrow:
            '<div class="gl-prev slick-arrow"><i class="far fa-chevron-left large"></i></div>',
          nextArrow:
            '<div class="gl-next slick-arrow"><i class="far fa-chevron-right large"></i></div>',
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
            '<div class="gl-prev"><i class="far fa-chevron-left large"></i></div>';
          defaults["nextArrow"] =
            '<div class="gl-next"><i class="far fa-chevron-right large"></i></div>';
        }

        var config = $.extend({}, defaults, slider.data("slick"));
        // Initialize Slider
        slider.slick(config);
      });
    },

    main_menu: function () {
      $(
        ".default-menu .menu-item-has-children>a,.site-menu .page_item_has_children>a"
      ).append(
        '<span class="chevron"><i class="far fa-chevron-down"></i></span>'
      );

      $(
        ".canvas-menu .menu-item-has-children>a,.canvas-menu .page_item_has_children>a"
      ).on("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        var parent = $(this).parent();
        if (parent.hasClass("active")) {
          parent.removeClass("active");
          parent.find(">.sub-menu,>.children").slideUp(300);
        } else {
          if (
            $(this)
              .parents(".menu-item-has-children,.page_item_has_children")
              .hasClass("active") == false
          ) {
            $(".canvas-menu li>.sub-menu,.canvas-menu li>.children").slideUp(
              300
            );
            $(".canvas-menu li").removeClass("active");
          }
          parent.find(">.sub-menu,>.children").slideDown(300);
          parent.addClass("active");
        }
      });

      // Open Canvas Menu
      $(".canvas-menu").on("click", ".icon-menu", function (e) {
        e.preventDefault();
        $(this).parents(".canvas-menu").toggleClass("active");
      });

      // Close Canvas Menu
      $(".canvas-menu").on("click", ".btn-close,.bg-overlay", function (e) {
        e.preventDefault();
        $(this).parents(".canvas-menu").removeClass("active");
        $("body").css("overflow", "auto");
      });

      // Check Sub Menu
      $(".site-menu .sub-menu").each(function () {
        var width = $(this).outerWidth();

        if (width > 0) {
          var offset = $(this).offset();
          var w_body = $("body").outerWidth();
          var left = offset.left;
          if (w_body < left + width) {
            $(this).css("left", "-100%");
          }
        }
      });
    },

    dropdown_select: function () {
      $(".dropdown-select").on("click", ".entry-show", function () {
        $(this).parent().toggleClass("active");
      });
      CIVI.element.click_outside(".dropdown-select");
    },

    click_outside: function (element) {
      $(document).on("click", function (event) {
        var $this = $(element);
        if ($this !== event.target && !$this.has(event.target).length) {
          $this.removeClass("active");
        }
      });
    },

    elementor_header: function () {
      if (theme_vars.sticky_header == 1) {
        $(
          ".elementor-location-header .elementor-section-wrap>.elementor-element"
        ).addClass("uxper-sticky");
      }

      if (theme_vars.float_header == 1) {
        $(
          ".elementor-location-header .elementor-section-wrap>.elementor-element"
        ).addClass("uxper-float");
      }
    },

    sticky_header: function () {
      var offset =
        $("header.site-header").length > 0
          ? $("header.site-header").offset().top
          : 0;
      var wpadminbar = $("#wpadminbar").length ? $("#wpadminbar").height() : 0;
      // Cache the sticky header element for performance
      var $stickyHeader = $(".sticky-header");

      if (wpadminbar) {
        $stickyHeader.addClass("has-wpadminbar");
      }

      // Use throttled scroll handler for better performance
      var lastIsSticky = null;
      $(window)
        .on("scroll", throttle(function () {
          var isSticky = $(window).scrollTop() > offset - wpadminbar;
          // Only update DOM if state changed
          if (isSticky !== lastIsSticky) {
            $stickyHeader.toggleClass("on", isSticky);
            lastIsSticky = isSticky;
          }
        }, 16)) // ~60fps
        .trigger("scroll");
    },

    popup: function () {
      $(".civi-on-popup").on("click", function (event) {
        event.preventDefault();
        var id = $(this).attr("href");
        $(id).addClass("active");
        $("body").addClass("open-popup");
      });

      $(".civi-popup").on("click", ".btn-close,.bg-overlay", function () {
        $(this).parents(".civi-popup").removeClass("active");
        $("body").removeClass("open-popup");
        $("body").css("overflow", "auto");
      });
    },

    toggle_popup: function () {
      $(".popup").on("click", ".bg-overlay, .btn-close", function (e) {
        e.preventDefault();
        $("body").css("overflow", "auto");
        $("body").removeClass("open-popup");
        $(this).parents(".popup").removeClass("open");
        $(".site-header").removeClass("show-popup");
        var $popup = $(this).closest('.popup-account');
        if ($popup.length) {
          $popup.find('.form-account').removeClass('active');
          $popup.find('.ux-pending-approval').hide().removeClass('active');
          $popup.find('.tabs-form a').removeClass('active');
          $popup.find('.tabs-form .btn-login').addClass('active');
          $popup.find('.ux-login').addClass('active');
          $popup.find('p.msg').removeClass('text-error text-success text-warning').text('');
        }
      });

      $(".btn-open-popup").on("click", function (e) {
        e.preventDefault();
        $("body").css("overflow", "hidden");
        $("body").addClass("open-popup");
        $(".popup").removeClass("open");
        $(this).parent().find(".popup").addClass("open");
        $(".site-header").addClass("show-popup");
      });

      $("#secondary .jobs-booking .btn-open-popup").on("click", function (e) {
        e.preventDefault();
        $("body").css("overflow", "auto");
      });

      $(".btn-open-claim").on("click", function (e) {
        e.preventDefault();
        $("body").css("overflow", "hidden");
        $("body").addClass("open-popup");
        $(".popup").removeClass("open");
        $(this).parents(".claim-badge").find(".popup").addClass("open");
        $(".site-header").addClass("show-popup");
      });

      $("body").on("click", ".logged-out a", function (e) {
        e.preventDefault();
        $("body").css("overflow", "hidden");
        var tab = $(this).attr("class");
        $(".tabs-form a").removeClass("active");
        if (tab.indexOf("btn-login") != -1) {
          $(".tabs-form a.btn-login").addClass("active");
        }
        if (tab.indexOf("btn-register") != -1) {
          $(".tabs-form a.btn-register").addClass("active");
        }
        $(".body-popup .form-account").removeClass("active");
        $(".canvas-menu").removeClass("active");
        var form_id = $(".tabs-form a.active").attr("data-form");
        $(".body-popup ." + form_id).addClass("active");
        $(".popup").removeClass("open");
        var id = $(this).attr("href");

        $(id).addClass("open");
      });
    },

    nav_tabs: function () {
      $(".tabs-form a").on("click", function (e) {
        e.preventDefault();
        $(".tabs-form a").removeClass("active");
        $(this).addClass("active");
        $(".body-popup .form-account").each(function () {
          if (!$(this).hasClass("alway-show")) {
            $(this).removeClass("active");
          }
        });
        var $popup = $(this).closest('.popup-account');
        if ($popup.length) {
          $popup.find('.ux-pending-approval').hide().removeClass('active');
        }

        var id = $(this).attr("data-form");
        if (id == "ux-register") {
          $(this).closest(".inner-popup").find(".footer-popup").fadeOut(0);
        } else {
          $(this).closest(".inner-popup").find(".footer-popup").fadeIn(0);
        }
        $(".body-popup ." + id).addClass("active");
      });

      $(".tab-group > ul li a").on("click", function (e) {
        e.preventDefault();
        $(".tab-group > ul li").removeClass("active");
        $(this).parent().addClass("active");
        $(".tab-group .tab").removeClass("active");
        var id = $(this).attr("href");
        $(id).addClass("active");
      });

      $(".btn-reset-password").on("click", function (e) {
        e.preventDefault();
        $(".ux-login").removeClass("active");
        $(".civi-reset-password-wrap").addClass("active");
      });

      $(".back-to-login").on("click", function (e) {
        e.preventDefault();
        $(".civi-reset-password-wrap").removeClass("active");
        $(".ux-login").addClass("active");
      });
    },

    validate_form: function () {
      function detectRecaptchaVersion(formElement) {
        const v3Script = document.querySelector('script[src*="recaptcha/api.js?render="]');
        if (v3Script) {
          return 'v3';
        }

        if (formElement && formElement.length) {
          const v2Widget = formElement.find('.g-recaptcha').length > 0 ||
                          formElement.closest('.form-account, .el-user-form').find('.g-recaptcha').length > 0;
          if (v2Widget) {
            return 'v2';
          }
        }

        const v2WidgetGlobal = document.querySelector('.g-recaptcha');
        if (v2WidgetGlobal) {
          return 'v2';
        }

        return null;
      }

      function getRecaptchaResponse(formElement, formIndex) {
        if (typeof grecaptcha === "undefined") {
          return "";
        }

        // Skip reCAPTCHA for local development
        const currentDomain = window.location.hostname;
        const isLocal = currentDomain.includes('localhost') ||
          currentDomain.includes('.local') ||
          currentDomain === '127.0.0.1' ||
          currentDomain.startsWith('192.168.');

        if (isLocal) {
          return "local_dev_token";
        }

        // First, check if token was already set in hidden input by callback (v2) or execute (v3)
        let hiddenInput = null;
        if (formElement && formElement.length) {
          hiddenInput = formElement.find('.g-recaptcha-response').first()[0];
        }

        if (!hiddenInput) {
          hiddenInput = document.querySelector('.g-recaptcha-response');
        }

        if (hiddenInput && hiddenInput.value) {
          const token = hiddenInput.value;
          return token;
        }

        // Fallback to v2 (checkbox) - try to get response from widget
        if (typeof grecaptcha.getResponse === "function") {
          // For v2, try to find widget ID by checking all widgets on the page
          // This is more reliable than using formIndex
          for (let i = 0; i < 10; i++) {
            try {
              const response = grecaptcha.getResponse(i);
              if (response && response.length > 0) {
                return response;
              }
            } catch (e) {
              // Continue searching - widget ID might not exist
            }
          }

          // If no response found, try with formIndex as fallback
          try {
            const response = grecaptcha.getResponse(formIndex);
            if (response && response.length > 0) {
              return response;
            }
          } catch (e) {
          }
        }

        return "";
      }

      function executeRecaptchaV3(action, formElement) {
        if (typeof grecaptcha === "undefined") {
          return Promise.resolve("");
        }

        const currentDomain = window.location.hostname;
        const isLocal = currentDomain.includes('localhost') ||
          currentDomain.includes('.local') ||
          currentDomain === '127.0.0.1' ||
          currentDomain.startsWith('192.168.');

        if (isLocal) {
          return Promise.resolve("local_dev_token");
        }

        const recaptchaScript = document.querySelector('script[src*="recaptcha/api.js?render="]');
        if (!recaptchaScript) {
          return Promise.resolve("");
        }

        const siteKey = recaptchaScript.src.match(/render=([^&]+)/)?.[1];
        if (!siteKey) {
          return Promise.resolve("");
        }

        return new Promise(function(resolve, reject) {
          grecaptcha.ready(function() {
            if (typeof grecaptcha.execute !== "function") {
              reject("");
              return;
            }

            grecaptcha.execute(siteKey, { action: action })
              .then(function(token) {
                if (!token) {
                  reject("");
                  return;
                }

                // Find the correct hidden input for this form
                let hiddenInput = null;
                if (formElement && formElement.length) {
                  // First try to find input within the form
                  hiddenInput = formElement.find('.g-recaptcha-response').first()[0];
                  // If not found, try to find in parent container
                  if (!hiddenInput) {
                    const container = formElement.closest('.popup-account, .el-user-form');
                    if (container && container.length) {
                      hiddenInput = container.find('.g-recaptcha-response').first()[0];
                    }
                  }
                }

                // Fallback to first input on page if form-specific not found
                if (!hiddenInput) {
                  hiddenInput = document.querySelector('.g-recaptcha-response');
                }

                if (hiddenInput) {
                  hiddenInput.value = token;
                }
                resolve(token);
              })
              .catch(function(error) {
                console.error('reCAPTCHA v3 execution error:', error);
                reject("");
              });
          });
        });
      }

      function showMessageAndLoading(form, message, className, showLoading) {
        const $msg = form.find("p.msg").removeClass("text-error text-success text-warning");
        if (message) {
          $msg.text(message).addClass(className).show();
        } else {
          $msg.hide();
        }
        form.closest(".popup-account, .el-user-form").find(".loading-effect").toggle(showLoading);
      }

      function handleAjaxSuccess(form, nextForm, data) {
        var effectiveMessage = data.messages_text || theme_vars[data.messages] || data.messages;
        form.find("p.msg").text(effectiveMessage).addClass(data.class);
        if (data.success === true && data.verify === true) {
          form.removeClass("active");
          if (nextForm) {
            nextForm.addClass("active");
          }
        } else if (data.success === true && data.url_redirect) {
          window.location.href = data.url_redirect;
        } else if (data.success === true && (data.url_redirect === '' || data.url_redirect === null || typeof data.url_redirect === 'undefined')) {
          var isPendingApproval = false;
          var pendingPatterns = [
            'awaiting_admin_approval',
            'waiting_approval',
            'Registration successful. Awaiting admin approval.',
            'Awaiting admin approval',
            'pending approval',
            'admin approval'
          ];

          if (data.messages) {
            for (var i = 0; i < pendingPatterns.length; i++) {
              if (data.messages.includes(pendingPatterns[i])) {
                isPendingApproval = true;
                break;
              }
            }
          }

          if (!isPendingApproval && effectiveMessage) {
            for (var i = 0; i < pendingPatterns.length; i++) {
              if (effectiveMessage.includes(pendingPatterns[i])) {
                isPendingApproval = true;
                break;
              }
            }
          }

          if (isPendingApproval) {
            var $container = form.closest('.popup-account, .el-user-form');
            var $pendingForm = $container.find('.ux-pending-approval');

            $container.find('.form-account').removeClass('active');
            $pendingForm.show().addClass('active');
            form.find('button[type="submit"], .gl-button.btn.button').prop('disabled', true).addClass('is-disabled');
          } else {
            var isVerificationForm = form.hasClass('ux-verify');
            var $container = form.closest('.popup-account, .el-user-form');
            var hasStatusUserEnabled = $container.data('enable-status-user') === '1' ||
              $container.data('enable-status-user') === 1 ||
              $('body').data('enable-status-user') === '1' ||
              $('meta[name="enable-status-user"]').attr('content') === '1';
            if (isVerificationForm && hasStatusUserEnabled && data.success === true) {
              var $pendingForm = $container.find('.ux-pending-approval');

              if ($pendingForm.length > 0) {
                $container.find('.form-account').removeClass('active');
                $pendingForm.show().addClass('active');
                form.find('button[type="submit"], .gl-button.btn.button').prop('disabled', true).addClass('is-disabled');
              }
            }
          }
        }
        form.closest(".popup-account, .el-user-form").find(".loading-effect").fadeOut();
      }

      $.validator.addMethod("phoneNumber", function (value, element) {
        const prefixCode = $(element).closest('.tel-group').find('select[name="prefix_code"] option:selected').data('dial-code') || '';
        if (!prefixCode) return false;
        const phoneNumber = value.replace(/\D/g, '');
        const minLength = 7;
        const maxLength = 12;
        const isValidLength = phoneNumber.length >= minLength && phoneNumber.length <= maxLength + prefixCode.length;
        return this.optional(element) || (value.startsWith(prefixCode) && isValidLength);
      }, theme_vars.invalid_phone || "Please enter a valid phone number.");

      $.validator.addMethod("noSpecialChars", function (value, element) {
        const pattern = /^[a-zA-Z0-9]*$/;
        const sanitizedValue = value.replace(/\s/g, "").replace(/[^\w\s]/gi, "");
        return this.optional(element) || pattern.test(sanitizedValue);
      }, theme_vars.no_special_chars || "Please enter a valid value without special characters or spaces.");

      // Validation messages
      $.extend($.validator.messages, {
        required: theme_vars.required || "This field is required.",
        email: theme_vars.email || "Please enter a valid email address.",
        minlength: $.validator.format(theme_vars.minlength || "Please enter at least {0} characters."),
        maxlength: $.validator.format(theme_vars.maxlength || "Please enter no more than {0} characters."),
        phoneNumber: theme_vars.invalid_phone || "Please enter a valid phone number (7-12 digits after country code).",
        noSpecialChars: theme_vars.no_special_chars || "Please enter a valid value without special characters or spaces."
      });

      $(".ux-login").each(function (index) {
        const _this = $(this);
        _this.validate({
          rules: {
            email: { required: true },
            password: { required: true, minlength: 5, maxlength: 30 }
          },
          errorPlacement: function (error, element) {
            error.appendTo(element.closest('.form-group'));
          },
          submitHandler: function (form) {
            var civi_recaptcha = _this.find('input[name="civi_recaptcha"]').val();

            if (civi_recaptcha !== '0') {
              const recaptchaVersion = detectRecaptchaVersion(_this);

              if (recaptchaVersion === 'v3') {
                executeRecaptchaV3('login', _this).then(function (token) {
                  if (token) {
                    submitLoginForm(token);
                  } else {
                    showMessageAndLoading(_this, theme_vars.recaptcha_verification_failed || "reCAPTCHA verification failed. Please try again.", "text-error", false);
                  }
                }).catch(function(error) {
                  showMessageAndLoading(_this, theme_vars.recaptcha_verification_failed || "reCAPTCHA verification failed. Please try again.", "text-error", false);
                });
                return false;
              } else if (recaptchaVersion === 'v2') {
                if (typeof grecaptcha !== "undefined" && typeof grecaptcha.getResponse === "function") {
                  const formCount = $("form.ux-login, form.ux-register").length;
                  var g_recaptcha_response = getRecaptchaResponse(_this, formCount - 1);
                  if (!g_recaptcha_response) {
                    showMessageAndLoading(_this, theme_vars.recaptcha_complete_verification || "Please complete the reCAPTCHA verification.", "text-error", false);
                    return false;
                  }
                  submitLoginForm(g_recaptcha_response);
                  return false;
                } else {
                  showMessageAndLoading(_this, theme_vars.recaptcha_not_configured || "reCAPTCHA is not properly configured.", "text-error", false);
                  return false;
                }
              } else {
                showMessageAndLoading(_this, theme_vars.recaptcha_not_configured || "reCAPTCHA is not properly configured.", "text-error", false);
                return false;
              }
            } else {
              submitLoginForm("");
            }

            function submitLoginForm(recaptchaToken) {
              const data = {
                email: _this.find('input[name="email"]').val(),
                password: _this.find('input[name="password"]').val(),
                reload: _this.find('input[name="current_page"]').val(),
                captcha: _this.find(".civi-captcha").val(),
                num_captcha: _this.find(".civi-num-captcha").data("captcha"),
                security: theme_vars.login_nonce,
                g_recaptcha_response: recaptchaToken,
                action: "get_login_user"
              };

              $.ajax({
                url: ajax_url,
                type: "POST",
                cache: false,
                dataType: "json",
                data: data,
                beforeSend: function () {
                  showMessageAndLoading(_this, theme_vars.send_user_info, "", true);
                },
                success: function (data) {
                  // Log debug info if available
                  if (data.debug && console && console.log) {
                    console.log('reCAPTCHA Debug Info:', data.debug);
                  }
                  handleAjaxSuccess(_this, null, data);
                },
                error: function (xhr, status, error) {
                  console.error('Login AJAX Error:', {xhr: xhr, status: status, error: error});
                  showMessageAndLoading(_this, theme_vars.ajax_error || "An error occurred. Please try again.", "text-error", false);
                }
              });
            }
          }
        });
      });

      $(".ux-register").each(function (index) {
        const _this = $(this);
        const _next = _this.nextAll('.ux-verify').first();

        _this.validate({
          rules: {
            reg_firstname: { required: true },
            reg_lastname: { required: true },
            reg_company_name: { required: true, noSpecialChars: true },
            reg_email: { required: true, email: true },
            reg_phone: { required: true, phoneNumber: true },
            reg_password: { required: true, minlength: 5, maxlength: 32 },
            accept_account: { required: true }
          },
          messages: {
            reg_firstname: { required: theme_vars.required },
            reg_lastname: { required: theme_vars.required },
            reg_company_name: {
              required: theme_vars.required,
              noSpecialChars: theme_vars.reg_company_name_noSpecialChars || "Username cannot contain special characters or spaces."
            },
            reg_email: {
              required: theme_vars.required,
              email: theme_vars.reg_email_email || "Please enter a valid email address."
            },
            reg_phone: {
              required: theme_vars.required,
              phoneNumber: theme_vars.reg_phone_phoneNumber || "Please enter a valid phone number."
            },
            reg_password: {
              required: theme_vars.required,
              minlength: theme_vars.reg_password_minlength || "Password must be at least 5 characters long.",
              maxlength: theme_vars.reg_password_maxlength || "Password cannot exceed 32 characters."
            },
            accept_account: { required: theme_vars.accept_account_required || "You must accept the terms and privacy policy." }
          },
          errorPlacement: function (error, element) {
            const placementMap = {
              'accept_account': () => element.closest('.accept-account').find('label[for="' + element.attr('id') + '"]'),
              'reg_phone': () => element.closest('.tel-group'),
              'reg_email': () => element,
              'reg_company_name': () => element,
              'reg_password': () => element,
              'default': () => element.closest('.col-group') || element.closest('.form-group')
            };
            const placement = placementMap[element.attr('name')] || placementMap['default'];
            error.insertAfter(placement());
          },
          highlight: function (element, errorClass, validClass) {
            $(element).addClass(errorClass).removeClass(validClass);
            if ($(element).attr('name') === 'reg_phone') {
              $(element).closest('.tel-group').addClass(errorClass);
            }
          },
          unhighlight: function (element, errorClass, validClass) {
            $(element).removeClass(errorClass).addClass(validClass);
            if ($(element).attr('name') === 'reg_phone') {
              $(element).closest('.tel-group').removeClass(errorClass);
            }
          },
          submitHandler: function (form) {
            var civi_recaptcha = _this.find('input[name="civi_recaptcha"]').val();

            if (civi_recaptcha !== '0') {
              const recaptchaVersion = detectRecaptchaVersion(_this);

              if (recaptchaVersion === 'v3') {
                executeRecaptchaV3('register', _this).then(function (token) {
                  if (token) {
                    submitRegisterForm(token);
                  } else {
                    showMessageAndLoading(_this, theme_vars.recaptcha_verification_failed || "reCAPTCHA verification failed. Please try again.", "text-error", false);
                  }
                }).catch(function(error) {
                  showMessageAndLoading(_this, theme_vars.recaptcha_verification_failed || "reCAPTCHA verification failed. Please try again.", "text-error", false);
                });
                return false;
              } else if (recaptchaVersion === 'v2') {
                if (typeof grecaptcha !== "undefined" && typeof grecaptcha.getResponse === "function") {
                  const formCount = $("form.ux-login, form.ux-register").length;
                  var g_recaptcha_response = getRecaptchaResponse(_this, formCount - 1);
                  if (!g_recaptcha_response) {
                    showMessageAndLoading(_this, theme_vars.recaptcha_complete_verification || "Please complete the reCAPTCHA verification.", "text-error", false);
                    return false;
                  }
                  submitRegisterForm(g_recaptcha_response);
                  return false;
                } else {
                  showMessageAndLoading(_this, theme_vars.recaptcha_not_configured || "reCAPTCHA is not properly configured.", "text-error", false);
                  return false;
                }
              } else {
                showMessageAndLoading(_this, theme_vars.recaptcha_not_configured || "reCAPTCHA is not properly configured.", "text-error", false);
                return false;
              }
            } else {
              submitRegisterForm("");
            }

            function submitRegisterForm(recaptchaToken) {
              const data = {
                account_type: _this.find('input[name="account_type"]:checked').val(),
                firstname: _this.find('input[name="reg_firstname"]').val(),
                lastname: _this.find('input[name="reg_lastname"]').val(),
                companyname: _this.find('input[name="reg_company_name"]').val(),
                email: _this.find('input[name="reg_email"]').val(),
                phone: _this.find('input[name="reg_phone"]').val(),
                phone_code: _this.find('select[name="prefix_code"]').val(),
                password: _this.find('input[name="reg_password"]').val(),
                mc4wp_subscribe: _this.find('input[name="mc4wp-subscribe"]').val() || "",
                captcha: _this.find(".civi-captcha").val(),
                num_captcha: _this.find(".civi-num-captcha").data("captcha"),
                security: theme_vars.register_nonce,
                g_recaptcha_response: recaptchaToken,
                action: "get_register_user"
              };

              $.ajax({
                url: ajax_url,
                type: "POST",
                cache: false,
                dataType: "json",
                data: data,
                beforeSend: function () {
                  showMessageAndLoading(_this, theme_vars.send_user_info, "", true);
                },
                success: function (data) {
                  // Log debug info if available
                  if (data.debug && console && console.log) {
                    console.log('reCAPTCHA Debug Info:', data.debug);
                  }
                  handleAjaxSuccess(_this, _next, data);
                },
                error: function (xhr, status, error) {
                  console.error('Registration AJAX Error:', {xhr: xhr, status: status, error: error});
                  showMessageAndLoading(_this, theme_vars.ajax_error || "An error occurred. Please try again.", "text-error", false);
                }
              });
            }
          }
        });
      });

      $(".ux-verify").each(function () {
        var _this = $(this);
        var _prev = $(this).prev();
        _this.validate({
          rules: {
            verify_code: {
              required: true,
            },
            verify_code_phone: {
              required: true,
            },
          },
          submitHandler: function (form) {
            $.ajax({
              url: ajax_url,
              type: "POST",
              cache: false,
              dataType: "json",
              data: {
                verify_code: _this.find('input[name="verify_code"]').val(),
                verify_code_phone: _this
                  .find('input[name="verify_code_phone"]')
                  .val(),
                account_type: _prev
                  .find('input[name="account_type"]:checked')
                  .val(),
                firstname: _prev.find('input[name="reg_firstname"]').val(),
                lastname: _prev.find('input[name="reg_lastname"]').val(),
                companyname: _prev.find('input[name="reg_company_name"]').val(),
                email: _prev.find('input[name="reg_email"]').val(),
                phone: _prev.find('input[name="reg_phone"]').val(),
                phone_code: _prev.find('select[name="prefix_code"]').val(),
                password: _prev.find('input[name="reg_password"]').val(),
                security: theme_vars.verify_code_nonce,
                action: "verify_code",
              },
              beforeSend: function () {
                _this
                  .find("p.msg")
                  .removeClass("text-error text-success text-warning");
                _this.find("p.msg").text(theme_vars.send_user_info);
                _this.find("p.msg").show();
                $(".popup-account .loading-effect").fadeIn();
              },
              success: function (data) {
                _this.find("p.msg").text(data.messages);
                if (data.success != true) {
                  _this.find("p.msg").addClass(data.class);
                } else {
                  // Check if we should show pending approval form instead of redirecting
                  var effectiveMessage = data.messages_text || theme_vars[data.messages] || data.messages;
                  var isPendingApproval = false;
                  var pendingPatterns = [
                    'awaiting_admin_approval',
                    'waiting_approval',
                    'Registration successful. Awaiting admin approval.',
                    'Awaiting admin approval',
                    'pending approval',
                    'admin approval'
                  ];

                  // Check for pending approval messages
                  if (data.messages) {
                    for (var i = 0; i < pendingPatterns.length; i++) {
                      if (data.messages.includes(pendingPatterns[i])) {
                        isPendingApproval = true;
                        break;
                      }
                    }
                  }

                  if (!isPendingApproval && effectiveMessage) {
                    for (var i = 0; i < pendingPatterns.length; i++) {
                      if (effectiveMessage.includes(pendingPatterns[i])) {
                        isPendingApproval = true;
                        break;
                      }
                    }
                  }

                  if (isPendingApproval) {
                    // Show pending approval form instead of redirecting
                    var $container = _this.closest('.popup-account, .el-user-form');
                    var $pendingForm = $container.find('.ux-pending-approval');

                    if ($pendingForm.length > 0) {
                      // Hide all other forms
                      $container.find('.form-account').removeClass('active');

                      // Show pending approval form
                      $pendingForm.show().addClass('active');

                      // Disable submit buttons
                      _this.find('button[type="submit"], .gl-button.btn.button').prop('disabled', true).addClass('is-disabled');
                    }
                  } else if (data.url_redirect) {
                    window.location.href = data.url_redirect;
                  } else if (data.url_redirect === '') {
                    setTimeout(function () {
                      location.reload();
                    }, 2000);
                  } else {
                    location.reload();
                  }
                }
                $(".popup-account .loading-effect").fadeOut();
              },
            });
          },
        });
      });

      let isResending = false;
      $(".ux-verify .resend").on("click", function (e) {
        e.preventDefault();
        if (isResending) return;
        isResending = true;
        const _this = $(this);
        const data = {
          companyname: _this.closest('.el-user-form, .popup-account').find('.ux-register input[name="reg_company_name"]').val(),
          email: _this.closest('.el-user-form, .popup-account').find('.ux-register input[name="reg_email"]').val(),
          phone: _this.closest('.el-user-form, .popup-account').find('.ux-register input[name="reg_phone"]').val(),
          resend: _this.data("resend"),
          security: theme_vars.verify_resend_nonce,
          action: "civi_verify_resend"
        };

        $.ajax({
          type: "POST",
          dataType: "json",
          url: ajax_url,
          data: data,
          beforeSend: function () {
            _this.find(".btn-loading").css("display", "inline-block");
          },
          success: function () {
            _this.find(".btn-loading").css("display", "none");
            isResending = false;
          },
          error: function () {
            _this.find(".btn-loading").css("display", "none");
            isResending = false;
          }
        });
      });

      $(".verify-email").on("click", function (e) {
        e.preventDefault();
        const _this = $(this);
        const form = _this.closest("form");
        const email = form.find('input[name="user_login"]').val();

        $.ajax({
          type: "POST",
          dataType: "json",
          url: ajax_url,
          data: {
            email: email,
            action: "civi_verify_email"
          },
          beforeSend: function () {
            form.closest(".civi-reset-password-wrap").find("p.msg").removeClass("text-error text-success").hide();
            form.find(".verify-email").addClass("loading");
            form.find(".verify-field").hide();
          },
          success: function (data) {
            form.closest(".civi-reset-password-wrap").find("p.msg").text(data.message).addClass(data.class).show();
            form.find(".verify-email").removeClass("loading");
            if (data.success === true) {
              form.find(".verify-field").show();
              let countdown = sessionStorage.getItem('verifyCountdown') || 60;
              const text = form.find(".verify-email").attr("data-title");
              const interval = setInterval(function () {
                form.find(".verify-email").text("(" + countdown + ")").addClass("disabled");
                sessionStorage.setItem('verifyCountdown', countdown);
                countdown--;
                if (countdown < 0) {
                  clearInterval(interval);
                  form.find(".verify-email").text(text).removeClass("disabled");
                  sessionStorage.removeItem('verifyCountdown');
                }
              }, 1000);
            }
          },
          error: function (xhr, status, error) {
            form.closest(".civi-reset-password-wrap").find("p.msg").text(theme_vars.ajax_error || "An error occurred. Please try again.").addClass("text-error").show();
            form.find(".verify-email").removeClass("loading");
          }
        });
      });

      $('.el-uf-nav a').on('click', function (e) {
        e.preventDefault();
        const _this = $(this);
        const formType = _this.data('form');
        const parent = _this.closest('.el-user-form');
        parent.find('.el-uf-nav a').removeClass('active');
        _this.addClass('active');
        parent.find('.el-uf-item').removeClass('active');
        parent.find('.ux-' + formType).closest('.el-uf-item').addClass('active');
      });
    },

    forget_password: function ($this) {
      $(".forgot-password").on("click", function () {
        $(".civi-resset-password-wrap").slideToggle();
      });

      $(".civi_forgetpass").on("click", function (e) {
        e.preventDefault();
        var $form = $(this).parents("form");
        $(".ux-login p.error").hide();

        $.ajax({
          type: "post",
          url: ajax_url,
          dataType: "json",
          data: $form.serialize(),
          beforeSend: function () {
            $(".popup-account p.msg").removeClass(
              "text-error text-success text-warning"
            );
            $(".popup-account p.msg").text(theme_vars.forget_password);
            $(".civi-reset-password-wrap p.msg").show();
            $(".popup-account .loading-effect").fadeIn();
          },
          success: function (data) {
            $(".civi-reset-password-wrap p.msg").text(data.message);
            $(".civi-reset-password-wrap p.msg").addClass(data.class);
            $(".popup-account .loading-effect").fadeOut();
          },
        });
      });

      $(".generate-password").on("click", function (e) {
        e.preventDefault();
        var Password = {
          _pattern: /[a-zA-Z0-9_\-\+\.\}\{\?\!\@\#\$\%\&\*\~]/,

          _getRandomByte: function () {
            // http://caniuse.com/#feat=getrandomvalues
            if (window.crypto && window.crypto.getRandomValues) {
              var result = new Uint8Array(1);
              window.crypto.getRandomValues(result);
              return result[0];
            } else if (window.msCrypto && window.msCrypto.getRandomValues) {
              var result = new Uint8Array(1);
              window.msCrypto.getRandomValues(result);
              return result[0];
            } else {
              return Math.floor(Math.random() * 256);
            }
          },

          generate: function (length) {
            return Array.apply(null, { length: length })
              .map(function () {
                var result;
                while (true) {
                  result = String.fromCharCode(this._getRandomByte());
                  if (this._pattern.test(result)) {
                    return result;
                  }
                }
              }, this)
              .join("");
          },
        };
        $("#new-password").val(Password.generate(24));
        $("#new-password-error").fadeOut();
      });

      $(".control-password span").on("click", function () {
        var _this = $(this);
        if (_this.hasClass("active")) {
          _this.removeClass("active");
          $(this).parent().find("input").attr("type", "password");
        } else {
          _this.addClass("active");
          $(this).parent().find("input").attr("type", "text");
        }
      });

      $(".civi-new-password-wrap form").validate({
        rules: {
          new_password: {
            required: true,
            minlength: 8,
          },
        },
        submitHandler: function (form) {
          var new_password = $(form).find('input[name="new_password"]').val();
          var confirm_password = $(form)
            .find('input[name="confirm_password"]')
            .val();
          var login = $(form).find('input[name="login"]').val();
          var key = $(form).find('input[name="key"]').val();

          $.ajax({
            type: "POST",
            url: ajax_url,
            data: {
              new_password: new_password,
              confirm_password: confirm_password,
              login: login,
              key: key,
              security: theme_vars.change_password_nonce,
              action: "change_password_ajax",
            },
            beforeSend: function () {
              $(".civi-new-password-wrap p.msg").removeClass(
                "text-error text-success text-warning"
              );
              $(".civi-new-password-wrap p.msg").text(
                theme_vars.change_password
              );
              $(".civi-new-password-wrap p.msg").show();
              $(".civi-new-password-wrap .loading-effect").fadeIn();
            },
            success: function (data) {
              var data = $.parseJSON(data);
              $(".civi-new-password-wrap p.msg").text(data.message);
              $(".civi-new-password-wrap p.msg").addClass(data.class);
              $(".popup-account .loading-effect").fadeOut();
              if (data.success) {
                var baseurl = window.location.origin + window.location.pathname;
                window.location.href = baseurl;
              }
            },
          });
        },
      });
    },

    cookie_notices: function () {
      if (
        theme_vars.notice_cookie_enable == 1 &&
        theme_vars.notice_cookie_confirm != "yes" &&
        theme_vars.notice_cookie_messages != "" &&
        sessionStorage.getItem("hide-cookie-form") != "true" &&
        $("body.home").length > 0
      ) {
        $.growl({
          location: "br",
          fixed: true,
          duration: 3600000,
          size: "large",
          title: "",
          message: theme_vars.notice_cookie_messages,
        });

        $("#civi-button-cookie-notice-not-ok").on("click", function () {
          $(this).closest("#growls-br").remove();
          sessionStorage.setItem("hide-cookie-form", "true");
        });

        $("#civi-button-cookie-notice-ok").on("click", function () {
          $(this).closest("#growls-br").remove();

          var _data = {
            action: "notice_cookie_confirm",
          };

          _data = $.param(_data);

          $.ajax({
            url: theme_vars.ajax_url,
            type: "POST",
            data: _data,
            dataType: "json",
            success: function (results) { },
            error: function (errorThrown) {
              error.log(errorThrown);
            },
          });
        });
      }
    },

    user_form_settings: function () {
      if ($(".elementor-widget-civi-user-form").length > 0) {
        $("body").find(".popup-account").remove();
        $("body").find(".account.logged-out").css("display", "none");
      }
    },

    phone_input_handler: function () {
      var phoneInputs = ['#author_mobile_number', '#apply_phone', '#jobs_apply_call_to', '#ip_reg_phone', '#company_phone'];

      function getCurrentPrefix($input) {
        var prefix = $input.attr('data-prefix');
        if (!prefix) {
          var $select = $('.prefix-code');
          prefix = $select.length ? $select.find(':selected').data('dial-code') : '+1';
        }
        return prefix;
      }

      $('.prefix-code').on('change', function () {
        var $select = $(this);
        var selectedCode = $select.find(':selected').data('dial-code');
        phoneInputs.forEach(function (inputId) {
          var $input = $(inputId);
          if ($input.length) {
            $input.attr('data-prefix', selectedCode);
            var phone = $input.val().replace(/[^0-9+]/g, '').replace(/^\+\d+/, '').replace(/^0+/, '');
            $input.val(selectedCode + phone);
          }
        });
      });

      phoneInputs.forEach(function (inputId) {
        $(inputId).on('input', function (e) {
          var $input = $(this);
          var prefix = getCurrentPrefix($input);
          var currentValue = $input.val().replace(/[^0-9+]/g, '');

          if (!currentValue.startsWith(prefix)) {
            var phone = currentValue.replace(/^\+\d+/, '').replace(/^0+/, '');
            $input.val(prefix + phone);
          }
        });

        $(inputId).on('blur', function () {
          var $input = $(this);
          var prefix = getCurrentPrefix($input);
          var phone = $input.val().replace(/[^0-9+]/g, '');
          phone = phone.replace(new RegExp('^' + prefix.replace(/\+/g, '\\+') + '0+'), prefix);
          if (!phone.startsWith(prefix)) {
            phone = phone.replace(/^\+\d+/, '').replace(/^0+/, '');
            $input.val(prefix + phone);
          } else {
            $input.val(phone);
          }
        });

        $(inputId).on('keydown', function (e) {
          var $input = $(this);
          var prefix = getCurrentPrefix($input);
          var cursorPos = $input[0].selectionStart;
          var prefixLength = prefix.length;
          if (cursorPos <= prefixLength && (e.key === 'Backspace' || e.key === 'Delete')) {
            e.preventDefault();
          }
          if (cursorPos < prefixLength && !e.ctrlKey && !e.metaKey && e.key.length === 1) {
            e.preventDefault();
          }
        });

        (function initializeInput() {
          var $input = $(inputId);
          if ($input.length) {
            var prefix = getCurrentPrefix($input);
            $input.attr('data-prefix', prefix);
            var currentValue = $input.val();
            if (currentValue && !currentValue.startsWith(prefix)) {
              var phone = currentValue.replace(/[^0-9+]/g, '').replace(/^\+\d+/, '').replace(/^0+/, '');
              $input.val(prefix + phone);
            } else if (!currentValue) {
              $input.val(prefix);
            }
          }
        })();
      });
    },

    check_company_tooltip: function () {
      document.querySelectorAll('.civi-check-company').forEach((el) => {
        const tooltip = el.querySelector('.tip-content');
        if (!tooltip) return;
        const rect = tooltip.getBoundingClientRect();
        if (rect.left < 0) {
          tooltip.style.left = '0';
          tooltip.style.transform = 'translateY(calc(-100% - 10px))';
        } else if (rect.right > window.innerWidth) {
          tooltip.style.left = 'auto';
          tooltip.style.right = '0';
          tooltip.style.transform = 'translateY(calc(-100% - 10px))';
        }
      });
    },
  };

  CIVI.onReady = {
    init: function () {
      CIVI.element.init();
    },
  };

  CIVI.onLoad = {
    init: function () {
      CIVI.element.windowLoad();
    },
  };

  CIVI.onScroll = {
    init: function () {
      // Scroll Window
    },
  };

  CIVI.onResize = {
    init: function () {
      // Resize Window
    },
  };

  $(document).ready(CIVI.onReady.init);
  $(window).on('scroll', CIVI.onScroll.init);
  $(window).on('resize', CIVI.onResize.init);
  $(window).on('load', CIVI.onLoad.init);
})(jQuery);
