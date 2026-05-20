var MESSAGES = MESSAGES || {};
(function ($) {
  "use strict";
  var ajax_url = civi_template_vars.ajax_url,
    uxper_messages = $(".uxper-messages"),
    form_popup = $("#form-messages-popup");

  MESSAGES = {
    init: function () {
      this.send_messages();
      this.list_user();
      this.write_mess();
      this.list_tabs_mess();
      this.ajax_load_mess();
      this.load_more_messages();
    },

    send_messages: function () {
      $("body").on("click", "#btn-send-messages", function (e) {
        e.preventDefault();
        var $this = $(this),
          title_message = form_popup.find('input[name="title_message"]').val(),
          content_message = form_popup
            .find('textarea[name="content_message"]')
            .val(),
          creator_message = $("#civi-add-messages").data("author-id"),
          recipient_message = $("#civi-add-messages").data("post-current");

        $.ajax({
          type: "POST",
          url: ajax_url,
          dataType: "json",
          data: {
            action: "civi_send_messages",
            title_message: title_message,
            content_message: content_message,
            creator_message: creator_message,
            recipient_message: recipient_message,
            security: theme_vars.send_message_nonce,
          },
          beforeSend: function () {
            $this.find(".btn-loading").fadeIn();
          },
          success: function (data) {
            if (data.success == true) {
              location.reload();
              form_popup.find(".civi-message-error").addClass("true");
            }
            form_popup.find(".civi-message-error").text(data.message);
            $this.find(".btn-loading").fadeOut();
          },
        });
      });
    },

    list_user: function () {
      $(".messages-dashboard .tab-info li:first-child").addClass("active");
      $("body").on("click", ".messages-dashboard .tab-info li", function (e) {
        e.preventDefault();
        var $this = $(this),
          message_id = $this.data("mess-id");

        $.ajax({
          type: "POST",
          url: ajax_url,
          dataType: "json",
          data: {
            action: "civi_messages_list_user",
            message_id: message_id,
            security: theme_vars.send_message_nonce,
          },
          beforeSend: function () {
            uxper_messages
              .find(".civi-loading-effect")
              .addClass("loading")
              .fadeIn();
          },
          success: function (data) {
            if (data.success == true) {
              $(".messages-dashboard .tab-info li").removeClass("active");
              $this.addClass("active").removeClass("unread");
              $(".messages-dashboard .mess-content").fadeOut(
                "fast",
                function () {
                  $(".messages-dashboard .mess-content").html(
                    data.mess_content_list
                  );
                  $(".messages-dashboard .mess-content").fadeIn(300);
                }
              );
              uxper_messages
                .find(".civi-loading-effect")
                .removeClass("loading")
                .fadeOut();
            }
          },
        });
      });
    },

    write_mess: function () {
      $("body").on("click", "#btn-write-message", function (e) {
        e.preventDefault();
        var $this = $(this),
          post_creator = $(".messages-dashboard .list-user.active").data(
            "mess-id"
          ),
          content_message = $(".messages-dashboard .mess-content")
            .find('textarea[name="uxper_send_mess"]')
            .val(),
          file_url = $("input.file_url").val(),
          mess_image_id = $("input.mess_image_id").val(),
          mess_image_url = $("input.mess_image_url").val();

        $.ajax({
          type: "POST",
          url: ajax_url,
          dataType: "json",
          data: {
            action: "civi_write_messages",
            post_creator: post_creator,
            content_message: content_message,
            security: theme_vars.write_message_nonce,
            file_url: file_url,
            mess_image_id: mess_image_id,
            mess_image_url: mess_image_url,
          },
          beforeSend: function () {
            $this.find(".btn-loading").fadeIn();
          },
          success: function (data) {
            if (data.success == true) {
              $(".messages-dashboard .mess-content")
                .find(".mess-content__body")
                .html(data.messages_html);
              $(".messages-dashboard .mess-content")
                .find('textarea[name="uxper_send_mess"]')
                .val("");
              $(".messages-dashboard .list-user.active").removeClass("unread");

              $(".content-write .custom-image-view").children().remove();
              $(".content-write #civi_mess_file_view").children().remove();
              $("input.file_url").val(""), $("input.mess_image_id").val("");
              $("input.mess_image_url").val("");
            } else {
              alert(data.message);
            }
            $this.find(".btn-loading").fadeOut();
          },
        });
      });
    },

    list_tabs_mess: function () {
      function tab_mess(obj) {
        $(".tab-list-mess  li").removeClass("active");
        $(obj).addClass("active");
        var id = $(obj).find("a").attr("href");
        $(".tab-info").hide();
        $(id).show();
      }

      $("body").on("click", ".tab-list-mess li", function (e) {
        var $this = $(this);
        e.preventDefault();
        tab_mess(this);
        return false;
      });
      tab_mess($(".tab-list-mess li:first-child"));
    },

    ajax_load_mess: function () {
      $("body").on("click", ".tab-mess .mess-refresh", function (e) {
        e.preventDefault();
        uxper_messages.addClass("open-nav");
        ajax_load();
      });

      $("body").on("click", ".mess-content__head .btn-delete", function (e) {
        e.preventDefault();
        var message_id = $(this).data("mess-id");
        ajax_load(message_id, "delete");
      });

      function tab_mess(obj) {
        $(".tab-list-mess  li").removeClass("active");
        $(obj).addClass("active");
        var id = $(obj).find("a").attr("href");
        $(".tab-info").hide();
        $(id).show();
      }

      function mobie_nav() {
        var nav_mess = $(".messages-dashboard .mess-list");

        $("body").on("click", ".icon-nav-mess", function (e) {
          e.preventDefault();
          nav_mess.toggleClass("open-nav");
          uxper_messages.removeClass("open-nav");
          if (nav_mess.hasClass("open-nav")) {
            nav_mess.prev().css({ visibility: "unset", opacity: "1" });
          } else {
            nav_mess.prev().css({ visibility: "hidden", opacity: "0" });
          }
        });

        nav_mess.prev().on('click', function () {
          $(this).css({ visibility: "hidden", opacity: "0" });
          nav_mess.removeClass("open-nav");
          uxper_messages.removeClass("open-nav");
        });

        if (window.matchMedia("(max-width: 576px)").matches) {
          $("body").on("click", ".messages-dashboard .list-user", function (e) {
            nav_mess.prev().css({ visibility: "hidden", opacity: "0" });
            nav_mess.removeClass("open-nav");
            uxper_messages.removeClass("open-nav");
          });
        }
      }
      mobie_nav();

      function ajax_load(message_id = "", action_click = "") {
        $.ajax({
          type: "POST",
          url: ajax_url,
          dataType: "json",
          data: {
            action: "civi_refresh_messages",
            message_id: message_id,
            action_click: action_click,
            security: theme_vars.send_message_nonce,
          },
          beforeSend: function () {
            uxper_messages
              .find(".civi-loading-effect")
              .addClass("loading")
              .fadeIn();
          },
          success: function (data) {
            if (data.success == true) {
              uxper_messages.html(data.mess_content);
              tab_mess($(".tab-list-mess li:first-child"));
              $(".messages-dashboard .tab-info li:first-child").addClass(
                "active"
              );
              $(".list-nav-dashboard .nav-item .badge").html(data.badge);
              uxper_messages
                .find(".civi-loading-effect")
                .removeClass("loading")
                .fadeOut();
              mobie_nav();
            }
          },
        });
      }
    },

    load_more_messages: function () {
      $(document).on("click", ".load-more-messages .btn-load-more", function (e) {
        e.preventDefault();
        var $this = $(this),
          $container = $this.closest(".load-more-messages"),
          $tabInfo = $this.closest(".tab-info"),
          status = $container.data("status"),
          currentPage = parseInt($tabInfo.data("paged")) || 1,
          maxPages = parseInt($tabInfo.data("max-pages")) || 1,
          nextPage = currentPage + 1,
          statusPending = status === "unread";

        if (nextPage > maxPages) {
          $container.hide();
          return;
        }

        $.ajax({
          type: "POST",
          url: ajax_url,
          dataType: "json",
          data: {
            action: "civi_load_more_messages",
            paged: nextPage,
            posts_per_page: 10,
            status_pending: statusPending,
            security: theme_vars.send_message_nonce,
          },
          beforeSend: function () {
            $this.find(".text").hide();
            $this.find(".loading").show();
            $this.prop("disabled", true);
          },
          success: function (data) {
            if (data.success === true) {
              // Append new messages to the list
              var $messageList = $tabInfo.find(".message-list-items");
              var $newItems = $(data.html).find("li");
              $messageList.append($newItems);

              // Update page counter
              $tabInfo.data("paged", nextPage);

              // Hide button if no more pages
              if (!data.has_more) {
                $container.fadeOut();
              }
            }
            $this.find(".text").show();
            $this.find(".loading").hide();
            $this.prop("disabled", false);
          },
          error: function () {
            $this.find(".text").show();
            $this.find(".loading").hide();
            $this.prop("disabled", false);
          },
        });
      });
    },
  };

  $(document).ready(function () {
    MESSAGES.init();
  });
})(jQuery);
