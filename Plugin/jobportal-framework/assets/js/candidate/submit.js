"use strict";

jQuery(document).ready(function ($) {
  /**
   *  Declarations go here
   */

  var submit_form = $("#candidate-profile-form");

  if (submit_form.length === 0) {
    return;
  }

  var profile_strength = $(".candidate-profile-strength");
  var profile_dashboard = $(".candidate-profile-dashboard");
  var ajax_url = jobportal_candidate_vars.ajax_url;
  var text_present = jobportal_candidate_vars.text_present;
  var custom_field_candidate = jobportal_candidate_vars.custom_field_candidate;
  var date_format = jobportal_template_vars.date_format;

  var tabTemplate = profile_dashboard.find(".tab-item.repeater");
  if (tabTemplate.length > 0) {
    $.each(tabTemplate, function () {
      var val = $(this).find("a").attr("href").replace("#tab-", "");
      var $index = 1,
        tabID = submit_form.find("#tab-" + val),
        btn_more = tabID.find(".btn-more.profile-fields"),
        item = tabID.find(".jobportal-candidate-wrapper .row").length,
        template = $(tabID.find("template").html().trim());

      if (item == 0) {
        template.find(".group-title h6 span").text($index);
        template
          .find(".project-upload")
          .attr("id", "project-uploader_" + $index);
        template
          .find(".project-uploaded-list")
          .attr("id", "project-uploaded-list_" + $index);
        template
          .find(".errors-log")
          .attr("id", "jobportal_project_errors_log_" + $index);
        template
          .find(".uploaded-container")
          .attr("id", "uploaded-container_" + $index);
        template.find(".uploaded-main").attr("id", "uploader-main_" + $index);
        template.insertBefore(btn_more);
      }
    });
  }

  var $rowActive = submit_form.find(
    ".jobportal-candidate-wrapper > .row:first-child"
  );
  $rowActive.find(".group-title i").removeClass("delete-group");
  $rowActive.find("input").addClass("point-mark");
  $rowActive.find("textarea").addClass("point-mark");
  $rowActive.find(".project-uploaded-list input").removeClass("point-mark");
  $rowActive.find("#project-uploaded-list_1").addClass("point-mark");

  var $profile = $("#candidate-profile");

  // Profile Strength Calculation Logic
  var isMarkPointRunning = false;
  var markPointTimeout = null;
  var lastSavedProfileStrength = -1;
  var saveStrengthTimeout = null;
  var isSavingStrength = false;
  var pendingStrengthValue = null;

  function debouncedMarkPoint() {
    if (markPointTimeout) {
      clearTimeout(markPointTimeout);
    }

    markPointTimeout = setTimeout(function () {
      if (!isMarkPointRunning) {
        markPoint();
      }
    }, 100);
  }

  function debouncedSaveProfileStrength() {
    if (saveStrengthTimeout) {
      clearTimeout(saveStrengthTimeout);
    }

    saveStrengthTimeout = setTimeout(function () {
      saveProfileStrength();
    }, 500);
  }

  function saveProfileStrength() {
    if (isSavingStrength || pendingStrengthValue === null) {
      return;
    }

    var strengthToSave = pendingStrengthValue;
    pendingStrengthValue = null;
    isSavingStrength = true;

    $.ajax({
      type: "POST",
      url: jobportal_candidate_vars.ajax_url,
      data: {
        action: "save_candidate_profile_strength",
        nonce: jobportal_candidate_vars.save_strength_nonce,
        profile_strength: strengthToSave,
      },
      success: function (response) {
        if (response && response.success) {
          lastSavedProfileStrength = strengthToSave;
        } else {
          pendingStrengthValue = strengthToSave;
          setTimeout(function () {
            saveProfileStrength();
          }, 1000);
        }
      },
      error: function (error) {
        pendingStrengthValue = strengthToSave;
        setTimeout(function () {
          saveProfileStrength();
        }, 2000);
      },
      complete: function () {
        isSavingStrength = false;
        if (pendingStrengthValue !== null && pendingStrengthValue !== lastSavedProfileStrength) {
          debouncedSaveProfileStrength();
        }
      },
    });
  }

  function markPoint() {
    if (isMarkPointRunning) {
      return;
    }

    isMarkPointRunning = true;

    try {
      var $fieldPoint = submit_form.find(".point-mark");

      // Mark active fields
      $fieldPoint.each(function () {
        var $field = $(this);
        var fieldValue = $field.val();

        if (
          fieldValue !== "" &&
          fieldValue !== null &&
          fieldValue !== undefined
        ) {
          if (!$field.hasClass("point-active")) {
            $field.addClass("point-active");
          }
        } else {
          if ($field.hasClass("point-active")) {
            $field.removeClass("point-active");
          }
        }
      });

      // Handle Select2 fields
      $(".jobportal-select2").each(function () {
        var $select = $(this);
        var selectedValue = $select.val();

        if (selectedValue && selectedValue.length > 0 && selectedValue !== "") {
          if (!$select.hasClass("point-active")) {
            $select.addClass("point-active");
          }
        } else {
          if ($select.hasClass("point-active")) {
            $select.removeClass("point-active");
          }
        }
      });

      // Handle Select2 Multiple fields
      $(".select2-multiple").each(function () {
        var $select = $(this);
        var $hiddenSelect = $select.find("select");
        var selectedValues = $hiddenSelect.val();

        if (selectedValues && selectedValues.length > 0) {
          if (!$select.hasClass("point-active")) {
            $select.addClass("point-active");
          }
        } else {
          if ($select.hasClass("point-active")) {
            $select.removeClass("point-active");
          }
        }
      });

      // Special fields that are counted separately - exclude from .point-mark counting
      var specialFieldSelectors = [
        "#search-location",
        "#wp-candidate_des-wrap",
        'input[name="jobportal_map_address"]',
        'input[name="jobportal_map_location"]',
        'input[name="jobportal_latitude"]',
        'input[name="jobportal_longtitude"]',
      ];
      var excludeSelector = specialFieldSelectors.join(", ");

      // Only count visible fields (not hidden by CSS or parent containers)
      // Exclude special fields that are counted separately
      var visiblePointMarks = submit_form.find(".point-mark").not(excludeSelector).filter(function() {
        var $el = $(this);
        return $el.is(':visible') || $el.closest('.form-group, .field-input').is(':visible');
      });
      var pointActive = visiblePointMarks.filter(".point-active").length;
      var pointAll = visiblePointMarks.length;

      // Media Gallery
      var mediaGalleryContainer = submit_form.find("#jobportal_gallery_thumbs");
      if (mediaGalleryContainer.length > 0) {
        var mediaGallery = mediaGalleryContainer.find(".media-thumb-wrap").length;
        if (mediaGallery > 0) {
          pointActive = pointActive + 1;
        }
        pointAll = pointAll + 1;
      }

      // Avatar field
      var avatarField = submit_form.find('input[name="author_avatar_image_url"]');
      if (avatarField.length > 0) {
        var avatar = avatarField.val();
        if (avatar && avatar !== "" && avatar !== null && avatar !== undefined) {
          pointActive = pointActive + 1;
        }
        pointAll = pointAll + 1;
      }

      // Cover image field
      var coverImageField = submit_form.find('input[name="candidate_cover_image_url"]');
      if (coverImageField.length > 0) {
        var coverImageValue = coverImageField.val();
        if (coverImageValue && coverImageValue !== "" && coverImageValue !== null && coverImageValue !== undefined) {
          pointActive = pointActive + 1;
        }
        pointAll = pointAll + 1;
      }

      // CV files list (multiple files upload)
      var cvFilesList = submit_form.find("#jobportal_cv_files_list");
      if (cvFilesList.length > 0) {
        var cvCount = cvFilesList.find(".cv-file-item").length;
        if (cvCount > 0) {
          pointActive = pointActive + 1;
        }
        pointAll = pointAll + 1;
      }

      // Project Upload
      var projectUploaded = submit_form.find("#project-uploaded-list_1");
      if (projectUploaded.length > 0) {
        if (projectUploaded.find(".media-thumb").length > 0) {
          if (!projectUploaded.hasClass("point-active")) {
            projectUploaded.addClass("point-active");
          }
        } else {
          if (projectUploaded.hasClass("point-active")) {
            projectUploaded.removeClass("point-active");
          }
        }
      }

      // Search Location
      var searchLocation = submit_form.find("#search-location");
      if (searchLocation.length > 0) {
        if (!searchLocation.hasClass("point-mark")) {
          searchLocation.addClass("point-mark");
        }
        if (searchLocation.val() && searchLocation.val() !== "") {
          if (!searchLocation.hasClass("point-active")) {
            searchLocation.addClass("point-active");
          }
          pointActive = pointActive + 1;
        } else {
          if (searchLocation.hasClass("point-active")) {
            searchLocation.removeClass("point-active");
          }
        }
        pointAll = pointAll + 1;
      }

      // TinyMCE Editor with textarea fallback
      var editorField = submit_form.find("#wp-candidate_des-wrap");
      if (editorField.length > 0) {
        if (!editorField.hasClass("point-mark")) {
          editorField.addClass("point-mark");
        }
        var hasEditorContent = false;
        var textareaContent = submit_form.find('textarea[name="candidate_des"]').val() || '';

        if (typeof tinyMCE !== "undefined" && tinyMCE.get("candidate_des")) {
          var editorContent = tinyMCE.get("candidate_des").getContent({ format: "text" }).trim();
          hasEditorContent = editorContent.length > 0 || textareaContent.trim().length > 0;
        } else {
          hasEditorContent = textareaContent.trim().length > 0;
        }

        if (hasEditorContent) {
          if (!editorField.hasClass("point-active")) {
            editorField.addClass("point-active");
          }
          pointActive = pointActive + 1;
        } else {
          if (editorField.hasClass("point-active")) {
            editorField.removeClass("point-active");
          }
        }
        pointAll = pointAll + 1;
      }

      var percent = pointAll > 0 ? Math.round((pointActive / pointAll) * 100) : 0;

      // Update UI
      $profile.find(".profile-strength").css("--pct", percent);
      $profile.find(".profile-strength h1 span:first-child").text(percent);
      $(".profile-strength.left-sidebar").css("--pct", percent);
      $(".profile-strength.left-sidebar").find(".title").find("span:nth-child(2)").text(percent);
      $(".nav-profile-strength .profile-strength .title span:nth-child(2)").text(percent);
      submit_form.attr("data-pointactive", pointActive);
      submit_form.attr("data-pointall", pointAll);

      var strengthField = submit_form.find('input[name="candidate_profile_strength"]');
      if (strengthField.length > 0) {
        strengthField.val(percent);
      }

      checkTabCompletion();

      if (percent !== lastSavedProfileStrength && typeof jobportal_candidate_vars !== "undefined") {
        pendingStrengthValue = percent;
        debouncedSaveProfileStrength();
      }
    } catch (error) {
    } finally {
      isMarkPointRunning = false;
    }
  }

  // Tab Completion Logic
  function checkTabCompletion() {
    var textTab = [
      "info",
      "awards",
      "projects",
      "skills",
      "experience",
      "education",
      "availability",
    ];

    $.each(textTab, function (index, val) {
      var tabID = submit_form.find("#tab-" + val);
      var checkStrength = profile_strength.find("#profile-check-" + val);
      var textHasCheck = checkStrength.data("has-check");
      var textNotCheck = checkStrength.data("not-check");
      var textCheck = checkStrength.find("span");

      var isTabComplete = false;

      if (val === "info") {
        var allFields = tabID.find(".point-mark");
        var requiredFields = allFields.filter(function () {
          var fieldName = $(this).attr("name") || $(this).attr("id") || "";
          return (
            !fieldName.includes("linkedin") &&
            !fieldName.includes("facebook") &&
            !fieldName.includes("instagram")
          );
        });

        var requiredActiveFields = requiredFields.filter(".point-active");

        var avatarField = submit_form.find('input[name="author_avatar_image_url"]');
        var hasAvatar = false;
        if (avatarField.length > 0) {
          var avatar = avatarField.val();
          hasAvatar = avatar && avatar !== "" && avatar !== null && avatar !== undefined;
        }

        var searchLocation = submit_form.find("#search-location");
        var hasLocation = false;
        if (searchLocation.length > 0) {
          var locationValue = searchLocation.val();
          hasLocation = locationValue && locationValue !== "";
        }

        var hasEditorContent = false;
        if (typeof tinyMCE !== "undefined" && tinyMCE.get("candidate_des")) {
          var editorContent = tinyMCE.get("candidate_des").getContent({ format: "text" }).trim();
          hasEditorContent = editorContent.length > 0;
        } else {
          var textareaContent = submit_form.find('textarea[name="candidate_des"]').val();
          hasEditorContent = textareaContent && textareaContent.trim().length > 0;
        }

        var allRequiredFieldsComplete =
          requiredFields.length > 0 &&
          requiredFields.length === requiredActiveFields.length;

        isTabComplete =
          allRequiredFieldsComplete &&
          hasAvatar &&
          hasLocation &&
          hasEditorContent;
      } else if (val === "skills") {
        var skillsSelect = tabID.find('select[name="candidate_skills[]"]');
        var skillsValue = skillsSelect.val();
        isTabComplete = skillsValue && skillsValue.length > 0;
      } else if (val === "education") {
        var rows = tabID.find(".jobportal-candidate-wrapper .row");
        var hasCompleteRow = false;

        rows.each(function (i) {
          var row = $(this);
          var titleField = row.find('input[name="candidate_education_title[]"]');
          var fromField = row.find('input[name="candidate_education_from[]"]');
          var descField = row.find('textarea[name="candidate_education_description[]"]');
          var presentCheckbox = row.find('input[name="candidate_education_check[]"]');
          var levelField = row.find('input[name="candidate_education_level[]"]');

          var titleValue = titleField.length > 0 ? titleField.val() : "";
          var fromValue = fromField.length > 0 ? fromField.val() : "";
          var descValue = descField.length > 0 ? descField.val() : "";
          var levelValue = levelField.length > 0 ? levelField.val() : "";
          var isPresent = presentCheckbox.length > 0 ? presentCheckbox.is(":checked") : false;

          var isRowComplete =
            titleValue !== "" &&
            descValue !== "" &&
            levelValue !== "" &&
            (fromValue !== "" || isPresent);

          if (isRowComplete) {
            hasCompleteRow = true;
          }
        });

        isTabComplete = hasCompleteRow;
      } else if (val === "experience") {
        var rows = tabID.find(".jobportal-candidate-wrapper .row");
        var hasCompleteRow = false;

        rows.each(function (i) {
          var row = $(this);
          var titleField = row.find('input[name="candidate_experience_job[]"]');
          var fromField = row.find('input[name="candidate_experience_from[]"]');
          var descField = row.find('textarea[name="candidate_experience_description[]"]');
          var presentCheckbox = row.find('input[name="candidate_experience_check[]"]');
          var companyField = row.find('input[name="candidate_experience_company[]"]');

          var titleValue = titleField.length > 0 ? titleField.val() : "";
          var fromValue = fromField.length > 0 ? fromField.val() : "";
          var descValue = descField.length > 0 ? descField.val() : "";
          var companyValue = companyField.length > 0 ? companyField.val() : "";
          var isPresent = presentCheckbox.length > 0 ? presentCheckbox.is(":checked") : false;

          var isRowComplete =
            titleValue !== "" &&
            descValue !== "" &&
            companyValue !== "" &&
            (fromValue !== "" || isPresent);

          if (isRowComplete) {
            hasCompleteRow = true;
          }
        });

        isTabComplete = hasCompleteRow;
      } else if (val === "projects") {
        var rows = tabID.find(".jobportal-candidate-wrapper .row");
        var hasCompleteProject = false;

        rows.each(function (i) {
          var row = $(this);
          var titleField = row.find('input[name="candidate_project_title[]"]');
          var linkField = row.find('input[name="candidate_project_link[]"]');
          var descField = row.find('textarea[name="candidate_project_description[]"]');
          var uploadList = row.find(".project-uploaded-list");

          var titleValue = titleField.length > 0 ? titleField.val() : "";
          var linkValue = linkField.length > 0 ? linkField.val() : "";
          var descValue = descField.length > 0 ? descField.val() : "";
          var hasImage = uploadList.find(".media-thumb").length > 0;

          var isProjectComplete =
            titleValue !== "" &&
            linkValue !== "" &&
            descValue !== "" &&
            hasImage;

          if (isProjectComplete) {
            hasCompleteProject = true;
          }
        });

        isTabComplete = hasCompleteProject;
      } else if (val === "awards") {
        var rows = tabID.find(".jobportal-candidate-wrapper .row");
        var hasCompleteAward = false;

        rows.each(function (i) {
          var row = $(this);
          var titleField = row.find('input[name="candidate_award_title[]"]');
          var dateField = row.find('input[name="candidate_award_date[]"]');
          var descField = row.find('textarea[name="candidate_award_description[]"]');

          var titleValue = titleField.length > 0 ? titleField.val() : "";
          var dateValue = dateField.length > 0 ? dateField.val() : "";
          var descValue = descField.length > 0 ? descField.val() : "";

          var isAwardComplete =
            titleValue !== "" && dateValue !== "" && descValue !== "";

          if (isAwardComplete) {
            hasCompleteAward = true;
          }
        });

        isTabComplete = hasCompleteAward;
      } else if (val === "availability") {
        isTabComplete = true;
      }

      if (isTabComplete) {
        checkStrength.addClass("check");
        textCheck.text(textHasCheck);
      } else {
        checkStrength.removeClass("check");
        textCheck.text(textNotCheck);
      }
    });
  }

  $(document).ready(function () {
    $(document).off("change keyup", ".point-mark");
    $(document).off("change", ".jobportal-select2");

    $(document).on("change keyup", ".point-mark", function () {
      debouncedMarkPoint();
    });

    $(document).on("change", ".jobportal-select2", function () {
      debouncedMarkPoint();
    });

    $(document).on(
      "click",
      '.avatar-delete, .remove-avatar, [data-action="remove"]',
      function () {
        var $this = $(this);
        if (
          $this.closest(
            ".avatar-container, .candidate-fields-avatar, #jobportal_avatar_container"
          ).length > 0
        ) {
          setTimeout(function () {
            debouncedMarkPoint();
          }, 100);
        }
      }
    );

    $(document).on(
      "change",
      'input[name="author_avatar_image_url"], input[name="author_avatar_image_id"]',
      function () {
        debouncedMarkPoint();
      }
    );

    $(document).on(
      "change",
      'input[name="jobportal_map_location"], input[name="jobportal_map_address"]',
      function () {}
    );

    // Delay initial markPoint to ensure all fields (TinyMCE, Select2, etc.) are fully initialized
    setTimeout(function() {
      debouncedMarkPoint();
    }, 800);
  });

  if (typeof tinyMCE !== "undefined") {
    if ($("#wp-candidate_des-wrap").hasClass("tmce-active")) {
      tinyMCE.get("candidate_des").on("change", function () {
        var value = tinyMCE
          .get("candidate_des")
          .getContent({ format: "text" })
          .trim().length;
        $("#wp-candidate_des-wrap").addClass("point-mark");
        if (value > 0) {
          $("#wp-candidate_des-wrap").addClass("point-active");
        } else {
          $("#wp-candidate_des-wrap").removeClass("point-active");
        }
        markPoint();
      });
    }
  }

  submit_form.closest("#wrapper").css("overflow", "inherit");

  function ajax_submit() {
    var candidate_social_data = {};
    $(".candidate-social-input").each(function () {
      var fieldName = $(this).attr("name");
      var fieldValue = $(this).val();
      candidate_social_data[fieldName] = fieldValue;
    });

    var candidate_id = submit_form.find('input[name="candidate_id"]').val(),
      candidate_first_name = submit_form
        .find('input[name="candidate_first_name"]')
        .val(),
      candidate_last_name = submit_form
        .find('input[name="candidate_last_name"]')
        .val(),
      candidate_email = submit_form.find('input[name="candidate_email"]').val(),
      candidate_phone = submit_form.find('input[name="candidate_phone"]').val(),
      candidate_phone_code = submit_form
        .find('select[name="prefix_code"]')
        .val(),
      candidate_current_position = submit_form
        .find('input[name="candidate_current_position"]')
        .val(),
      candidate_categories = submit_form
        .find('select[name="candidate_categories"]')
        .val(),
      candidate_dob = submit_form.find('input[name="candidate_dob"]').val(),
      candidate_age = submit_form.find('select[name="candidate_age"]').val(),
      candidate_gender = submit_form
        .find('select[name="candidate_gender"]')
        .val(),
      candidate_languages = submit_form
        .find('select[name="candidate_languages"]')
        .val(),
      candidate_qualification = submit_form
        .find('select[name="candidate_qualification"]')
        .val(),
      candidate_yoe = submit_form.find('select[name="candidate_yoe"]').val(),
      candidate_salary_type = submit_form
        .find('select[name="candidate_salary_type"]')
        .val(),
      candidate_offer_salary = submit_form
        .find('input[name="candidate_offer_salary"]')
        .val(),
      candidate_currency_type = submit_form
        .find('select[name="candidate_currency_type"]')
        .val(),
      candidate_education_title = submit_form
        .find('input[name="candidate_education_title[]"]')
        .map(function () {
          return $(this).val();
        })
        .get(),
      candidate_education_level = submit_form
        .find('input[name="candidate_education_level[]"]')
        .map(function () {
          return $(this).val();
        })
        .get(),
      candidate_education_from = submit_form
        .find('input[name="candidate_education_from[]"]')
        .map(function () {
          return $(this).val();
        })
        .get(),
      candidate_education_to = submit_form
        .find('input[name="candidate_education_to[]"]')
        .map(function () {
          return $(this).val();
        })
        .get(),
      candidate_education_description = submit_form
        .find('textarea[name="candidate_education_description[]"]')
        .map(function () {
          return $(this).val();
        })
        .get(),
      candidate_experience_job = submit_form
        .find('input[name="candidate_experience_job[]"]')
        .map(function () {
          return $(this).val();
        })
        .get(),
      candidate_experience_company = submit_form
        .find('input[name="candidate_experience_company[]"]')
        .map(function () {
          return $(this).val();
        })
        .get(),
      candidate_experience_from = submit_form
        .find('input[name="candidate_experience_from[]"]')
        .map(function () {
          return $(this).val();
        })
        .get(),
      candidate_experience_to = submit_form
        .find('input[name="candidate_experience_to[]"]')
        .map(function () {
          return $(this).val();
        })
        .get(),
      candidate_experience_description = submit_form
        .find('textarea[name="candidate_experience_description[]"]')
        .map(function () {
          return $(this).val();
        })
        .get(),
      candidate_skills = submit_form
        .find('select[name="candidate_skills[]"]')
        .val(),
      candidate_project_title = submit_form
        .find('input[name="candidate_project_title[]"]')
        .map(function () {
          return $(this).val();
        })
        .get(),
      candidate_project_link = submit_form
        .find('input[name="candidate_project_link[]"]')
        .map(function () {
          return $(this).val();
        })
        .get(),
      candidate_project_description = submit_form
        .find('textarea[name="candidate_project_description[]"]')
        .map(function () {
          return $(this).val();
        })
        .get(),
      candidate_project_image_id = submit_form
        .find('input[name="candidate_project_image_id[]"]')
        .map(function () {
          return $(this).val();
        })
        .get(),
      candidate_project_image_url = submit_form
        .find('input[name="candidate_project_image_url[]"]')
        .map(function () {
          return $(this).val();
        })
        .get(),
      candidate_award_title = submit_form
        .find('input[name="candidate_award_title[]"]')
        .map(function () {
          return $(this).val();
        })
        .get(),
      candidate_award_date = submit_form
        .find('input[name="candidate_award_date[]"]')
        .map(function () {
          return $(this).val();
        })
        .get(),
      candidate_award_description = submit_form
        .find('textarea[name="candidate_award_description[]"]')
        .map(function () {
          return $(this).val();
        })
        .get(),
      candidate_cover_image_id = submit_form
        .find('input[name="candidate_cover_image_id"]')
        .val(),
      candidate_cover_image_url = submit_form
        .find('input[name="candidate_cover_image_url"]')
        .val(),
      author_avatar_image_id = submit_form
        .find('input[name="author_avatar_image_id"]')
        .val(),
      author_avatar_image_url = submit_form
        .find('input[name="author_avatar_image_url"]')
        .val(),
      candidate_video_url = submit_form
        .find('input[name="candidate_video_url"]')
        .val(),
      candidate_resume = submit_form
        .find("#jobportal_drop_cv")
        .attr("data-attachment-id"),
      candidate_twitter = submit_form
        .find('input[name="candidate_twitter"]')
        .val(),
      candidate_linkedin = submit_form
        .find('input[name="candidate_linkedin"]')
        .val(),
      candidate_facebook = submit_form
        .find('input[name="candidate_facebook"]')
        .val(),
      candidate_instagram = submit_form
        .find('input[name="candidate_instagram"]')
        .val(),
      social_data = social_data,
      candidate_social_name = submit_form
        .find('input[name="candidate_social_name[]"]')
        .map(function () {
          return $(this).val();
        })
        .get(),
      candidate_social_url = submit_form
        .find('input[name="candidate_social_url[]"]')
        .map(function () {
          return $(this).val();
        })
        .get(),
      candidate_location = submit_form
        .find('select[name="candidate_location"]')
        .val(),
      candidate_map_address = submit_form
        .find('input[name="jobportal_map_address"]')
        .val(),
      candidate_map_location = submit_form
        .find('input[name="jobportal_map_location"]')
        .val(),
      candidate_latitude = submit_form
        .find('input[name="jobportal_latitude"]')
        .val(),
      candidate_longtitude = submit_form
        .find('input[name="jobportal_longtitude"]')
        .val(),
      jobportal_gallery_ids = submit_form
        .find('input[name="jobportal_gallery_ids[]"]')
        .map(function () {
          return $(this).val();
        })
        .get(),
      candidate_profile_strength = submit_form
        .find('input[name="candidate_profile_strength"]')
        .val();

    var fpDobInstance = submit_form
      .find('input[name="candidate_dob"]')
      .data("flatpickr");
    if (
      fpDobInstance &&
      fpDobInstance.selectedDates &&
      fpDobInstance.selectedDates.length > 0
    ) {
      var fpDate = fpDobInstance.selectedDates[0];
      var fpDateStr =
        fpDate.getFullYear() +
        "-" +
        String(fpDate.getMonth() + 1).padStart(2, "0") +
        "-" +
        String(fpDate.getDate()).padStart(2, "0");
      candidate_dob = fpDateStr;
    }

    var candidate_des;
    if ($('#wp-candidate_des-wrap textarea[name="candidate_des"]').length) {
      candidate_des = submit_form.find('textarea[name="candidate_des"]').val();
    } else {
      candidate_des =
        typeof tinymce != "undefined" &&
        tinymce?.get("candidate_des")?.getContent();
    }

    var additional = {};
    submit_form.find(".block-from").each(function () {
      $.each(custom_field_candidate, function (index, value) {
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
        if (value.type == "select") {
          val = $("select[name=" + value.id + "]").val();
        }
        if (value.type == "url") {
          val = $("input[name=" + value.id + "]").val();
        }
        if (value.type == "textarea") {
          val = $("textarea[name=" + value.id + "]").val();
        }
        if (value.type == "text") {
          val = $("input[name=" + value.id + "]").val();
        }
        additional[value.id] = val;
      });
    });

    $.ajax({
      dataType: "json",
      url: ajax_url,
      type: "POST",
      data: {
        action: "candidate_submit_ajax",
        jobportal_security_candidate_submit: $("#jobportal_security_candidate_submit").val(),
        candidate_id: candidate_id,

        candidate_first_name: candidate_first_name,
        candidate_last_name: candidate_last_name,
        candidate_email: candidate_email,
        candidate_phone: candidate_phone,
        candidate_phone_code: candidate_phone_code,
        candidate_current_position: candidate_current_position,
        candidate_categories: candidate_categories,
        candidate_des: candidate_des,
        candidate_dob: candidate_dob,
        candidate_age: candidate_age,
        candidate_gender: candidate_gender,
        candidate_languages: candidate_languages,
        candidate_qualification: candidate_qualification,
        candidate_yoe: candidate_yoe,
        candidate_offer_salary: candidate_offer_salary,
        candidate_salary_type: candidate_salary_type,
        candidate_currency_type: candidate_currency_type,

        candidate_education_title: candidate_education_title,
        candidate_education_level: candidate_education_level,
        candidate_education_from: candidate_education_from,
        candidate_education_to: candidate_education_to,
        candidate_education_description: candidate_education_description,

        candidate_experience_job: candidate_experience_job,
        candidate_experience_company: candidate_experience_company,
        candidate_experience_from: candidate_experience_from,
        candidate_experience_to: candidate_experience_to,
        candidate_experience_description: candidate_experience_description,

        candidate_skills: candidate_skills,

        candidate_project_title: candidate_project_title,
        candidate_project_link: candidate_project_link,
        candidate_project_description: candidate_project_description,
        candidate_project_image_id: candidate_project_image_id,
        candidate_project_image_url: candidate_project_image_url,

        candidate_award_title: candidate_award_title,
        candidate_award_date: candidate_award_date,
        candidate_award_description: candidate_award_description,

        candidate_cover_image_id: candidate_cover_image_id,
        candidate_cover_image_url: candidate_cover_image_url,
        author_avatar_image_id: author_avatar_image_id,
        author_avatar_image_url: author_avatar_image_url,
        jobportal_gallery_ids: jobportal_gallery_ids,
        candidate_video_url: candidate_video_url,

        candidate_resume: candidate_resume,

        candidate_twitter: candidate_twitter,
        candidate_linkedin: candidate_linkedin,
        candidate_facebook: candidate_facebook,
        candidate_instagram: candidate_instagram,
        candidate_social_data: candidate_social_data,
        candidate_social_name: candidate_social_name,
        candidate_social_url: candidate_social_url,

        candidate_location: candidate_location,
        jobportal_map_address: candidate_map_address,
        jobportal_map_location: candidate_map_location,
        jobportal_latitude: candidate_latitude,
        jobportal_longtitude: candidate_longtitude,

        candidate_profile_strength: candidate_profile_strength,

        custom_field_candidate: additional,
      },
      beforeSend: function () {
        $(".btn-update-profile .btn-loading").fadeIn();
      },
      success: function (data) {
        $(".btn-update-profile .btn-loading").fadeOut();
        if (data.success === true) {
          window.location.reload();
        }
      },
    });
  }

  Date.prototype.addDays = function (days) {
    this.setDate(this.getDate() + parseInt(days));
    return this;
  };

  function present_education_to(row) {
    return function () {
      if ($(this).is(":checked")) {
        row.find(".present-to .datepicker").remove();
        if (row.find('.present-to input[type="text"]').length < 1) {
          row
            .find(".present-to")
            .append(
              '<input class="text-present" disabled type="text" name="candidate_education_to[]" value="' +
                text_present +
                '">'
            );
        }
      } else {
        row.find(".present-to .text-present").remove();
        if (row.find('.present-to input[type="text"]').length < 1) {
          row
            .find(".present-to")
            .append(
              '<input type="text" class="datepicker" placeholder="' +
                date_format +
                '" name="candidate_education_to[]">'
            );
        }
      }
    };
  }

  function present_experience_to(row) {
    return function () {
      if ($(this).is(":checked")) {
        row.find(".present-to .datepicker").remove();
        if (row.find('.present-to input[type="text"]').length < 1) {
          row
            .find(".present-to")
            .append(
              '<input class="text-present" disabled type="text" name="candidate_experience_to[]" value="' +
                text_present +
                '">'
            );
        }
      } else {
        row.find(".present-to .text-present").remove();
        if (row.find('.present-to input[type="text"]').length < 1) {
          row
            .find(".present-to")
            .append(
              '<input type="text" class="datepicker" placeholder="' +
                date_format +
                '" name="candidate_experience_to[]" value="">'
            );
        }
      }
    };
  }

  function removeAllChecked() {
    var checkedBoxes = $('input[name="candidate_cover_image_id"]:checked');
    checkedBoxes.prop("checked", false);
  }

  // Make Validator to check array Input fields
  // https://github.com/jquery-validation/jquery-validation/issues/1226
  //
  $.validator.prototype.checkForm = function () {
    this.prepareForm();
    for (
      var i = 0, elements = (this.currentElements = this.elements());
      elements[i];
      i++
    ) {
      if (
        this.findByName(elements[i].name).length != undefined &&
        this.findByName(elements[i].name).length > 1
      ) {
        for (
          var cnt = 0;
          cnt < this.findByName(elements[i].name).length;
          cnt++
        ) {
          this.check(this.findByName(elements[i].name)[cnt]);
        }
      } else {
        this.check(elements[i]);
      }
    }
    return this.valid();
  };

  function setAttrAndProp(input, attr = {}, prop = {}) {
    if (!(input instanceof jQuery)) {
      return false;
    }

    $.each(attr, function (attrName, attrVal) {
      input.attr(attrName, attrVal);
    });

    $.each(prop, function (propName, propVal) {
      input.attr(propName, propVal);
    });
  }

  function findRelatedInputDate(input, nameToFind) {
    if (!(input instanceof jQuery)) {
      return false;
    }
    var relatedInput = input
      .closest(".row")
      .find(`input[name="${nameToFind}"]`);
    return relatedInput;
  }

  function setRelatedInputDateTo(input) {
    if (!(input instanceof jQuery)) {
      return false;
    }

    var nameWithFrom = input.attr("name");
    var nameWithTo = nameWithFrom.replace("from", "to");

    var relatedInput = findRelatedInputDate(input, nameWithTo);

    if (relatedInput == false) {
      return false;
    }

    var fromDate = new Date(input.val());
    if (fromDate !== "") {
      var minDate = fromDate.addDays(1).toISOString().split("T")[0];
    }

    var attrs = {
      min: minDate,
    };

    var props = {
      required: true,
    };

    setAttrAndProp(relatedInput, attrs, props);
  }

  function setRelatedInputDateFrom(input) {
    if (!(input instanceof jQuery)) {
      return false;
    }

    var nameWithTo = input.attr("name");
    var nameWithFrom = nameWithTo.replace("to", "from");

    var relatedInput = findRelatedInputDate(input, nameWithFrom);

    if (relatedInput == false) {
      return false;
    }

    var toDate = new Date(input.val());
    var maxDate = toDate.addDays(-1).toISOString().split("T")[0];

    var attrs = {
      max: maxDate,
    };

    var props = {
      required: true,
    };

    setAttrAndProp(relatedInput, attrs, props);
  }

  function validateSingleInput(input) {
    if (!(input instanceof jQuery)) {
      return false;
    }

    submit_form.validate().element(input);

    if (input.hasClass("error")) {
      input.focus();
      return false;
    }

    return true;
  }

  function ajaxDeleteAttachment(clickedEl, $type, $none) {
    var $this = $(clickedEl),
      icon_delete = $this,
      thumbnail = $this.closest(".media-thumb-wrap"),
      candidate_id = $this.data("candidate-id"),
      attachment_id = $this.data("attachment-id");

    icon_delete.html('<i class="fal fa-spinner fa-spin large"></i>');

    $.ajax({
      type: "post",
      url: ajax_url,
      dataType: "json",
      data: {
        action: "remove_candidate_attachment_ajax",
        candidate_id: candidate_id,
        attachment_id: attachment_id,
        type: $type,
        removeNonce: $none,
      },
      success: function (response) {
        if (response.success) {
          thumbnail.remove();
        }
        icon_delete.html('<i class="fal fa-spinner fa-spin large"></i>');
      },
      error: function () {
        icon_delete.html('<i class="far fa-trash-alt large"></i>');
      },
    });
  }

  submit_form.on("click", "i.delete-group", function () {
    var groupToRemove = $(this).closest(".group-title").closest(".row");
    var groupSiblings = groupToRemove.siblings(".row");
    var template = groupToRemove.siblings("template");

    groupToRemove.remove();

    $.each(groupSiblings, function renumberGroups(index) {
      $(this)
        .find(".group-title h6 span")
        .text(index + 1);
    });

    // Update total number of groups
    template.data("size", groupSiblings.size());
  });

  submit_form.on("click", ".group-title", function () {
    if (!$(this).hasClass("up")) {
      $(this).addClass("up");
    } else {
      $(this).removeClass("up");
    }
  });

  $.validator.setDefaults({ ignore: ":hidden:not(select)" });

  $.validator.addMethod(
    "notFutureDate",
    function (value, element) {
      if (!value) {
        return true;
      }

      var selectedDate = new Date(value);
      if (isNaN(selectedDate.getTime())) {
        return false;
      }

      var today = new Date();
      today.setHours(23, 59, 59, 999);
      selectedDate.setHours(0, 0, 0, 0);

      return selectedDate <= today;
    },
    "Date of birth cannot be in the future. Please select a past or today's date."
  );

  submit_form.validate({
    ignore: [],
    rules: {
      candidate_dob: {
        notFutureDate: true,
      },
    },
    messages: {
      candidate_dob: {
        notFutureDate:
          "Date of birth cannot be in the future. Please select a past or today's date.",
      },
    },

    submitHandler: function (form) {
      ajax_submit();
    },
    errorPlacement: function (error, element) {
      if (element.attr("name") === "jobportal_gallery_ids") {
        error.insertAfter("#jobportal_gallery_errors");
      } else if (element.is(":radio")) {
        error.appendTo(element.parents("fieldset"));
      } else if (element.is("select")) {
        error.insertAfter(element.next(".select2"));
      } else if (element.is(":checkbox")) {
        error.appendTo(element.parents(".checkbox-group"));
      } else if (element.hasClass("custom-file-input")) {
        error.insertAfter(element.parents(".custom-file"));
      } else {
        error.insertAfter(element);
      }
    },
    invalidHandler: function () {
      if ($(".error:visible").length > 0) {
        var firstError = $(".error:visible").first();
        var errorElement = firstError;

        if (firstError.prev(".select2").length > 0) {
          errorElement = firstError.prev(".select2");
        }
        if (firstError.closest(".form-group").length > 0) {
          errorElement = firstError.closest(".form-group");
        }

        $("html, body").animate(
          {
            scrollTop: errorElement.offset().top - 150,
          },
          800
        );

        var inputField = errorElement.find("input, textarea, select").first();
        if (inputField.length > 0 && inputField.is(":visible")) {
          setTimeout(function () {
            inputField.focus();
          }, 100);
        }
      }
    },
  });

  function setupCandidateDobValidation() {
    var $dobInput = submit_form.find('input[name="candidate_dob"]');

    if (!$dobInput.length) {
      return;
    }

    var fpInstance = $dobInput.data("flatpickr");
    var maxDate = $dobInput.attr("max");

    if (fpInstance && maxDate) {
      var today = new Date();
      today.setHours(23, 59, 59, 999);
      fpInstance.set("maxDate", today);

      var inputValue = $dobInput.attr("value");
      if (
        inputValue &&
        (!fpInstance.selectedDates || fpInstance.selectedDates.length === 0)
      ) {
        try {
          var dateObj = new Date(inputValue);
          if (!isNaN(dateObj.getTime())) {
            fpInstance.setDate(dateObj, true);
          }
        } catch (e) {}
      }

      var originalOnChange = fpInstance.config.onChange;
      fpInstance.config.onChange = function (selectedDates, dateStr, instance) {
        if (originalOnChange && typeof originalOnChange === "function") {
          originalOnChange(selectedDates, dateStr, instance);
        }

        var $input = $(instance.input);
        if ($input.length && dateStr) {
          setTimeout(function () {
            $input.valid();
          }, 100);
        }
      };
    } else if (maxDate && !fpInstance) {
      setTimeout(setupCandidateDobValidation, 100);
    }

    $dobInput
      .off("change.candidateDob blur.candidateDob")
      .on("change.candidateDob blur.candidateDob", function () {
        var $input = $(this);
        if ($input.val()) {
          setTimeout(function () {
            $input.valid();
          }, 100);
        }
      });
  }

  setTimeout(function () {
    setupCandidateDobValidation();
  }, 500);

  $(document).on("flatpickr:ready", function () {
    setupCandidateDobValidation();
  });

  submit_form.on("blur", 'input[type="date"][name*="from"]', function () {
    var isValid = validateSingleInput($(this));

    if (!isValid) {
      return false;
    }

    setRelatedInputDateTo($(this));
  });

  submit_form.on("blur", 'input[type="date"][name*="to"]', function () {
    var isValid = validateSingleInput($(this));

    if (!isValid) {
      return false;
    }
  });

  function formatDateForDisplay(date) {
    const options = { month: "long", day: "numeric", year: "numeric" };
    return new Date(date).toLocaleDateString("en-US", options);
  }

  $(".btn-more.profile-fields").on("click", function () {
    var template = $(this).siblings("template");
    var html = $(template.html().trim());
    var index = parseInt(template.data("size")) + 1;

    html.find(".group-title h6 span").text(index);
    html.find(".project-upload").attr("id", "project-uploader_" + index);
    html
      .find(".project-uploaded-list")
      .attr("id", "project-uploaded-list_" + index);
    html.find(".errors-log").attr("id", "jobportal_project_errors_log_" + index);
    html.find(".uploaded-container").attr("id", "uploaded-container_" + index);
    html.find(".uploaded-main").attr("id", "uploader-main_" + index);

    html.insertBefore($(this));
    tab_projects_each(index);

    template.data("size", index);

    $("#tab-education .row").each(function () {
      var row = $(this);
      row
        .find('input[type="checkbox"]')
        .on("change", present_education_to(row));
    });

    $("#tab-experience .row").each(function () {
      var row = $(this);
      row
        .find('input[type="checkbox"]')
        .on("change", present_experience_to(row));
    });
  });

  $('input[name="candidate_cover_image_id"]').on('click', function () {
    var is_checked = false;
    if ($(this).is(":checked")) {
      is_checked = true;
    }

    removeAllChecked();

    $(this).prop("checked", is_checked);
  });

  function tab_projects_each($index) {
    $("#tab-projects .row").each(function ($index) {
      var $index = $index + 1;

      var upload_nonce = $("#tab-projects").data("nonce");
      var cv_title = $("#tab-projects").data("title");
      var cv_type = $("#tab-projects").data("type");
      var cv_size = $("#tab-projects").data("file-size");
      var uploader = "uploader_" + $index;

      uploader = new plupload.Uploader({
        browse_button: "uploader-main_" + $index,
        file_data_name: "candidate_upload_file",
        container: "uploaded-container_" + $index,
        drop_element: "uploaded-container_" + $index,
        max_file_count: 1,
        url:
          ajax_url +
          "?action=upload_candidate_attachment_ajax&nonce=" +
          upload_nonce,
        filters: {
          mime_types: [
            {
              title: cv_title,
              extensions: cv_type,
            },
          ],
          max_file_size: cv_size,
          prevent_duplicates: true,
        },
      });

      uploader.init();

      function configProjectUploader(uploader) {
        var options = {
          filters: {
            mime_types: [
              {
                title: cv_title,
                extensions: cv_type,
              },
            ],
            max_file_size: cv_size,
            prevent_duplicates: true,
          },
        };

        uploader.setOption(options);

        uploader.bind("FilesAdded", function (up, files) {
          var candidateThumb = "";
          plupload.each(files, function (file) {
            candidateThumb +=
              '<li class="card-preview-item" id="holder-' + file.id + '"></li>';
          });

          document.getElementById(
            "project-uploaded-list_" + $index
          ).innerHTML += candidateThumb;
          up.refresh();
          uploader.start();
        });

        uploader.bind("UploadProgress", function (up, file) {
          var project_btn = "project-uploader_" + $index;
          document.getElementById(project_btn).innerHTML =
            '<span><i class="fal fa-spinner fa-spin large"></i></span>';
        });

        uploader.bind("Error", function (up, err) {
          document.getElementById(
            "jobportal_project_errors_log_" + $index
          ).innerHTML += "Error: " + err.message + "<br/>";
        });

        uploader.bind("FileUploaded", function (up, file, ajax_response) {
          var response = $.parseJSON(ajax_response.response);
          if (response.success) {
            var $html = $($("#project-single-image").html().trim());
            var $project_uploaded = $("#project-uploaded-list_" + $index);
            var $project_btn = $("#project-uploader_" + $index);

            $html.find("img").attr("src", response.url);
            $html.find("a").attr("data-attachment-id", response.attachment_id);

            $project_uploaded
              .find("input.candidate_project_image_id")
              .val(response.attachment_id);
            $project_uploaded
              .find("input.candidate_project_image_url")
              .val(response.url);

            $("#holder-" + file.id).html($html);
            $("#candidate-profile-form").find(".point-mark").on('change', );
            $project_btn.text("");
          }
        });
      }

      function triggerUploaderButton(uploader) {
        $(uploader.settings.browse_button).trigger("click");
      }

      var icon_delete =
        "#project-uploaded-list_" + $index + " .icon-project-delete";
      $("body").on("click", icon_delete, function (e) {
        e.preventDefault();
        var $this = $(this);
        var $none = $this.closest("#tab-projects").data("nonce");
        var $type = $this.closest("#tab-projects").data("type");
        var $project_uploaded = $this.closest(".project-uploaded-list");
        var $project_btn = $project_uploaded.siblings(".project-upload");
        var $text_uploaded = $this.closest("#tab-projects").data("uploaded");

        $project_uploaded
          .find('input[name="candidate_project_image_id[]"]')
          .val("");
        $project_uploaded
          .find('input[name="candidate_project_image_url[]"]')
          .val("");
        ajaxDeleteAttachment($(this), $type, $none);
        $("#candidate-profile-form").find(".point-mark").on('change', );

        $project_btn.html($text_uploaded);
      });

      $(this)
        .find(".browse.project-upload")
        .on("click", function () {
          configProjectUploader(uploader);

          triggerUploaderButton(uploader);
        });
    });
  }

  tab_projects_each();

  var buttonWrapper = submit_form.find(".button-wrapper");

  if (buttonWrapper.length > 0) {
    var buttonWrapperOriginalTop = 0;
    var buttonWrapperHeight = 0;
    var isInitialized = false;

    function updateButtonPosition() {
      if (!buttonWrapper.hasClass("is-sticky")) {
        buttonWrapperOriginalTop = buttonWrapper.offset().top;
        buttonWrapperHeight = buttonWrapper.outerHeight();
      }

      var formOffset = submit_form.offset();
      // Get form padding to calculate correct position and width
      var formPaddingLeft = parseInt(submit_form.css("padding-left")) || 0;
      var formPaddingRight = parseInt(submit_form.css("padding-right")) || 0;
      // Calculate inner width (excluding padding) to match button-wrapper's actual content width
      var formOuterWidth = submit_form.outerWidth();
      var formInnerWidth = formOuterWidth - formPaddingLeft - formPaddingRight;

      // Set left position including padding (to align with content area), and width excluding padding
      buttonWrapper[0].style.setProperty('--sticky-form-left', (formOffset.left + formPaddingLeft) + 'px');
      buttonWrapper[0].style.setProperty('--sticky-form-width', formInnerWidth + 'px');
    }

    function checkButtonVisibility() {
      if (!isInitialized) {
        updateButtonPosition();
        isInitialized = true;
      }

      var scrollTop = $(window).scrollTop();
      var windowHeight = $(window).height();
      var buttonTop = buttonWrapperOriginalTop;
      var viewportBottom = scrollTop + windowHeight;

      var isOutOfView = buttonTop > viewportBottom;

      if (isOutOfView) {
        if (!buttonWrapper.hasClass("is-sticky")) {
          updateButtonPosition();
          buttonWrapper.addClass("is-sticky");
        }
      } else {
        if (buttonWrapper.hasClass("is-sticky")) {
          buttonWrapper.removeClass("is-sticky");
          setTimeout(function() {
            updateButtonPosition();
          }, 100);
        }
      }
    }

    var scrollTimeout;
    $(window).on("scroll", function() {
      clearTimeout(scrollTimeout);
      scrollTimeout = setTimeout(function() {
        checkButtonVisibility();
      }, 16);
    });

    $(window).on("resize", function() {
      updateButtonPosition();
      checkButtonVisibility();
    });

    checkButtonVisibility();
  }
});
