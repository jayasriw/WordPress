var CANDIDATE = CANDIDATE || {};
(function ($) {
  "use strict";

  CANDIDATE = {
    init: function () {
      this.tab_candidate();
      this.social_candidate();
      this.login_notice();
      this.clickTabLocalStorage();
      this.showSavedTab();
      this.initPresentCheckboxes();
    },

    tab_candidate: function () {
      function tabcandidate(obj) {
        $(".jobs-candidate-sidebar ul li").removeClass("active");
        $(obj).addClass("active");
        var id = $(obj).find("a").attr("href");
        $(".tab-info-candidate").hide();
        $(id).show();
      }
      $(".tab-candidate li").on('click', function () {
        tabcandidate(this);
        return false;
      });
      tabcandidate($(".jobs-candidate-sidebar ul li:first-child"));
    },

    social_candidate: function () {
      $("body").on(
        "click",
        "#candidate-submit-social .soical-remove-inner",
        function () {
          var wrap = $(this).closest(".clone-wrap");
          $(wrap).find(".field-wrap").slideToggle();
        }
      );
    },

    login_notice: function () {
      var notice = $(".btn-login.notice-employer").data("notice");
      if ($(".btn-login").hasClass("notice-employer")) {
        $("#popup-form .notice").html(
          '<i class="fal fa-exclamation-circle"></i>' + notice
        );
      }
    },

    setTabLocalStorage: function (value) {
      var current_page = $("#main div:first-child").attr("id");
      localStorage.setItem(
        "session_jobportal_tab_dashboard" + "_" + current_page,
        value
      );
    },

    getTabLocalStorage: function () {
      var current_page = $("#main div:first-child").attr("id");
      var stored = localStorage.getItem("session_jobportal_tab_dashboard" + "_" + current_page);

      if (stored && $(".tab-list").find(`li a[href="${stored}"]`).length === 0) {
        CANDIDATE.clearTabLocalStorage();
        return null;
      }

      return stored;
    },
    present_education_to: function (row) {
      return function () {
        var checkbox = $(this);
        var presentToField = row.find(".present-to");

        if (checkbox.is(":checked")) {
          presentToField.find("input").remove();
          presentToField.append(
            '<input class="text-present" disabled type="text" name="candidate_education_to[]" value="' +
            text_present + '">'
          );
          checkbox.val("present");
        } else {
          presentToField.find("input").remove();
          presentToField.append(
            '<input type="date" placeholder="' + date_format + '" name="candidate_education_to[]" value="">'
          );
          checkbox.val("");
        }
      };
    },

    present_experience_to: function (row) {
      return function () {
        var checkbox = $(this);
        var presentToField = row.find(".present-to");

        if (checkbox.is(":checked")) {
          presentToField.find("input").remove();
          presentToField.append(
            '<input class="text-present" disabled type="text" name="candidate_experience_to[]" value="' +
            text_present + '">'
          );
          checkbox.val("present");
        } else {
          presentToField.find("input").remove();
          presentToField.append(
            '<input type="date" placeholder="' + date_format + '" name="candidate_experience_to[]" value="">'
          );
          checkbox.val("");
        }
      };
    },

    initPresentCheckboxes: function () {
      $(document).on("change", '#tab-education input[name="candidate_education_check[]"]', function () {
        var row = $(this).closest(".row");
        CANDIDATE.present_education_to(row).call(this);
      });

      $(document).on("change", '#tab-experience input[name="candidate_experience_check[]"]', function () {
        var row = $(this).closest(".row");
        CANDIDATE.present_experience_to(row).call(this);
      });

      $(document).on("click", "#tab-education .btn-more", function () {
        CANDIDATE.addEducationRow();
      });

      $(document).on("click", "#tab-experience .btn-more", function () {
        CANDIDATE.addExperienceRow();
      });
    },

    addEducationRow: function () {
      var template = $("#template-item-education");
      var currentSize = parseInt(template.attr("data-size")) || 0;
      var newSize = currentSize + 1;

      var newRow = template.html();
      newRow = newRow.replace('<span></span>', '<span>' + newSize + '</span>');

      template.before(newRow);
      template.attr("data-size", newSize);
    },

    addExperienceRow: function () {
      var template = $("#template-item-experience");
      if (template.length > 0) {
        var currentSize = parseInt(template.attr("data-size")) || 0;
        var newSize = currentSize + 1;

        var newRow = template.html();
        newRow = newRow.replace('<span></span>', '<span>' + newSize + '</span>');

        template.before(newRow);
        template.attr("data-size", newSize);
      }
    },

    switchToTab: function (obj) {
      if (!obj || obj.length === 0) {
        obj = $(".tab-list li:first-child");
      }

      $(".tab-list ul li, .tab-list li").removeClass("active");
      $(obj).addClass("active");

      var href = $(obj).find("a").attr("href");
      if (href) {
        $(".tab-info").hide().removeClass("active");
        $(href).show().addClass("active");

        CANDIDATE.setTabLocalStorage(href);
      }
    },


    showSavedTab: function () {
      var tabDefault = $(".tab-list li:first-child");
      var idStored = CANDIDATE.getTabLocalStorage();

      if (idStored !== null) {
        var foundTab = $(".tab-list").find(`li a[href="${idStored}"]`).parent();

        if (foundTab.length > 0) {
          tabDefault = foundTab;
        } else {
          CANDIDATE.clearTabLocalStorage();
        }
      }

      if (tabDefault.length === 0) {
        tabDefault = $(".tab-list li:first-child");
      }

      CANDIDATE.switchToTab(tabDefault);
    },

    clearTabLocalStorage: function () {
      var current_page = $("#main div:first-child").attr("id");
      localStorage.removeItem("session_jobportal_tab_dashboard" + "_" + current_page);
    },

    clickTabLocalStorage: function () {
      $(".tab-list li").off('click.candidate').on('click.candidate', function (e) {
        e.preventDefault();

        var href = $(this).find("a").attr("href");
        if (href) {
          CANDIDATE.setTabLocalStorage(href);
          CANDIDATE.switchToTab(this);
        }

        return false;
      });
    }
  };

  $(document).ready(function () {
    CANDIDATE.init();
  });
})(jQuery);
