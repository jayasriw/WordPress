(function ($) {
  "use strict";
  $(document).ready(function () {
    var ajax_url = civi_template_vars.ajax_url,
      apply_saved = civi_template_vars.apply_saved,
      not_file = civi_template_vars.not_file,
      $form_popup = $(".form-popup-apply");

    $form_popup.each(function () {
      var $form = $(this);
      var form_id = $form.attr("id");
      if (!form_id) return;

      var $btn_submit = $form.find(".btn-submit-apply-jobs");
      var $btn_popup = $(".civi-button-apply." + form_id);
      var apply_form = $("#" + form_id);

      if ($btn_submit.length === 0 || apply_form.length === 0) return;

      $btn_submit.on("click", function (e) {
        e.preventDefault();
        var $this = $(this),
          email = apply_form.find('input[name="apply_email"]').val(),
          message = apply_form.find('textarea[name="apply_message"]').val(),
          phone = apply_form.find('input[name="apply_phone"]').val(),
          jobs_id = $btn_popup.length > 0 ? $btn_popup.data("jobs_id") : $this.data("jobs_id"),
          cv_url = apply_form.find('input[name="jobs_cv_url"]').val(),
          type_apply = apply_form.find('input[name="type_apply"]').val(),
          candidate_categories = apply_form
            .find('select[name="candidate_categories"]')
            .val(),
          candidate_dob = apply_form.find('input[name="candidate_dob"]').val(),
          candidate_current_position = apply_form
            .find('input[name="candidate_current_position"]')
            .val(),
          candidate_age = apply_form.find('select[name="candidate_age"]').val(),
          candidate_gender = apply_form
            .find('select[name="candidate_gender"]')
            .val(),
          candidate_languages = apply_form
            .find('select[name="candidate_languages"]')
            .val(),
          candidate_qualification = apply_form
            .find('select[name="candidate_qualification"]')
            .val(),
          candidate_yoe = apply_form.find('select[name="candidate_yoe"]').val();

        if (!jobs_id) {
          apply_form.find(".message_error").text(not_file || "Invalid job ID");
          return;
        }

        // Validate Date of Birth - cannot be in the future
        if (candidate_dob) {
          var selectedDate = new Date(candidate_dob);
          var today = new Date();
          today.setHours(23, 59, 59, 999);
          selectedDate.setHours(0, 0, 0, 0);

          if (selectedDate > today) {
            apply_form.find(".message_error").removeClass("true").text("Date of birth cannot be in the future. Please select a past or today's date.");
            return;
          }
        }

        $.ajax({
          type: "POST",
          url: ajax_url,
          dataType: "json",
          data: {
            action: "jobs_add_to_apply",
            jobs_id: jobs_id,
            email: email,
            phone: phone,
            message: message,
            cv_url: cv_url,
            type_apply: type_apply,
            security: theme_vars.job_apply_nonce,

            candidate_current_position: candidate_current_position,
            candidate_categories: candidate_categories,
            candidate_dob: candidate_dob,
            candidate_age: candidate_age,
            candidate_gender: candidate_gender,
            candidate_languages: candidate_languages,
            candidate_qualification: candidate_qualification,
            candidate_yoe: candidate_yoe,
          },
          beforeSend: function () {
            $this.find(".btn-loading").fadeIn();
          },
          success: function (data) {
            var $messageError = apply_form.find(".message_error");
            if (!$messageError.length) {
              $this.find(".btn-loading").fadeOut();
              return;
            }

            if (!data || typeof data !== 'object') {
              $messageError.text(not_file || "Invalid response from server");
              $this.find(".btn-loading").fadeOut();
              return;
            }

            if (data.success === true) {
              $messageError.addClass("true");
              var successMessage = data.message || apply_saved || "Success";
              $messageError.text(successMessage);
              $(".civi-button-apply[data-jobs_id =" + jobs_id + "]").html(
                apply_saved || "Applied"
              );
              if (data.redirect) {
                window.location.href = data.redirect;
              } else {
                location.reload();
              }
            } else {
              var errorMessage = not_file || "An error occurred";
              if (data.data && typeof data.data === 'object' && data.data.message) {
                errorMessage = data.data.message;
              } else if (data.message) {
                errorMessage = data.message;
              }
              $messageError.removeClass("true").text(errorMessage);
            }
            $this.find(".btn-loading").fadeOut();
          },
          error: function (xhr, status, error) {
            var $messageError = apply_form.find(".message_error");
            var msg = not_file || "An error occurred";

            if (!$messageError.length) {
              $this.find(".btn-loading").fadeOut();
              return;
            }

            if (xhr && xhr.responseJSON && typeof xhr.responseJSON === 'object') {
              if (xhr.responseJSON.data && typeof xhr.responseJSON.data === 'object' && xhr.responseJSON.data.message) {
                msg = xhr.responseJSON.data.message;
              } else if (xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
              }
            } else if (xhr && xhr.responseText) {
              try {
                var parsed = JSON.parse(xhr.responseText);
                if (parsed && typeof parsed === 'object') {
                  if (parsed.data && typeof parsed.data === 'object' && parsed.data.message) {
                    msg = parsed.data.message;
                  } else if (parsed.message) {
                    msg = parsed.message;
                  }
                }
              } catch (e) {
                if (xhr.responseText && xhr.responseText.trim().length > 0 && !xhr.responseText.trim().startsWith('<')) {
                  msg = xhr.responseText;
                }
              }
            }

            $messageError.removeClass("true").text(msg);
            $this.find(".btn-loading").fadeOut();
          },
        });
      });
    });
  });
})(jQuery);
