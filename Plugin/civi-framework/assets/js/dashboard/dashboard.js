var DASHBOARD = DASHBOARD || {};
(function ($) {
  "use strict";

  DASHBOARD = {
    init: function () {
      this.closebtn();
      this.opennav();
      this.icon_setting();
      this.scrollToElement();
      this.scroll_top();
      this.tabs();
      this.tabs_active();
      this.tabs_change_heading();
      this.tabs_popup();
      this.form_popup();
      this.svg();
      this.search_canvas();
      this.select_tabs();
      this.about_jobs();
      this.service_conver_price();
      this.toggle_password();
      this.about_company();
      this.check_company();
    },
    svg: function () {
      var $nav = $(".list-nav-dashboard .nav-item");
      var colorAccent = $(".civi-nav-dashboard").data("accent") || "#007456";
      var colorSecondary = $(".civi-nav-dashboard").data("secondary") || "#999";

      function setSvgColor($li, color) {
        var $object = $li.find("object.civi-svg");
        if ($object.length) {
          try {
            var contentDoc = $object[0].contentDocument;
            if (contentDoc) {
              $(contentDoc).find("path").attr("fill", color);
            }
          } catch (e) {
            console.warn("Cannot access SVG content:", e);
          }
        }
      }

      function getActiveNav() {
        return $nav.filter(".active");
      }

      $nav.each(function () {
        setSvgColor($(this), colorSecondary);
      });

      var $navActive = getActiveNav();
      if ($navActive.length) {
        setSvgColor($navActive, colorAccent);
      }

      $nav.find("a").off("mouseenter.svg mouseleave.svg").on({
        "mouseenter.svg": function () {
          var $li = $(this).closest(".nav-item");
          setSvgColor($li, colorAccent);
        },
        "mouseleave.svg": function () {
          var $li = $(this).closest(".nav-item");
          if (!$li.hasClass("active")) {
            setSvgColor($li, colorSecondary);
          }
          var $currentActive = getActiveNav();
          if ($currentActive.length) {
            setSvgColor($currentActive, colorAccent);
          }
        }
      });

      $nav.find("object.civi-svg").off("load.svg").on("load.svg", function () {
        var $li = $(this).closest(".nav-item");
        if ($li.hasClass("active")) {
          setSvgColor($li, colorAccent);
        } else {
          setSvgColor($li, colorSecondary);
        }
      });

      $nav.find("a").off("click.svg-update").on("click.svg-update", function() {
        setTimeout(function() {
          var $currentActive = getActiveNav();
          if ($currentActive.length) {
            setSvgColor($currentActive, colorAccent);
          }
        }, 10);
      });
    },

    closebtn: function () {
      var $nav = $(".nav-dashboard-wapper");
      var $close = $nav.find(".closebtn");
      $close.on('click', function () {
        $(".nav-dashboard-wapper").toggleClass("close");
        if ($close.find("i").hasClass("fas fa-arrow-right")) {
          $close.find("i").removeAttr("class", "fas fa-arrow-right");
          $(this).find("i").attr("class", "fas fa-arrow-left");
          $nav.css({ width: "260px", "overflow-y": "auto" });
          $nav.find(".nav-profile-strength").css("display", "block");
          $nav.find(".nav-item a").removeClass("tooltip");

          if ($("body").hasClass("rtl")) {
            $(".page-dashboard #civi-content-dashboard").css(
              "padding-right",
              "290px"
            );
          } else {
            $(".page-dashboard #civi-content-dashboard").css(
              "padding-left",
              "290px"
            );
          }
        } else {
          $close.find("i").removeAttr("class", "fas fa-arrow-left");
          $(this).find("i").attr("class", "fas fa-arrow-right");
          $nav.css({ width: "55px", "overflow-y": "unset" });
          $nav.find(".nav-profile-strength").css("display", "none");
          $nav.find(".nav-item a").addClass("tooltip");

          if ($("body").hasClass("rtl")) {
            $nav.find(".list-nav-dashboard").css("margin-right", "0");
            $(".page-dashboard #civi-content-dashboard").css(
              "padding-right",
              "85px"
            );
          } else {
            $nav.find(".list-nav-dashboard").css("margin-left", "0");
            $(".page-dashboard #civi-content-dashboard").css(
              "padding-left",
              "85px"
            );
          }
        }
      });
    },

    search_canvas: function () {
      var search_id =
        "#" + $(".form-search-canvas .jobs-search-canvas").attr("id");
      var available = $(search_id).data("key");

      if (window.matchMedia("(max-width: 1199PX)").matches) {
        $(search_id)
          .autocomplete({
            source: available,
            minLength: 0,
            autoFocus: false,
            focus: true,
          })
          .focus(function () {
            $(this).data("uiAutocomplete").search($(this).val());
          });
      }
    },

    opennav: function () {
      var dashboard = $(".nav-dashboard-inner");
      dashboard.find(".icon-nav-mobie").on('click', function () {
        dashboard.toggleClass("open-nav");
        if (dashboard.hasClass("open-nav")) {
          $(this).css("left", "260px");
        } else {
          $(this).css("left", "0");
        }
      });
      dashboard.find(".bg-overlay").on('click', function () {
        dashboard.removeClass("open-nav");
        dashboard.find(".icon-nav-mobie").css("left", "0");
      });
    },

    icon_setting: function () {
      var icon_setting = ".action-setting .icon-setting";
      $("body").on("click", icon_setting, function (e) {
        var action = $(this).closest(".action-setting");
        var dropdown = $(action).find(".action-dropdown");
        e.preventDefault();
        $(dropdown).toggleClass("show");
        $(".action-setting .action-dropdown").not(dropdown).removeClass("show");
        $(action).css("z-index", "2");
        $(".action-setting").not(action).css("z-index", "1");
        e.stopPropagation();
      });

      $(document).on("click", function () {
        $(".action-setting .action-dropdown").removeClass("show");
        $(".action-setting").css("z-index", "1");
      });

      $("body").on("click", ".action-setting .action-dropdown", function (e) {
        e.stopPropagation();
      });
    },

    scrollToElement: function () {
      var ele = $("#company-review-details");
      var hash = location.hash.replace("#", "");
      $(window).on('load', function () {
        if (hash == "company-review-details") {
          $("html, body").animate({ scrollTop: ele.offset().top }, 1000);
        }
      });
    },

    scroll_top: function () {
      var scrollHeader = 100;
      var submit = $(".civi-submit-header");
      $(window).on('scroll', function () {
        var scroll = getCurrentScroll();
        if (scroll >= scrollHeader) {
          submit.addClass("scroll");
        } else {
          submit.removeClass("scroll");
        }
      });

      function getCurrentScroll() {
        return window.pageYOffset;
      }
    },

    toggle_password: function () {
      $(".civi-toggle-password").on('click', function () {
        $(this).toggleClass("fa-eye fa-eye-slash");
        var input = $($(this).attr("toggle"));
        if (input.attr("type") == "password") {
          input.attr("type", "text");
        } else {
          input.attr("type", "password");
        }
      });
    },

    tabs: function () {
      function tab_dashboard(obj) {
        $(".tab-dashboard ul li").removeClass("active");
        $(obj).addClass("active");
        var id = $(obj).find("a").attr("href");
        $(".tab-info").hide();
        $(id).show();
      }

      $(".tab-list li").on('click', function () {
        tab_dashboard(this);
        return false;
      });

      var current_page = $("#main div:first-child").attr("id");

      var localStorageValue = localStorage.getItem(
        "session_civi_tab_dashboard" + "_" + current_page
      );

      var hash = window.location.hash;
      if (hash && $(".tab-list li a[href='" + hash + "']").length > 0) {
        tab_dashboard($(".tab-list li a[href='" + hash + "']").parent());
      } else if (localStorageValue == null) {
        tab_dashboard($(".tab-list li:first-child"));
      } else {
         // Optionally handle localStorage case if needed to persist across reloads without hash
         // For now, if no hash, we can respect the clicked one if logic permits,
         // but existing code only checked null. Let's keep existing behavior for non-hash.
          var $savedTab = $(".tab-list li a[href='" + localStorageValue + "']");
          if($savedTab.length > 0){
              tab_dashboard($savedTab.parent());
          } else {
              tab_dashboard($(".tab-list li:first-child"));
          }
      }
    },

    tabs_active: function () {
      function tab_active(obj) {
        $(".tab-dashboard-active ul li").removeClass("active");
        $(".tab-info-active").removeClass("active");
        $(obj).addClass("active");
        var id = $(obj).find("a").attr("href");
        $(".tab-info-active").hide();
        $(id).show();
      }

      $(".tab-list-active li").on('click', function () {
        tab_active(this);
        return false;
      });
    },

    tabs_change_heading: function () {
      var $my_candidate = $(".my-candidate");
      $my_candidate.find(".tab-list li").on('click', function () {
        var $tab_active = $(this).find("a");
        var $text = $tab_active.data("text");

        $my_candidate.find(".entry-title h4").text($text);
      });
    },

    tabs_popup: function () {
      function tab_popup(obj) {
        $(".tab-popup-wrapper ul li").removeClass("active");
        $(obj).addClass("active");
        var id = $(obj).find("a").attr("href");
        $(id).show();
      }

      $(".tab-list-popup li").on('click', function () {
        $(".tab-popup").hide();
        tab_popup(this);
        return false;
      });
    },

    form_popup: function () {
      $(".form-popup").each(function () {
        if ($(this).is("#form-invite-popup")) {
          var form_popup = "#form-invite-popup";
          var btn_popup = "#btn-invite-candidate";
        } else if ($(this).is("#form-messages-popup")) {
          var form_popup = "#form-messages-popup";
          var btn_popup = "#civi-add-messages";
        } else if ($(this).is("#form-messages-applicants")) {
          var form_popup = "#form-messages-applicants";
          var btn_popup = "#btn-mees-applicants";
        } else if ($(this).is("#form-setting-deactive")) {
          var form_popup = "#form-setting-deactive";
          var btn_popup = "#btn-setting-deactive";
        } else if ($(this).is("#form-candidate-user-package")) {
          var form_popup = "#form-candidate-user-package";
          var btn_popup = "#action-user-package";
        } else if ($(this).is("#form-service-order-refund")) {
          var form_popup = "#form-service-order-refund";
          var btn_popup = ".btn-order-refund";
        } else if ($(this).is("#form-service-view-reason")) {
          var form_popup = "#form-service-view-reason";
          var btn_popup = ".btn-view-reason";
        } else if ($(this).is("#form-service-withdraw")) {
          var form_popup = "#form-service-withdraw";
          var btn_popup = "#btn-service-withdraw";
        } else if ($(this).is("#form-employer-user-package")) {
          var form_popup = "#form-employer-user-package";
          var btn_popup = "#action-employer-user-package";
        } else {
          var form_popup = "#civi-form-setting-meetings";
          var btn_popup = "#btn-meeting-settings";
        }

        var $btn_close = $(form_popup).find(".btn-close");
        var $bg_overlay = $(form_popup).find(".bg-overlay");
        var $btn_cancel = $(form_popup).find(".button-cancel");

        function open_popup(e) {
          e.preventDefault();
          $(form_popup).css({ opacity: "1", visibility: "unset" });
        }

        function close_popup(e) {
          e.preventDefault();
          $(form_popup).css({ opacity: "0", visibility: "hidden" });
        }

        $("body").on("click", btn_popup, open_popup);
        $bg_overlay.on('click', close_popup);
        $btn_close.on('click', close_popup);
        $btn_cancel.on('click', close_popup);
      });
    },

    select_tabs: function () {
      $(".civi-section-salary-select").hide();
      $("#select-salary-pay").on('change', function () {
        $(".civi-section-salary-select").hide();
        $("#" + $(this).val()).show();
        if ($(this).val() == "agree") {
          $("#jobs_rate").hide();
        } else {
          $("#jobs_rate").show();
        }
      });
      $("#" + $("#select-salary-pay option[selected]").val()).show();

      //Apply
      $(".civi-section-apply-select").hide();
      $("#select-apply-type").on('change', function () {
        $(".civi-section-apply-select").hide();
        $("#" + $(this).val()).show();
      });
      $("#" + $("#select-apply-type").val()).show();
    },

    about_jobs: function () {
      var $form_general = $("#jobs-submit-general"),
        $title = $form_general.find('input[name="jobs_title"]'),
        $cate = $form_general.find('select[name="jobs_categories"]'),
        $type = $form_general.find('select[name="jobs_type"]'),
        $about = $(".about-jobs-dashboard"),
        $text_title = $about.find(".title-about"),
        $text_cate = $about.find(".cate-about"),
        $label_type = $about.find(".label-wrapper .label-type-inner"),
        $label_location = $about.find(".label-wrapper .label-location-inner"),
        $logo = $about.find(".img-company"),
        $title_company = $about.find(".name-company");

      $title
        .on('keyup', function () {
          var value = $(this).val();
          var data_title = $text_title.data("title");
          if (value == "") {
            $text_title.text(data_title);
          } else {
            $text_title.text(value);
          }
        })
        .trigger("keyup");

      $cate
        .on('change', function () {
          var value = "";
          var data_cate = $text_cate.data("cate");
          $(this)
            .find("option:selected")
            .each(function () {
              value += $(this).text() + " ";
            });
          if ($(this).val() == "") {
            $text_cate.text(data_cate);
          } else {
            $text_cate.text(value);
          }
        })
        .trigger("change");

      $type
        .on('change', function () {
          var value = "",
            html = "";
          $(this)
            .find("option:selected")
            .each(function () {
              value = $(this).text() + " ";
              html += '<div class="label label-type">' + value + "</div>";
            });
          $label_type.html(html);
        })
        .trigger("change");

      var $location = $('select[name="jobs_location"]');
      $location
        .on('change', function () {
          var value = "";
          $(this)
            .find("option:selected")
            .each(function () {
              value =
                '<div class="label label-location"><i class="fas fa-map-marker-alt"></i>' +
                $(this).text() +
                "</div>";
            });
          if ($(this).val() !== "") {
            $label_location.html(value);
          }
        })
        .trigger("change");

      //company
      var $company = $('select[name="jobs_select_company"]');
      $company
        .on('change', function () {
          var company_url = "",
            company_title = "",
            data_name = $title_company.data("name");
          $(this)
            .find("option:selected")
            .each(function () {
              company_url = $(this).data("url");
              company_title = $(this).text();
            });
          if (company_url == "") {
            $logo.html('<i class="far fa-camera"></i>');
          } else {
            $logo.html('<img src ="' + company_url + '" alt=""/>');
          }
          if ($(this).val() !== "") {
            $title_company.text(company_title);
          } else {
            $title_company.text(data_name);
          }
        })
        .trigger("change");

      //salary
      var $form_salary = $("#jobs-submit-salary"),
        $salary_currency = $form_salary.find(
          'select[name="jobs_currency_type"]'
        ),
        $salary_show = $form_salary.find('select[name="jobs_salary_show"]'),
        $salary_rate = $form_salary.find('select[name="jobs_salary_rate"]'),
        $salary_minimum = $form_salary.find(
          'input[name="jobs_salary_minimum"]'
        ),
        $salary_maximum = $form_salary.find(
          'input[name="jobs_salary_maximum"]'
        ),
        $maximum_price = $form_salary.find('input[name="jobs_maximum_price"]'),
        $minimum_price = $form_salary.find('input[name="jobs_minimum_price"]'),
        $label_price = $about.find(".label-price");

      function getThousandSeparator() {
        var thousand_sep = (typeof theme_vars !== 'undefined' ? theme_vars.thousand_separator : '') || (typeof civi_submit_vars !== 'undefined' ? civi_submit_vars.thousand_separator : '');

        if (!thousand_sep || thousand_sep.trim() === '') {
          return ' ';
        }

        return thousand_sep;
      }

      $salary_show
        .on('change', function () {
          var html = "",
            salary_show = $salary_show.find("option:selected").val(),
            text_min = (typeof theme_vars !== 'undefined' ? theme_vars.salary_text_minimum : '') || (typeof civi_submit_vars !== 'undefined' ? civi_submit_vars.salary_text_minimum : '') || $label_price.data("text-min"),
            text_max = (typeof theme_vars !== 'undefined' ? theme_vars.salary_text_maximum : '') || (typeof civi_submit_vars !== 'undefined' ? civi_submit_vars.salary_text_maximum : '') || $label_price.data("text-max"),
            text_agree = (typeof theme_vars !== 'undefined' ? theme_vars.salary_text_negotiable : '') || (typeof civi_submit_vars !== 'undefined' ? civi_submit_vars.salary_text_negotiable : '') || $label_price.data("text-agree"),
            salary_rate = $salary_rate.find("option:selected").val(),
            salary_currency = $salary_currency.find("option:selected").val(),
            currency_position = (typeof theme_vars !== 'undefined' ? theme_vars.currency_position : '') || (typeof civi_submit_vars !== 'undefined' ? civi_submit_vars.currency_position : '');
          if (salary_show == "range") {
            // Get thousand separator from theme options
            var thousand_separator = getThousandSeparator();

            var salary_minimum = $salary_minimum
              .val()
              .toString()
              .replace(/\B(?=(\d{3})+(?!\d))/g, thousand_separator);
            var salary_maximum = $salary_maximum
              .val()
              .toString()
              .replace(/\B(?=(\d{3})+(?!\d))/g, thousand_separator);
            $maximum_price.val("");
            $minimum_price.val("");
            if (currency_position === "before") {
              html =
                '<i class="fas fa-money-bill-alt"></i><span class="salary-currency">' +
                salary_currency +
                '</span><span class="salary-minimum">' +
                salary_minimum +
                '</span> - <span class="salary-currency">' +
                salary_currency +
                '</span><span class="salary-maximum">' +
                salary_maximum +
                '</span> / <span class="salary-rate">' +
                salary_rate +
                "</span>";
            } else if (currency_position === "after") {
              html =
                '<i class="fas fa-money-bill-alt"></i><span class="salary-minimum">' +
                salary_minimum +
                '</span> <span class="salary-currency">' +
                salary_currency +
                '</span> - <span class="salary-maximum">' +
                salary_maximum +
                '</span> <span class="salary-currency">' +
                salary_currency +
                '</span> / <span class="salary-rate">' +
                salary_rate +
                "</span>";
            }
          }
          if (salary_show == "maximum_amount") {
            // Get thousand separator from theme options
            var thousand_separator = getThousandSeparator();

            var maximum_price = $maximum_price
              .val()
              .toString()
              .replace(/\B(?=(\d{3})+(?!\d))/g, thousand_separator);
            $salary_minimum.val("");
            $salary_maximum.val("");
            $minimum_price.val("");

            if (currency_position === "after") {
              html =
                '<i class="fas fa-money-bill-alt"></i>' +
                text_max +
                '<span class="price-maximum">' +
                maximum_price +
                '</span> <span class="salary-currency">' +
                salary_currency +
                '</span> / <span class="salary-rate">' +
                salary_rate +
                "</span>";
            } else {
              html =
                '<i class="fas fa-money-bill-alt"></i>' +
                text_max +
                '<span class="salary-currency">' +
                salary_currency +
                '</span><span class="price-maximum">' +
                maximum_price +
                '</span> / <span class="salary-rate">' +
                salary_rate +
                "</span>";
            }
          }
          if (salary_show == "starting_amount") {
            // Get thousand separator from theme options
            var thousand_separator = getThousandSeparator();

            var minimum_price = $minimum_price
              .val()
              .toString()
              .replace(/\B(?=(\d{3})+(?!\d))/g, thousand_separator);
            $salary_minimum.val("");
            $salary_maximum.val("");
            $maximum_price.val("");

            if (currency_position === "after") {
              html =
                '<i class="fas fa-money-bill-alt"></i>' +
                text_min +
                '<span class="price-minimum">' +
                minimum_price +
                '</span> <span class="salary-currency">' +
                salary_currency +
                '</span> / <span class="salary-rate">' +
                salary_rate +
                "</span>";
            } else {
              html =
                '<i class="fas fa-money-bill-alt"></i>' +
                text_min +
                '<span class="salary-currency">' +
                salary_currency +
                '</span><span class="price-minimum">' +
                minimum_price +
                '</span> / <span class="salary-rate">' +
                salary_rate +
                "</span>";
            }
          }
          if (salary_show == "agree") {
            $salary_minimum.val("");
            $salary_maximum.val("");
            $maximum_price.val("");
            $minimum_price.val("");
            html = text_agree;
          }
          $label_price.html(html);
        })
        .trigger("change");

      $salary_currency
        .on('change', function () {
          var salary_currency = $salary_currency.find("option:selected").val();
          $(".salary-currency").html(salary_currency);
        })
        .trigger("change");

      $salary_rate
        .on('change', function () {
          var salary_rate = $salary_rate.find("option:selected").val();
          $(".salary-rate").html(salary_rate);
        })
        .trigger("change");

      $salary_minimum
        .on('input', function () {
          var value = $(this).val();
          var decimal_separator = (typeof theme_vars !== 'undefined' ? theme_vars.decimal_separator : '') || (typeof civi_submit_vars !== 'undefined' ? civi_submit_vars.decimal_separator : '') || '.';
          var regex = new RegExp('[^0-9' + decimal_separator + ']', 'g');
          value = value.replace(regex, '');
          $(this).val(value);
        })
        .on('keyup', function () {
          var salary_minimum = $salary_minimum.val();
          if (salary_minimum) {
            // Use theme options for formatting
            var thousand_separator = getThousandSeparator();
            var decimal_separator = (typeof theme_vars !== 'undefined' ? theme_vars.decimal_separator : '') || (typeof civi_submit_vars !== 'undefined' ? civi_submit_vars.decimal_separator : '') || '.';


            if (!decimal_separator || decimal_separator.trim() === '') {
              decimal_separator = '.';
            }

            // Prevent same separators
            if (thousand_separator === decimal_separator) {
              if (decimal_separator === ',') {
                thousand_separator = ' ';
              } else {
                thousand_separator = ',';
              }
            }

            // Format number manually to avoid toLocaleString issues
            var number = parseFloat(salary_minimum);
            var formatted_salary = number.toString();

            // Add thousand separators manually
            formatted_salary = formatted_salary.replace(/\B(?=(\d{3})+(?!\d))/g, thousand_separator);
            $(".salary-minimum").html(formatted_salary);
          } else {
            $(".salary-minimum").html('');
          }

        })
        .trigger("keyup");

      $salary_maximum
        .on('input', function () {
          // Remove non-numeric characters except decimal separator
          var value = $(this).val();
          var decimal_separator = (typeof theme_vars !== 'undefined' ? theme_vars.decimal_separator : '') || (typeof civi_submit_vars !== 'undefined' ? civi_submit_vars.decimal_separator : '') || '.';
          var regex = new RegExp('[^0-9' + decimal_separator + ']', 'g');
          value = value.replace(regex, '');
          $(this).val(value);
        })
        .on('keyup', function () {
          var salary_maximum = $salary_maximum.val();
          if (salary_maximum) {
            // Use theme options for formatting
            var thousand_separator = getThousandSeparator();
            var decimal_separator = (typeof theme_vars !== 'undefined' ? theme_vars.decimal_separator : '') || (typeof civi_submit_vars !== 'undefined' ? civi_submit_vars.decimal_separator : '') || '.';


            if (!decimal_separator || decimal_separator.trim() === '') {
              decimal_separator = '.';
            }

            // Prevent same separators
            if (thousand_separator === decimal_separator) {
              if (decimal_separator === ',') {
                thousand_separator = ' '; // Use space for thousands
              } else {
                thousand_separator = ',';
              }
            }

            // Format number manually to avoid toLocaleString issues
            var number = parseFloat(salary_maximum);
            var formatted_salary = number.toString();

            // Add thousand separators manually
            formatted_salary = formatted_salary.replace(/\B(?=(\d{3})+(?!\d))/g, thousand_separator);
            $(".salary-maximum").html(formatted_salary);
          } else {
            $(".salary-maximum").html('');
          }

        })
        .trigger("keyup");

      $maximum_price
        .on('input', function () {
          // Remove non-numeric characters except decimal separator
          var value = $(this).val();
          var decimal_separator = (typeof theme_vars !== 'undefined' ? theme_vars.decimal_separator : '') || (typeof civi_submit_vars !== 'undefined' ? civi_submit_vars.decimal_separator : '') || '.';
          var regex = new RegExp('[^0-9' + decimal_separator + ']', 'g');
          value = value.replace(regex, '');
          $(this).val(value);
        })
        .on('keyup', function () {
          var maximum_price = $maximum_price.val();
          if (maximum_price) {
            // Use theme options for formatting
            var thousand_separator = getThousandSeparator();
            var decimal_separator = (typeof theme_vars !== 'undefined' ? theme_vars.decimal_separator : '') || (typeof civi_submit_vars !== 'undefined' ? civi_submit_vars.decimal_separator : '') || '.';


            if (!decimal_separator || decimal_separator.trim() === '') {
              decimal_separator = '.';
            }

            // Prevent same separators
            if (thousand_separator === decimal_separator) {
              if (decimal_separator === ',') {
                thousand_separator = ' '; // Use space for thousands
              } else {
                thousand_separator = ',';
              }
            }

            // Format number manually to avoid toLocaleString issues
            var number = parseFloat(maximum_price);
            var formatted_price = number.toString();

            // Add thousand separators manually
            formatted_price = formatted_price.replace(/\B(?=(\d{3})+(?!\d))/g, thousand_separator);
            $(".price-maximum").html(formatted_price);
          } else {
            $(".price-maximum").html('');
          }

        })
        .trigger("keyup");

      $minimum_price
        .on('input', function () {
          // Remove non-numeric characters except decimal separator
          var value = $(this).val();
          var decimal_separator = (typeof theme_vars !== 'undefined' ? theme_vars.decimal_separator : '') || (typeof civi_submit_vars !== 'undefined' ? civi_submit_vars.decimal_separator : '') || '.';
          var regex = new RegExp('[^0-9' + decimal_separator + ']', 'g');
          value = value.replace(regex, '');
          $(this).val(value);
        })
        .on('keyup', function () {
          var minimum_price = $minimum_price.val();
          if (minimum_price) {
            // Use theme options for formatting
            var thousand_separator = getThousandSeparator();
            var decimal_separator = (typeof theme_vars !== 'undefined' ? theme_vars.decimal_separator : '') || (typeof civi_submit_vars !== 'undefined' ? civi_submit_vars.decimal_separator : '') || '.';


            if (!decimal_separator || decimal_separator.trim() === '') {
              decimal_separator = '.';
            }

            // Prevent same separators
            if (thousand_separator === decimal_separator) {
              if (decimal_separator === ',') {
                thousand_separator = ' ';
              } else if (decimal_separator === '.') {
                thousand_separator = ',';
              } else {
                thousand_separator = ',';
              }
            }

            // Format number manually to avoid toLocaleString issues
            var number = parseFloat(minimum_price);
            var formatted_price = number.toString();

            // Add thousand separators manually
            formatted_price = formatted_price.replace(/\B(?=(\d{3})+(?!\d))/g, thousand_separator);
            $(".price-minimum").html(formatted_price);
          } else {
            $(".price-minimum").html('');
          }
        })
        .trigger("keyup");
    },

    about_company: function () {
      var $form_general = $("#company-submit-general"),
        $title = $form_general.find('input[name="company_title"]'),
        $company_avatar_url = $form_general.find("input.avatar_url"),
        $location = $('select[name="company_location"]'),
        $about = $(".about-company-dashboard"),
        $text_title = $about.find(".title-about"),
        $text_des = $about.find(".des-about"),
        $text_location = $about.find(".location-about");

      $title
        .on('keyup', function () {
          var value = $(this).val();
          var data_title = $text_title.data("title");
          if (value == "") {
            $text_title.text(data_title);
          } else {
            $text_title.text(value);
          }
        })
        .trigger("keyup");

      $location
        .on('change', function () {
          var value = "";
          var data_location = $text_location.data("location");
          $(this)
            .find("option:selected")
            .each(function () {
              value += $(this).text() + " ";
            });
          if ($(this).val() == "") {
            $text_location.text(data_location);
          } else {
            $text_location.text(value);
          }
        })
        .trigger("change");

      if (typeof tinyMCE !== "undefined") {
        if ($("#wp-company_des-wrap").hasClass("tmce-active")) {
          tinyMCE.get("company_des").on("keyup", function () {
            var value = tinyMCE
              .get("company_des")
              .getContent({ format: "text" });
            if (value == "") {
              $text_des.text("");
            } else {
              $text_des.text(value);
            }
          });
        }
      }
    },

    check_company: function () {
      var $form_company = $("#submit_company_form"),
        $company_website = $form_company.find('input[name="company_website"]'),
        $company_phone = $form_company.find('input[name="company_phone"]'),
        $company_location = $(
          '#company-submit-location select[name="company_location"]'
        ),
        $about = $(".about-company-dashboard"),
        $check_webs = $about.find(".check-webs"),
        $webs_verified = $check_webs.data("verified"),
        $webs_not_verified = $check_webs.data("not-verified"),
        $check_phone = $about.find(".check-phone"),
        $phone_verified = $check_phone.data("verified"),
        $phone_not_verified = $check_phone.data("not-verified"),
        $check_location = $about.find(".check-location"),
        $location_verified = $check_location.data("verified"),
        $location_not_verified = $check_location.data("not-verified");
      $company_website
        .on('keyup', function () {
          var value_website = $(this).val();
          if (value_website !== "") {
            $check_webs.html('<i class="fas fa-check"></i>' + $webs_verified);
            $check_webs.addClass("active");
          } else {
            $check_webs.html(
              '<i class="fas fa-check"></i>' + $webs_not_verified
            );
            $check_webs.removeClass("active");
          }
          if (
            $check_webs.hasClass("active") &&
            $check_phone.hasClass("active") &&
            $check_location.hasClass("active")
          ) {
            $about.find(".civi-check-company").addClass("active");
          } else {
            $about.find(".civi-check-company").removeClass("active");
          }
        })
        .trigger("keyup");

      $company_phone.on('keyup', function () {
        var phoneVal = $(this).val().replace(/\D/g, '');
        if (phoneVal.length >= 7 && phoneVal.length <= 12) {
          $check_phone.html('<i class="fas fa-check"></i>' + $phone_verified);
          $check_phone.addClass("active");
        } else {
          $check_phone.html('<i class="fas fa-check"></i>' + $phone_not_verified);
          $check_phone.removeClass("active");
        }
        if (
          $check_webs.hasClass("active") &&
          $check_phone.hasClass("active") &&
          $check_location.hasClass("active")
        ) {
          $about.find(".civi-check-company").addClass("active");
        } else {
          $about.find(".civi-check-company").removeClass("active");
        }
      }).trigger("keyup");

      $company_location
        .on('change', function () {
          var value_location = $(this).val();
          if (value_location !== "") {
            $check_location.html(
              '<i class="fas fa-check"></i>' + $location_verified
            );
            $check_location.addClass("active");
          } else {
            $check_location.html(
              '<i class="fas fa-check"></i>' + $location_not_verified
            );
            $check_location.removeClass("active");
          }
          if (
            $check_webs.hasClass("active") &&
            $check_phone.hasClass("active") &&
            $check_location.hasClass("active")
          ) {
            $about.find(".civi-check-company").addClass("active");
          } else {
            $about.find(".civi-check-company").removeClass("active");
          }
        })
        .trigger("change");
    },

    service_conver_price: function () {
      var $form_service = $("#submit_service_form"),
        $service_price = $form_service.find('input[name="service_price"]'),
        $price_received = $form_service.find('input[name="price_received"]'),
        $percentage_price = $price_received.data("price-received");
      $service_price
        .on('keyup', function () {
          var $price =
            (parseInt($service_price.val()) *
              (100 - parseInt($percentage_price))) /
            100;
          $price_received.val(parseInt($price));
        })
        .trigger("keyup");
    },
  };

  $(document).ready(function () {
    DASHBOARD.init();
  });
})(jQuery);
