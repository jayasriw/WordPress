(function ($) {
  "use strict";

  var submit_form = $("#submit_jobs_form"),
    jobs_title_error = submit_form.data("titleerror"),
    jobs_des_error = submit_form.data("deserror"),
    jobs_cat_error = submit_form.data("caterror"),
    jobs_type_error = submit_form.data("typeerror"),
    jobs_skills_error = submit_form.data("skillserror");

  var ajax_url = jobportal_submit_vars.ajax_url,
    jobs_dashboard = jobportal_submit_vars.jobs_dashboard,
    custom_field_jobs = jobportal_submit_vars.custom_field_jobs;

  var aiRequest = null;
  var aiResponseWarningTimer = null;

  function parseDateFromString(dateString) {
    if (!dateString || typeof dateString !== 'string') {
      return null;
    }

    var trimmedDate = dateString.trim();
    if (!trimmedDate) {
      return null;
    }

    var dateObj = null;
    var dateParts = [];

    if (trimmedDate.indexOf('/') !== -1) {
      dateParts = trimmedDate.split('/');
      if (dateParts.length === 3) {
        var part1 = parseInt(dateParts[0], 10);
        var part2 = parseInt(dateParts[1], 10);
        var part3 = parseInt(dateParts[2], 10);

        if (part1 > 12) {
          var day = part1;
          var month = part2 - 1;
          var year = part3;
          dateObj = new Date(year, month, day);
        } else if (part2 > 12) {
          var month = part1 - 1;
          var day = part2;
          var year = part3;
          dateObj = new Date(year, month, day);
        } else {
          var dateDDMM = new Date(part3, part2 - 1, part1);
          var dateMMDD = new Date(part3, part1 - 1, part2);

          if (dateDDMM.getDate() === part1 && dateDDMM.getMonth() === part2 - 1) {
            dateObj = dateDDMM;
          } else if (dateMMDD.getDate() === part2 && dateMMDD.getMonth() === part1 - 1) {
            dateObj = dateMMDD;
          } else {
            dateObj = dateDDMM;
          }
        }
      }
    } else if (trimmedDate.indexOf('-') !== -1) {
      dateParts = trimmedDate.split('-');
      if (dateParts.length === 3) {
        if (dateParts[0].length === 4) {
          var year = parseInt(dateParts[0], 10);
          var month = parseInt(dateParts[1], 10) - 1;
          var day = parseInt(dateParts[2], 10);
          dateObj = new Date(year, month, day);
        } else {
          var day = parseInt(dateParts[0], 10);
          var month = parseInt(dateParts[1], 10) - 1;
          var year = parseInt(dateParts[2], 10);
          dateObj = new Date(year, month, day);
        }
      } else {
        dateObj = new Date(trimmedDate);
      }
    } else {
      dateObj = new Date(trimmedDate);
    }

    if (!dateObj || isNaN(dateObj.getTime())) {
      console.warn('parseDateFromString: Invalid date format:', dateString);
      return null;
    }

    return dateObj;
  }

  function calculateDaysBetweenDates(startDate, endDate) {
    if (!startDate || !endDate) {
      return 0;
    }

    var start, end;

    if (startDate instanceof Date) {
      start = new Date(startDate.getTime());
    } else {
      start = new Date(startDate);
    }

    if (endDate instanceof Date) {
      end = new Date(endDate.getTime());
    } else {
      end = new Date(endDate);
    }

    start.setHours(0, 0, 0, 0);
    end.setHours(0, 0, 0, 0);

    var timeDiff = end.getTime() - start.getTime();
    var daysDiff = Math.round(timeDiff / (1000 * 3600 * 24));

    return daysDiff;
  }

  function calculateDaysFromClosingDate(closingDateString) {
    if (!closingDateString) {
      return 0;
    }

    var closingDate = parseDateFromString(closingDateString);
    if (!closingDate) {
      console.warn('calculateDaysFromClosingDate: Could not parse date:', closingDateString);
      return 0;
    }

    var today = new Date();
    today.setHours(0, 0, 0, 0);

    var daysDiff = calculateDaysBetweenDates(today, closingDate);

    if (daysDiff < 0) {
      console.warn('calculateDaysFromClosingDate: Closing date is in the past. Days:', daysDiff);
    }

    return daysDiff < 0 ? 0 : daysDiff;
  }

  $(document).ready(function () {
    $.validator.setDefaults({ ignore: ":hidden:not(select)" });

    $.validator.addMethod("isCityName", function (value, element) {
      var apiUsername = "ductrung"; // Replace with your GeoNames username
      var isValid = false;

      if (value == "") {
        isValid = true;
        return isValid;
      } else {
        // Make a request to the GeoNames API
        var url =
          "https://secure.geonames.org/searchJSON?q=" +
          encodeURIComponent(value) +
          "&maxRows=1&username=" +
          apiUsername;

        $.ajax({
          url: url,
          method: "GET",
          dataType: "json",
          async: false,
          success: function (data) {
            // Check if the API response contains a city
            if (data.geonames && data.geonames.length > 0) {
              var type = data.geonames[0].fclName;
              if (type.indexOf("city") != -1) {
                isValid = true;
              }
            }
          },
          error: function () {
          },
        });

        return isValid;
      }
    });

    $.validator.addMethod("notPastDate", function (value, element) {
      if (!value) {
        return true;
      }

      var selectedDate = parseDateFromString(value);
      if (!selectedDate) {
        return false;
      }

      var today = new Date();
      today.setHours(0, 0, 0, 0);
      selectedDate.setHours(0, 0, 0, 0);

      return selectedDate >= today;
    }, "Please select a date that is not in the past.");

    submit_form.validate({
      ignore: [],
      rules: {
        jobs_title: {
          required: true,
        },
        jobs_categories: {
          required: true,
        },
        jobs_type: {
          required: true,
        },
        jobs_skills: {
          required: true,
        },
        jobs_des: {
          required: true,
        },
        jobs_closing_date: {
          notPastDate: true,
        },
      },
      messages: {
        jobs_title: jobs_title_error,
        jobs_des: jobs_des_error,
        jobs_categories: jobs_cat_error,
        jobs_type: jobs_type_error,
        jobs_skills: jobs_skills_error,
        jobs_closing_date: {
          notPastDate: "Closing date cannot be in the past. Please select a future date.",
        },
      },
      submitHandler: function (form) {
        var submitButtonName = $(this.submitButton).attr("name");
        var $submitButton = $(this.submitButton);
        $submitButton.prop("disabled", true).addClass("disabled");
        submit_form.off("submit");
        ajax_load(submitButtonName);
      },
      errorPlacement: function (error, element) {
        if (element.is(":radio")) {
          error.appendTo(element.parents("fieldset"));
        } else if (element.is("select")) {
          error.insertAfter(element.next(".select2"));
        } else {
          error.insertAfter(element);
        }
      },
      invalidHandler: function () {
        if ($(".error:visible").length > 0) {
          var firstError = $(".error:visible").first();
          var errorElement = firstError;

          if (firstError.prev('.select2').length > 0) {
            errorElement = firstError.prev('.select2');
          }
          if (firstError.closest('.form-group').length > 0) {
            errorElement = firstError.closest('.form-group');
          }

          $("html, body").animate(
            {
              scrollTop: errorElement.offset().top - 150,
            },
            800
          );

          var inputField = errorElement.find('input, textarea, select').first();
          if (inputField.length > 0 && inputField.is(':visible')) {
            setTimeout(function() {
              inputField.focus();
            }, 100);
          }
        }
      },
    });

    $(".btn-submit-draft").on("click", function(e) {
      e.preventDefault();
      e.stopPropagation();

      var submitButtonName = $(this).attr("name");
      var $submitButton = $(this);

      submit_form.find('.error').remove();
      submit_form.find('.error-message').remove();
      submit_form.find('.form-group').removeClass('has-error');

      $submitButton.prop("disabled", true).addClass("disabled");
      submit_form.off("submit");
      ajax_load(submitButtonName);
    });

    function setupClosingDateValidation() {
      var $closingDateInput = submit_form.find('input[name="jobs_closing_date"]');

      if ($closingDateInput.length) {
        var fpInstance = $closingDateInput.data('flatpickr');
        var inputValue = $closingDateInput.val();

        if (fpInstance) {
          var today = new Date();
          today.setHours(0, 0, 0, 0);
          fpInstance.set('minDate', today);

          if (inputValue) {
            try {
              fpInstance.setDate(inputValue, false);
            } catch (e) {
              console.warn('Error setting flatpickr date in validation:', e);
            }
          }

          var originalOnChange = fpInstance.config.onChange;
          fpInstance.config.onChange = function(selectedDates, dateStr, instance) {
            if (originalOnChange && typeof originalOnChange === 'function') {
              originalOnChange(selectedDates, dateStr, instance);
            }

            var $input = $(instance.input);
            if ($input.length && dateStr) {
              setTimeout(function() {
                $input.valid();
              }, 100);
            }
          };
        } else if (inputValue) {
          setTimeout(setupClosingDateValidation, 100);
        }

        $closingDateInput.off('change.closingDate blur.closingDate').on('change.closingDate blur.closingDate', function() {
          var $input = $(this);
          if ($input.val()) {
            setTimeout(function() {
              $input.valid();
            }, 100);
          }
        });
      }
    }

    setTimeout(function() {
      setupClosingDateValidation();
    }, 500);

    $(document).on('flatpickr:ready', function() {
      setupClosingDateValidation();
    });

    function ajax_load(submit_button) {
      var jobs_form = submit_form.find('input[name="jobs_form"]').val(),
        jobs_action = submit_form.find('input[name="jobs_action"]').val(),
        jobs_id = submit_form.find('input[name="jobs_id"]').val(),
        jobs_title = submit_form.find('input[name="jobs_title"]').val(),
        jobs_categories = submit_form
          .find('select[name="jobs_categories"]')
          .val(),
        jobs_new_categories = submit_form
          .find('input[name="jobs_new_categories"]')
          .val(),
        jobs_type = submit_form.find('select[name="jobs_type"]').val(),
        jobs_skills = submit_form.find('select[name="jobs_skills"]').val(),
        jobs_career = submit_form.find('select[name="jobs_career"]').val(),
        jobs_experience = submit_form
          .find('select[name="jobs_experience"]')
          .val(),
        jobs_qualification = submit_form
          .find('select[name="jobs_qualification"]')
          .val(),
        jobs_quantity = submit_form.find('select[name="jobs_quantity"]').val(),
        jobs_gender = submit_form.find('select[name="jobs_gender"]').val(),
        jobs_days_closing = submit_form
          .find('input[name="jobs_days_closing"]')
          .val(),
        jobs_closing_date = submit_form.find('input[name="jobs_closing_date"]').val(),
        jobs_salary_show = submit_form
          .find('select[name="jobs_salary_show"]')
          .val(),
        jobs_currency_type = submit_form
          .find('select[name="jobs_currency_type"]')
          .val(),
        jobs_salary_minimum = submit_form
          .find('input[name="jobs_salary_minimum"]')
          .val(),
        jobs_salary_maximum = submit_form
          .find('input[name="jobs_salary_maximum"]')
          .val(),
        jobs_salary_rate = submit_form
          .find('select[name="jobs_salary_rate"]')
          .val(),
        jobs_minimum_price = submit_form
          .find('input[name="jobs_minimum_price"]')
          .val(),
        jobs_maximum_price = submit_form
          .find('input[name="jobs_maximum_price"]')
          .val(),
        jobs_select_apply = submit_form
          .find('select[name="jobs_select_apply"]')
          .val(),
        jobs_apply_email = submit_form
          .find('input[name="jobs_apply_email"]')
          .val(),
        jobs_apply_external = submit_form
          .find('input[name="jobs_apply_external"]')
          .val(),
        jobs_apply_call_to = submit_form
          .find('input[name="jobs_apply_call_to"]')
          .val(),
        jobs_select_company = submit_form
          .find('select[name="jobs_select_company"]')
          .val(),
        jobs_location = submit_form.find('select[name="jobs_location"]').val(),
        jobs_new_location = submit_form
          .find('input[name="jobs_new_location"]')
          .val(),
        jobs_thumbnail_url = submit_form
          .find('input[name="jobs_thumbnail_url"]')
          .val(),
        jobs_thumbnail_id = submit_form
          .find('input[name="jobs_thumbnail_id"]')
          .val(),
        jobportal_gallery_ids = submit_form
          .find('input[name="jobportal_gallery_ids[]"]')
          .map(function () {
            return $(this).val();
          })
          .get(),
        jobs_video_url = submit_form.find('input[name="jobs_video_url"]').val(),
        jobs_map_address = submit_form
          .find('input[name="jobportal_map_address"]')
          .val(),
        jobs_map_location = submit_form
          .find('input[name="jobportal_map_location"]')
          .val(),
        jobs_latitude = submit_form.find('input[name="jobportal_latitude"]').val(),
        jobs_longtitude = submit_form
          .find('input[name="jobportal_longtitude"]')
          .val();
      var jobs_des = "";
      if ($("#wp-jobs_des-wrap textarea[name='candidate_des']").length) {
        jobs_des = submit_form.find('textarea[name="jobs_des"]').val();
      } else {
        jobs_des = tinymce.get("jobs_des").getContent();
      }

      if (jobs_closing_date) {
        var parsedClosingDate = parseDateFromString(jobs_closing_date);
        var today = new Date();
        today.setHours(0, 0, 0, 0);

        if (parsedClosingDate) {
          parsedClosingDate.setHours(0, 0, 0, 0);
          var daysDiff = calculateDaysBetweenDates(today, parsedClosingDate);

          if (daysDiff < 0) {
            jobs_days_closing = 0;
          } else {
            jobs_days_closing = daysDiff;
          }
        } else {
          jobs_days_closing = calculateDaysFromClosingDate(jobs_closing_date);
        }

        var $daysClosingInput = submit_form.find('input[name="jobs_days_closing"]');
        if ($daysClosingInput.length) {
          $daysClosingInput.val(jobs_days_closing);
        }
      }

      var additional = {};
      $("#jobs-submit-additional").each(function () {
        $.each(custom_field_jobs, function (index, value) {
          var val = $(".form-control[name=" + value.id + "]").val();
          if (value.type == "radio") {
            val = $("input[name=" + value.id + "]:checked").val();
          }
          if (value.type == "checkbox_list") {
            var arr_checkbox = [];
            $('input[name="' + value.id + '[]"]:checked').each(function () {
              arr_checkbox.push($(this).val());
            });
            val = arr_checkbox;
          }
          if (value.type == "image") {
            val = $("input#custom_image_id_" + value.id).val();
          }
          additional[value.id] = val;
        });
      });

      $.ajax({
        type: "POST",
        dataType: "json",
        url: ajax_url,
        data: {
          action: "jobs_submit_ajax",
          jobs_form: jobs_form,
          jobs_action: jobs_action,
          jobs_id: jobs_id,
          jobs_title: jobs_title,
          jobs_categories: jobs_categories,
          jobs_new_categories: jobs_new_categories,
          jobs_type: jobs_type,
          jobs_skills: jobs_skills,
          jobs_des: jobs_des,
          jobs_career: jobs_career,
          jobs_experience: jobs_experience,
          jobs_qualification: jobs_qualification,
          jobs_quantity: jobs_quantity,
          jobs_gender: jobs_gender,
          jobs_days_closing: jobs_days_closing,

          jobs_salary_show: jobs_salary_show,
          jobs_currency_type: jobs_currency_type,
          jobs_salary_minimum: jobs_salary_minimum,
          jobs_salary_maximum: jobs_salary_maximum,
          jobs_salary_rate: jobs_salary_rate,
          jobs_minimum_price: jobs_minimum_price,
          jobs_maximum_price: jobs_maximum_price,

          jobs_select_apply: jobs_select_apply,
          jobs_apply_email: jobs_apply_email,
          jobs_apply_external: jobs_apply_external,
          jobs_apply_call_to: jobs_apply_call_to,

          jobs_select_company: jobs_select_company,
          jobs_location: jobs_location,
          jobs_new_location: jobs_new_location,
          jobs_thumbnail_url: jobs_thumbnail_url,
          jobs_thumbnail_id: jobs_thumbnail_id,
          jobportal_gallery_ids: jobportal_gallery_ids,
          jobs_video_url: jobs_video_url,
          custom_field_jobs: additional,

          jobs_map_address: jobs_map_address,
          jobs_map_location: jobs_map_location,
          jobs_latitude: jobs_latitude,
          jobs_longtitude: jobs_longtitude,

          submit_button: submit_button,
        },
        beforeSend: function () {
          if (submit_button == "submit_jobs") {
            $(".btn-submit-jobs .btn-loading").fadeIn();
          } else {
            $(".btn-submit-draft .btn-loading").fadeIn();
          }
        },
        success: function (data) {
          if (submit_button == "submit_jobs") {
            $(".btn-submit-jobs .btn-loading").fadeOut();
            if (data.success === true) {
              window.location.href = jobs_dashboard;
            }
          } else {
            $(".btn-submit-draft .btn-loading").fadeOut();
          }
        },
        complete: function () {
          submit_form
            .find('button[name="' + submit_button + '"]')
            .prop("disabled", false)
            .removeClass("disabled");
          submit_form.on("submit", submit_form.validate().settings.submitHandler);
        },
        error: function (xhr, status, error) {
          submit_form
            .find('button[name="' + submit_button + '"]')
            .prop("disabled", false)
            .removeClass("disabled");
          submit_form.on("submit", submit_form.validate().settings.submitHandler);
        },
      });
    }

    $(".ai-helper").on("click", function (e) {
      e.preventDefault();
      var _this = $(this),
        popup_name = _this.attr("data-popup");
      $("#" + popup_name).addClass("open");
    });

    if ($(window).width() > 767) {
      $(".generate-content").each(function () {
        var left = $(this).find(".left"),
          right = $(this).find(".right"),
          left_height = left.outerHeight();

        right.css("height", left_height);
      });
    }

    $(".ai-generate").on("submit", function (e) {
      e.preventDefault();

      var _this = $(this),
        wrap = _this.closest(".ai-popup"),
        wrap_inner = wrap.find(".inner-popup"),
        keywords = _this.find('textarea[name="ai_prompt"]').val().trim(),
        tone = _this.find('select[name="ai_tone"] option:selected').val(),
        language = _this.find('select[name="ai_language"] option:selected').val();

      _this.find(".field-notice").removeClass("error warning");
      _this.find(".field-notice p").text("");

      var isValid = true;
      var errorMsg = "";
      var sensitiveWarningMessage = "";

      function showSensitiveWarning() {
        if (sensitiveWarningMessage) {
          _this.find(".field-notice p").text(sensitiveWarningMessage);
          _this.find(".field-notice").addClass("warning").removeClass("error");
        }
      }

      if (!keywords) {
        isValid = false;
        errorMsg += jobportal_submit_vars.enter_description + "\n";
      }

      if (keywords && keywords.length < 20) {
        isValid = false;
        errorMsg += jobportal_submit_vars.min_length + "\n";
      }

      if (keywords && keywords.length > 2000) {
        isValid = false;
        errorMsg += jobportal_submit_vars.max_length + "\n";
      }

      if (keywords) {
        var sensitiveInfo = ['ssn', 'social security', 'social security number', 'passport', 'passport number', 'phone number', 'birth date', 'date of birth', 'dob'];
        var containsSensitive = sensitiveInfo.some(info => {
          var escapedInfo = info.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
          var regex = new RegExp('\\b' + escapedInfo + '\\b', 'i');
          return regex.test(keywords);
        });

        var placeholders = ['[job title]', '[years of experience]', '[industry]', '[company name]'];
        var hasPlaceholder = placeholders.some(placeholder =>
          keywords.toLowerCase().includes(placeholder.toLowerCase())
        );

        if (hasPlaceholder) {
          isValid = false;
          errorMsg += jobportal_submit_vars.replace_placeholder + "\n";
        }

        if (containsSensitive && !hasPlaceholder) {
          sensitiveWarningMessage = jobportal_submit_vars.avoid_sensitive;
          showSensitiveWarning();
        }
      }

      if (!tone) {
        isValid = false;
        errorMsg += jobportal_submit_vars.select_tone + "\n";
      }
      if (!language) {
        isValid = false;
        errorMsg += jobportal_submit_vars.select_language + "\n";
      }

      if (!isValid) {
        _this.find(".field-notice p").text(errorMsg.trim().replace(/\n/g, " "));
        _this.find(".field-notice").addClass("error").removeClass("warning");
        return;
      }

      if (aiRequest && aiRequest.readyState !== 4) {
        aiRequest.abort();
      }

      if (aiResponseWarningTimer) {
        clearTimeout(aiResponseWarningTimer);
        aiResponseWarningTimer = null;
      }

      var aiTimeout = parseInt(jobportal_submit_vars.ai_timeout, 10);
      if (isNaN(aiTimeout) || aiTimeout <= 0) {
        aiTimeout = 120000;
      }

      var warningDelay = parseInt(jobportal_submit_vars.ai_timeout_warning, 10);
      if (isNaN(warningDelay) || warningDelay <= 0 || warningDelay >= aiTimeout) {
        warningDelay = Math.min(45000, Math.max(10000, aiTimeout - 5000));
      }

      if (!sensitiveWarningMessage) {
        aiResponseWarningTimer = setTimeout(function () {
          _this.find(".field-notice p").text(jobportal_submit_vars.timeout_warning || "The AI request is taking longer than expected...");
          _this.find(".field-notice").addClass("warning").removeClass("error");
        }, warningDelay);
      }

      aiRequest = $.ajax({
        url: ajax_url,
        type: "POST",
        data: {
          action: "auto_description_generate",
          keywords: keywords,
          tone: _this.find('select[name="ai_tone"] option:selected').text(),
          language: _this.find('select[name="ai_language"] option:selected').text(),
          security: jobportal_submit_vars.ai_nonce
        },
        timeout: aiTimeout,
        beforeSend: function () {
          _this.find(".btn-loading").fadeIn();
          wrap.find(".suggestion").text("");
          _this.find(".field-notice").removeClass("error warning");
          _this.find(".field-notice p").text("");
          showSensitiveWarning();
          wrap.find(".generate-content").removeClass("has-suggestion");
        },
        success: function (response) {
          if (aiResponseWarningTimer) {
            clearTimeout(aiResponseWarningTimer);
            aiResponseWarningTimer = null;
          }
          aiRequest = null;
          _this.find(".btn-loading").fadeOut();
          showSensitiveWarning();

          var parsedResponse;
          try {
            parsedResponse = typeof response === 'string' ? $.parseJSON(response) : response;
          } catch (e) {
            _this.find(".field-notice p").text(jobportal_submit_vars.connection_error || 'Invalid response from server.');
            _this.find(".field-notice").addClass("error");
            return;
          }

          if (!parsedResponse || typeof parsedResponse !== 'object') {
            _this.find(".field-notice p").text(jobportal_submit_vars.connection_error || 'Invalid response format.');
            _this.find(".field-notice").addClass("error");
            return;
          }

          if (parsedResponse.success && parsedResponse.message) {
            _this.find(".field-submit button .text").text(jobportal_submit_vars.regenerate);
            wrap.find(".generate-content").addClass("has-suggestion");
            wrap.find(".suggestion").html(parsedResponse.message);

            if ($(window).width() < 768) {
              var suggestionOffset = wrap.find(".suggestion").offset();
              var innerOffset = wrap_inner.offset();
              if (suggestionOffset && innerOffset) {
                $(".ai-popup .inner-popup").animate(
                  {
                    scrollTop: suggestionOffset.top - innerOffset.top - 40,
                  },
                  500
                );
              }
            }

            $(".keep-generate").off("click.ai-helper");

            $(".keep-generate").on("click.ai-helper", function (e) {
              e.preventDefault();

              var messageContent = parsedResponse.message || '';

              var textarea = $("#wp-jobs_des-wrap textarea[name='candidate_des']");
              if (textarea.length) {
                textarea.val(messageContent);
              } else {
                if (typeof tinymce !== 'undefined' && tinymce.get("jobs_des")) {
                  try {
                    tinymce.get("jobs_des").setContent(messageContent);
                  } catch (e) {
                    console.error('TinyMCE error:', e);
                    var fallbackTextarea = $("#wp-jobs_des-wrap textarea");
                    if (fallbackTextarea.length) {
                      fallbackTextarea.val(messageContent);
                    }
                  }
                } else {
                  var fallbackTextarea = $("#wp-jobs_des-wrap textarea");
                  if (fallbackTextarea.length) {
                    fallbackTextarea.val(messageContent);
                  }
                }
              }

              wrap.find(".generate-content").removeClass("has-suggestion");
              var form = $(".ai-generate")[0];
              if (form) {
                form.reset();
              }
              _this.closest(".popup").removeClass("open");
              _this.find(".field-submit button .text").text(jobportal_submit_vars.generate);
            });
          } else {
            var errorMessage = parsedResponse.message || jobportal_submit_vars.connection_error || 'An error occurred. Please try again.';
            _this.find(".field-notice p").text(errorMessage);
            _this.find(".field-notice").addClass("error");
          }
        },
        error: function (xhr, status, error) {
          if (aiResponseWarningTimer) {
            clearTimeout(aiResponseWarningTimer);
            aiResponseWarningTimer = null;
          }

          if (status === "abort") {
            return;
          }

          _this.find(".btn-loading").fadeOut();

          if (status === "timeout") {
            _this.find(".field-notice p").text(jobportal_submit_vars.timeout_error || jobportal_submit_vars.connection_error);
          } else {
            _this.find(".field-notice p").text(jobportal_submit_vars.connection_error);
          }

          _this.find(".field-notice").addClass("error").removeClass("warning");
          aiRequest = null;
        }
      });
    });

  });
})(jQuery);
