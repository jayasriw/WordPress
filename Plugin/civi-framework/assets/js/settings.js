(function ($) {
  "use strict";

  if (typeof civi_settings_vars === 'undefined') {
    console.error('civi_settings_vars is not defined');
    return;
  }

  var ajax_url = civi_settings_vars.ajax_url,
    civi_site_url = civi_settings_vars.site_url,
    verify_id_before_title = civi_settings_vars.verify_id_before_title || '',
    verify_id_before_type = civi_settings_vars.verify_id_before_type || '',
    verify_id_before_file_size = civi_settings_vars.verify_id_before_file_size || '',
    verify_id_before_text = civi_settings_vars.verify_id_before_text || '',
    verify_id_before_url = civi_settings_vars.verify_id_before_url || '',
    verify_id_before_upload_nonce =
      civi_settings_vars.verify_id_before_upload_nonce || '',
    verify_id_after_title = civi_settings_vars.verify_id_after_title || '',
    verify_id_after_type = civi_settings_vars.verify_id_after_type || '',
    verify_id_after_file_size = civi_settings_vars.verify_id_after_file_size || '',
    verify_id_after_text = civi_settings_vars.verify_id_after_text || '',
    verify_id_after_url = civi_settings_vars.verify_id_after_url || '',
    verify_id_after_upload_nonce =
      civi_settings_vars.verify_id_after_upload_nonce || '',
    verify_id_selfie_title = civi_settings_vars.verify_id_selfie_title || '',
    verify_id_selfie_type = civi_settings_vars.verify_id_selfie_type || '',
    verify_id_selfie_file_size = civi_settings_vars.verify_id_selfie_file_size || '',
    verify_id_selfie_text = civi_settings_vars.verify_id_selfie_text || '',
    verify_id_selfie_url = civi_settings_vars.verify_id_selfie_url || '',
    verify_id_selfie_upload_nonce =
      civi_settings_vars.verify_id_selfie_upload_nonce || '';

  $(document).ready(function () {
    $(".form-settings").validate({
      ignore: ":hidden", // any children of hidden desc are ignored
      errorElement: "span", // wrap error elements in span not label
      rules: {
        user_firstname: {
          required: true,
        },
        user_lastname: {
          required: true,
        },
        user_email: {
          required: true,
        },
        user_mobile_number: {
          required: true,
        },
      },
      messages: {
        user_firstname: "",
        user_lastname: "",
        user_email: "",
        user_mobile_number: "",
      },
    });

    $("#civi_update_profile").on("click", function (e) {
      e.preventDefault();
      var $this = $(this);
      var $form = $this.parents("form");
      if ($form.valid()) {
        $.ajax({
          type: "POST",
          url: ajax_url,
          dataType: "json",
          data: {
            action: "civi_update_profile_ajax",
            user_firstname: $("#user_firstname").val(),
            user_lastname: $("#user_lastname").val(),
            user_des: $("#user_des").val(),
            user_email: $("#user_email").val(),
            phone_code: $(".prefix-code").val(),
            author_mobile_number: $("#author_mobile_number").val(),
            author_avatar_image_id: $("#author_avatar_image_id").val(),
            author_avatar_image_url: $("#author_avatar_image_url").val(),
            user_image_url: $("#author_verify_id_before_image_url").val(),
            user_image_id: $("#author_verify_id_before_image_id").val(),
            civi_security_update_profile: $(
              "#civi_security_update_profile"
            ).val(),
          },
          beforeSend: function () {
            $this.find(".btn-loading").fadeIn();
          },
          success: function (response) {
            $this.find(".btn-loading").fadeOut();
            if (response.success) {
              location.reload();
            }
          },
          error: function () {
            $this.find(".btn-loading").fadeOut();
          },
        });
      }
    });

    $(".block-search.search-input").on("click", ".icon-clear", function (e) {
      e.preventDefault();
      $(this).closest(".search-input").find(".input-search").val("");
      $(this).closest(".search-input").removeClass("has-clear");
    });

    $("#civi_change_pass").on("click", function (e) {
      e.preventDefault();
      var securitypassword, oldpass, newpass, confirmpass;
      var $this = $(this);
      var $form = $this.parents("form");
      if ($this.prop('disabled') || $this.hasClass('processing')) {
        return false;
      }
      oldpass = $("#oldpass").val();
      newpass = $("#newpass").val();
      confirmpass = $("#confirmpass").val();
      securitypassword = $("#civi_security_change_password").val();

      $.ajax({
        type: "POST",
        dataType: "json",
        url: civi_settings_vars.ajax_url,
        data: {
          action: "civi_change_password_ajax",
          oldpass: oldpass,
          newpass: newpass,
          confirmpass: confirmpass,
          civi_security_change_password: securitypassword,
        },
        beforeSend: function () {
          $this.prop('disabled', true).addClass('processing');
          $this.find(".btn-loading").fadeIn();
        },
        success: function (response) {
          var $message = $form.find(".message");

          if (response.success) {
            $message.removeClass("error").addClass("success");
            $message.html(response.message);
            setTimeout(function () {
              var logoutText = (civi_settings_vars.logout_countdown_text || 'You will be logged out in {countdown} seconds for security reasons.')
                .replace('{countdown}', '<span class="countdown-timer">3</span>');
              var $logoutText = $('<div class="logout-countdown" style="margin: 10px 0; color: #666;">' + logoutText + '</div>');
              $message.after($logoutText);
              $('body').css('pointer-events', 'none');
              setTimeout(function () {
                window.location.href = civi_settings_vars.site_url;
              }, 3000);
              var timeLeft = 3;
              var countdownInterval = setInterval(function () {
                timeLeft--;
                $('.countdown-timer').text(timeLeft);
                if (timeLeft <= 0) {
                  clearInterval(countdownInterval);
                }
              }, 1000);

            }, 1500);
          } else {
            $message.removeClass("success").addClass("error");
            $message.html(response.message);

            $this.prop('disabled', false).removeClass('processing');
          }

          $this.find(".btn-loading").fadeOut();
        },
        error: function () {
          $this.find(".btn-loading").fadeOut();
          var $message = $form.find(".message");
          $message.removeClass("success").addClass("error");
          $message.html(civi_settings_vars.ajax_error_text || 'An error occurred, please try again!');
          $this.prop('disabled', false).removeClass('processing');
        },
      });
    });

    var civi_verify_id_before = function () {
      var uploader_verify_id_before = new plupload.Uploader({
        browse_button: "civi_select_verify_id_before",
        file_data_name: "civi_verify_id_before_upload_file",
        drop_element: "civi_verify_id_before_view",
        container: "civi_verify_id_before_container",
        url: verify_id_before_url,
        filters: {
          mime_types: [
            {
              title: verify_id_before_title,
              extensions: verify_id_before_type,
            },
          ],
          max_file_size: verify_id_before_file_size,
          prevent_duplicates: true,
        },
      });
      uploader_verify_id_before.init();

      uploader_verify_id_before.bind("UploadProgress", function (up, file) {
        $("#civi_add_verify_id_before .la-upload").hide();
        document.getElementById("civi_select_verify_id_before").innerHTML =
          '<span><i class="fal fa-spinner fa-spin large"></i></span>';
      });

      uploader_verify_id_before.bind("FilesAdded", function (up, files) {
        up.refresh();
        uploader_verify_id_before.start();
      });
      uploader_verify_id_before.bind("Error", function (up, err) {
        document.getElementById("civi_verify_id_before_errors").innerHTML +=
          "Error #" + err.code + ": " + err.message + "<br/>";
      });

      var $image_id = $("#civi_verify_id_before_view").data("image-id");
      var $image_url = $("#civi_verify_id_before_view").data("image-url");
      if ($image_id && $image_url) {
        var $html =
          '<figure class="media-thumb media-thumb-wrap">' +
          '<img src="' +
          $image_url +
          '">' +
          '<div class="media-item-actions">' +
          '<a class="icon icon-verify-id-before-delete" data-attachment-id="' +
          $image_id +
          '" href="#" ><i class="far fa-trash-alt large"></i></a>' +
          '<span style="display: none;" class="icon icon-loader"><i class="fal fa-spinner fa-spin large"></i></span>' +
          "</div>" +
          "</figure>";
        $("#civi_verify_id_before_view").html($html);
        $("#civi_add_verify_id_before").hide();
      }
      uploader_verify_id_before.bind(
        "FileUploaded",
        function (up, file, ajax_response) {
          document.getElementById("civi_drop_verify_id_before").style.display =
            "none";
          var response = $.parseJSON(ajax_response.response);
          if (response.success) {
            $("input.verify_id_before_url").val(response.full_image);
            $("input.verify_id_before_id").val(response.attachment_id);
            var $html =
              '<figure class="media-thumb media-thumb-wrap">' +
              '<img src="' +
              response.full_image +
              '">' +
              '<div class="media-item-actions">' +
              '<a class="icon icon-verify-id-before-delete" data-attachment-id="' +
              response.attachment_id +
              '" href="#" ><i class="far fa-trash-alt large"></i></a>' +
              '<span style="display: none;" class="icon icon-loader"><i class="fal fa-spinner fa-spin large"></i></span>' +
              "</div>" +
              "</figure>";
            $("#civi_verify_id_before_view").html($html);
            civi_verify_id_before_delete();
            $("#civi_add_verify_id_before .la-upload").hide();
            $("#verify_id_before_url-error").hide();
            if ($(".form-dashboard").hasClass("candidate-profile-form")) {
              $("#candidate-profile-form").find(".point-mark").on('change', );
            }
          }

          //Company
          var $company_verify_id_before_url = $(
            "#submit_company_form input.verify_id_before_url"
          );
          var $about = $(".about-company-dashboard");
          $about
            .find(".img-company")
            .html(
              '<img src="' + $company_verify_id_before_url.val() + '" alt="">'
            );
        }
      );
    };
    civi_verify_id_before();

    var civi_verify_id_before_delete = function ($type) {
      $("body").on("click", ".icon-verify-id-before-delete", function (e) {
        e.preventDefault();
        var $this = $(this),
          icon_delete = $this,
          verify_id_before = $this
            .closest("#civi_verify_id_before_view")
            .find(".media-thumb-wrap"),
          attachment_id = $this.data("attachment-id"),
          $drop = $("#civi_drop_verify_id_before");

        icon_delete.html('<i class="fal fa-spinner fa-spin large"></i>');

        $.ajax({
          type: "post",
          url: ajax_url,
          dataType: "json",
          data: {
            action: "civi_verify_id_before_remove_ajax",
            attachment_id: attachment_id,
            type: $type,
            removeNonce: verify_id_before_upload_nonce,
          },
          success: function (response) {
            if (response.success) {
              verify_id_before.remove();
              verify_id_before.hide();

              $("#verify_id_before_url-error").show();
              $("#civi_add_verify_id_before").show();
            }
            icon_delete.html('<i class="fal fa-spinner fa-spin large"></i>');
            $drop.css("display", "block");
            $("#civi_add_verify_id_before .la-upload").show();
            $("#civi_select_verify_id_before").html(verify_id_before_text);
            $("input.verify_id_before_url").val("");
            $("input.verify_id_before_id").val("");
            if ($(".form-dashboard").hasClass("candidate-profile-form")) {
              $("#candidate-profile-form").find(".point-mark").on('change', );
            }

            //Company
            var $about = $(".about-company-dashboard");
            $about.find(".img-company").html('<i class="far fa-camera"></i>');
          },
          error: function () {
            icon_delete.html('<i class="far fa-trash-alt large"></i>');
          },
        });
      });
    };
    civi_verify_id_before_delete();

    var civi_verify_id_after = function () {
      var uploader_verify_id_after = new plupload.Uploader({
        browse_button: "civi_select_verify_id_after",
        file_data_name: "civi_verify_id_after_upload_file",
        drop_element: "civi_verify_id_after_view",
        container: "civi_verify_id_after_container",
        url: verify_id_after_url,
        filters: {
          mime_types: [
            {
              title: verify_id_after_title,
              extensions: verify_id_after_type,
            },
          ],
          max_file_size: verify_id_after_file_size,
          prevent_duplicates: true,
        },
      });
      uploader_verify_id_after.init();

      uploader_verify_id_after.bind("UploadProgress", function (up, file) {
        $("#civi_add_verify_id_after .la-upload").hide();
        document.getElementById("civi_select_verify_id_after").innerHTML =
          '<span><i class="fal fa-spinner fa-spin large"></i></span>';
      });

      uploader_verify_id_after.bind("FilesAdded", function (up, files) {
        up.refresh();
        uploader_verify_id_after.start();
      });
      uploader_verify_id_after.bind("Error", function (up, err) {
        document.getElementById("civi_verify_id_after_errors").innerHTML +=
          "Error #" + err.code + ": " + err.message + "<br/>";
      });

      var $image_id = $("#civi_verify_id_after_view").data("image-id");
      var $image_url = $("#civi_verify_id_after_view").data("image-url");
      if ($image_id && $image_url) {
        var $html =
          '<figure class="media-thumb media-thumb-wrap">' +
          '<img src="' +
          $image_url +
          '">' +
          '<div class="media-item-actions">' +
          '<a class="icon icon-verify-id-after-delete" data-attachment-id="' +
          $image_id +
          '" href="#" ><i class="far fa-trash-alt large"></i></a>' +
          '<span style="display: none;" class="icon icon-loader"><i class="fal fa-spinner fa-spin large"></i></span>' +
          "</div>" +
          "</figure>";
        $("#civi_verify_id_after_view").html($html);
        $("#civi_add_verify_id_after").hide();
      }
      uploader_verify_id_after.bind(
        "FileUploaded",
        function (up, file, ajax_response) {
          document.getElementById("civi_drop_verify_id_after").style.display =
            "none";
          var response = $.parseJSON(ajax_response.response);
          if (response.success) {
            $("input.verify_id_after_url").val(response.full_image);
            $("input.verify_id_after_id").val(response.attachment_id);
            var $html =
              '<figure class="media-thumb media-thumb-wrap">' +
              '<img src="' +
              response.full_image +
              '">' +
              '<div class="media-item-actions">' +
              '<a class="icon icon-verify-id-after-delete" data-attachment-id="' +
              response.attachment_id +
              '" href="#" ><i class="far fa-trash-alt large"></i></a>' +
              '<span style="display: none;" class="icon icon-loader"><i class="fal fa-spinner fa-spin large"></i></span>' +
              "</div>" +
              "</figure>";
            $("#civi_verify_id_after_view").html($html);
            civi_verify_id_after_delete();
            $("#civi_add_verify_id_after .la-upload").hide();
            $("#verify_id_after_url-error").hide();
            if ($(".form-dashboard").hasClass("candidate-profile-form")) {
              $("#candidate-profile-form").find(".point-mark").on('change', );
            }
          }

          //Company
          var $company_verify_id_after_url = $(
            "#submit_company_form input.verify_id_after_url"
          );
          var $about = $(".about-company-dashboard");
          $about
            .find(".img-company")
            .html(
              '<img src="' + $company_verify_id_after_url.val() + '" alt="">'
            );
        }
      );
    };
    civi_verify_id_after();

    var civi_verify_id_after_delete = function ($type) {
      $("body").on("click", ".icon-verify-id-after-delete", function (e) {
        e.preventDefault();
        var $this = $(this),
          icon_delete = $this,
          verify_id_after = $this
            .closest("#civi_verify_id_after_view")
            .find(".media-thumb-wrap"),
          attachment_id = $this.data("attachment-id"),
          $drop = $("#civi_drop_verify_id_after");

        icon_delete.html('<i class="fal fa-spinner fa-spin large"></i>');

        $.ajax({
          type: "post",
          url: ajax_url,
          dataType: "json",
          data: {
            action: "civi_verify_id_after_remove_ajax",
            attachment_id: attachment_id,
            type: $type,
            removeNonce: verify_id_after_upload_nonce,
          },
          success: function (response) {
            if (response.success) {
              verify_id_after.remove();
              verify_id_after.hide();

              $("#verify_id_after_url-error").show();
              $("#civi_add_verify_id_after").show();
            }
            icon_delete.html('<i class="fal fa-spinner fa-spin large"></i>');
            $drop.css("display", "block");
            $("#civi_add_verify_id_after .la-upload").show();
            $("#civi_select_verify_id_after").html(verify_id_after_text);
            $("input.verify_id_after_url").val("");
            $("input.verify_id_after_id").val("");
            if ($(".form-dashboard").hasClass("candidate-profile-form")) {
              $("#candidate-profile-form").find(".point-mark").on('change', );
            }

            //Company
            var $about = $(".about-company-dashboard");
            $about.find(".img-company").html('<i class="far fa-camera"></i>');
          },
          error: function () {
            icon_delete.html('<i class="far fa-trash-alt large"></i>');
          },
        });
      });
    };
    civi_verify_id_after_delete();

    var civi_verify_id_selfie = function () {
      var uploader_verify_id_selfie = new plupload.Uploader({
        browse_button: "civi_select_verify_id_selfie",
        file_data_name: "civi_verify_id_selfie_upload_file",
        drop_element: "civi_verify_id_selfie_view",
        container: "civi_verify_id_selfie_container",
        url: verify_id_selfie_url,
        filters: {
          mime_types: [
            {
              title: verify_id_selfie_title,
              extensions: verify_id_selfie_type,
            },
          ],
          max_file_size: verify_id_selfie_file_size,
          prevent_duplicates: true,
        },
      });
      uploader_verify_id_selfie.init();

      uploader_verify_id_selfie.bind("UploadProgress", function (up, file) {
        $("#civi_add_verify_id_selfie .la-upload").hide();
        document.getElementById("civi_select_verify_id_selfie").innerHTML =
          '<span><i class="fal fa-spinner fa-spin large"></i></span>';
      });

      uploader_verify_id_selfie.bind("FilesAdded", function (up, files) {
        up.refresh();
        uploader_verify_id_selfie.start();
      });
      uploader_verify_id_selfie.bind("Error", function (up, err) {
        document.getElementById("civi_verify_id_selfie_errors").innerHTML +=
          "Error #" + err.code + ": " + err.message + "<br/>";
      });

      var $image_id = $("#civi_verify_id_selfie_view").data("image-id");
      var $image_url = $("#civi_verify_id_selfie_view").data("image-url");
      if ($image_id && $image_url) {
        var $html =
          '<figure class="media-thumb media-thumb-wrap">' +
          '<img src="' +
          $image_url +
          '">' +
          '<div class="media-item-actions">' +
          '<a class="icon icon-verify-id-selfie-delete" data-attachment-id="' +
          $image_id +
          '" href="#" ><i class="far fa-trash-alt large"></i></a>' +
          '<span style="display: none;" class="icon icon-loader"><i class="fal fa-spinner fa-spin large"></i></span>' +
          "</div>" +
          "</figure>";
        $("#civi_verify_id_selfie_view").html($html);
        $("#civi_add_verify_id_selfie").hide();
      }
      uploader_verify_id_selfie.bind(
        "FileUploaded",
        function (up, file, ajax_response) {
          document.getElementById("civi_drop_verify_id_selfie").style.display =
            "none";
          var response = $.parseJSON(ajax_response.response);
          if (response.success) {
            $("input.verify_id_selfie_url").val(response.full_image);
            $("input.verify_id_selfie_id").val(response.attachment_id);
            var $html =
              '<figure class="media-thumb media-thumb-wrap">' +
              '<img src="' +
              response.full_image +
              '">' +
              '<div class="media-item-actions">' +
              '<a class="icon icon-verify-id-selfie-delete" data-attachment-id="' +
              response.attachment_id +
              '" href="#" ><i class="far fa-trash-alt large"></i></a>' +
              '<span style="display: none;" class="icon icon-loader"><i class="fal fa-spinner fa-spin large"></i></span>' +
              "</div>" +
              "</figure>";
            $("#civi_verify_id_selfie_view").html($html);
            civi_verify_id_selfie_delete();
            $("#civi_add_verify_id_selfie .la-upload").hide();
            $("#verify_id_selfie_url-error").hide();
            if ($(".form-dashboard").hasClass("candidate-profile-form")) {
              $("#candidate-profile-form").find(".point-mark").on('change', );
            }
          }

          //Company
          var $company_verify_id_selfie_url = $(
            "#submit_company_form input.verify_id_selfie_url"
          );
          var $about = $(".about-company-dashboard");
          $about
            .find(".img-company")
            .html(
              '<img src="' + $company_verify_id_selfie_url.val() + '" alt="">'
            );
        }
      );
    };
    civi_verify_id_selfie();

    var civi_verify_id_selfie_delete = function ($type) {
      $("body").on("click", ".icon-verify-id-selfie-delete", function (e) {
        e.preventDefault();
        var $this = $(this),
          icon_delete = $this,
          verify_id_selfie = $this
            .closest("#civi_verify_id_selfie_view")
            .find(".media-thumb-wrap"),
          attachment_id = $this.data("attachment-id"),
          $drop = $("#civi_drop_verify_id_selfie");

        icon_delete.html('<i class="fal fa-spinner fa-spin large"></i>');

        $.ajax({
          type: "post",
          url: ajax_url,
          dataType: "json",
          data: {
            action: "civi_verify_id_selfie_remove_ajax",
            attachment_id: attachment_id,
            type: $type,
            removeNonce: verify_id_selfie_upload_nonce,
          },
          success: function (response) {
            if (response.success) {
              verify_id_selfie.remove();
              verify_id_selfie.hide();

              $("#verify_id_selfie_url-error").show();
              $("#civi_add_verify_id_selfie").show();
            }
            icon_delete.html('<i class="fal fa-spinner fa-spin large"></i>');
            $drop.css("display", "block");
            $("#civi_add_verify_id_selfie .la-upload").show();
            $("#civi_select_verify_id_selfie").html(verify_id_selfie_text);
            $("input.verify_id_selfie_url").val("");
            $("input.verify_id_selfie_id").val("");
            if ($(".form-dashboard").hasClass("candidate-profile-form")) {
              $("#candidate-profile-form").find(".point-mark").on('change', );
            }

            //Company
            var $about = $(".about-company-dashboard");
            $about.find(".img-company").html('<i class="far fa-camera"></i>');
          },
          error: function () {
            icon_delete.html('<i class="far fa-trash-alt large"></i>');
          },
        });
      });
    };
    civi_verify_id_selfie_delete();
  });
})(jQuery);
