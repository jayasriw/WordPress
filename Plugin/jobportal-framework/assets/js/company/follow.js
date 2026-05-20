var FOLLOW = FOLLOW || {};
(function ($) {
  "use strict";

  FOLLOW = {
    init: function () {
      var follow_save = jobportal_template_vars.follow_save,
        follow_saved = jobportal_template_vars.follow_saved,
        package_expires = jobportal_template_vars.package_expires,
        ajax_url = jobportal_template_vars.ajax_url;

      $("body").on("click", ".jobportal-add-to-follow", function (e) {
        e.preventDefault();
        if (!$(this).hasClass("on-handle")) {
          var $this = $(this).addClass("on-handle"),
            company_inner = $this
              .closest(".company-inner")
              .addClass("company-active-hover"),
            company_id = $this.attr("data-company-id"),
            save = "";

          if (!$this.hasClass("added")) {
            var offset = $this.offset(),
              width = $this.width(),
              height = $this.height(),
              coords = {
                x: offset.left + width / 2,
                y: offset.top + height / 2,
              };
          }

          $.ajax({
            type: "post",
            url: ajax_url,
            dataType: "json",
            data: {
              action: "jobportal_add_to_follow",
              company_id: company_id,
              nonce: jobportal_ajax_nonce.follow,
            },
            beforeSend: function () {
              $this.children(".icon-plus i").removeClass("far fa-plus");
              $this
                .find(".icon-plus")
                .html('<span class="jobportal-dual-ring"></span>');
            },
            success: function (data) {
              if (data.added) {
                $this.removeClass("removed").addClass("added");
                $this
                  .parents(".jobportal-company-item")
                  .removeClass("removed-follow");
                $this.html(
                  '<span class="icon-plus"><i class="far fa-check"></i></span>' +
                    follow_saved
                );
              } else {
                $this.removeClass("added").addClass("removed");
                $this.parents(".jobportal-company-item").addClass("removed-follow");
                $this.html(
                  '<span class="icon-plus"><i class="far fa-plus"></i></span>' +
                    follow_save
                );
              }
              if (data.package_expires) {
                $this.removeClass("added").removeClass("removed");
                alert(package_expires);
                window.location.reload();
              }
              if (typeof data.added == "undefined") {
                console.log("login?");
              }
              $this.removeClass("on-handle");
              company_inner.removeClass("company-active-hover");
            },
            error: function (xhr) {
              var err = eval("(" + xhr.responseText + ")");
              $this.children("i").removeClass("fa-spinner fa-spin");
              $this.removeClass("on-handle");
              company_inner.removeClass("company-active-hover");
            },
          });
        }
      });
    },
  };
  $(document).ready(function () {
    FOLLOW.init();
  });
})(jQuery);
