var JOBPORTAL_USER_PACKAGE_WARNING = JOBPORTAL_USER_PACKAGE_WARNING || {};
(function ($) {
  "use strict";

  JOBPORTAL_USER_PACKAGE_WARNING = {
    init: function () {
      this.checkOldPackage();
      this.modalEvents();
    },

    checkOldPackage: function () {
      var self = this;
      var $newPackageLink = $("#jobportal-new-package-link");

      if ($newPackageLink.length === 0) {
        return;
      }

      $newPackageLink.on("click", function (e) {
        e.preventDefault();

        var $button = $(this);
        var originalHref = $button.attr("href");

        // Check if user has active package
        if (typeof jobportal_package_vars !== "undefined") {
          var ajax_url = jobportal_package_vars.ajax_url;
          var nonce = jobportal_package_vars.nonce;
          var action = jobportal_package_vars.action || "jobportal_check_old_package_before_activate";

          $.ajax({
            type: "POST",
            url: ajax_url,
            data: {
              action: action,
              nonce: nonce
            },
            beforeSend: function () {
              // Show loading effect using utility
              if (window.JobPortalButtonLoading) {
                window.JobPortalButtonLoading.show($button);
              } else {
                // Fallback if utility not available
                $button.addClass("loading").prop("disabled", true);
              }
            },
            success: function (response) {
              // Hide loading effect
              if (window.JobPortalButtonLoading) {
                window.JobPortalButtonLoading.hide($button);
              } else {
                // Fallback if utility not available
                $button.removeClass("loading").prop("disabled", false);
              }

              if (response.success && response.data.has_active_package) {
                // Show warning modal
                self.showWarningModal(response.data.package_info, originalHref);
              } else {
                // No active package, proceed normally
                window.location.href = originalHref;
              }
            },
            error: function () {
              // Hide loading effect on error
              if (window.JobPortalButtonLoading) {
                window.JobPortalButtonLoading.hide($button);
              } else {
                // Fallback if utility not available
                $button.removeClass("loading").prop("disabled", false);
              }
              // On error, proceed normally
              window.location.href = originalHref;
            }
          });
        } else {
          // If vars not available, proceed normally
          window.location.href = originalHref;
        }
      });
    },

    showWarningModal: function (packageInfo, redirectUrl) {
      var $modal = $("#jobportal-package-warning-modal");
      var $packageName = $("#warning-package-name");
      var $expiredDate = $("#warning-expired-date");
      var $remainingQuota = $("#warning-remaining-quota");

      // Set package info
      $packageName.text(packageInfo.package_name || "");
      $expiredDate.text(packageInfo.expired_date_format || "");

      // Set remaining quota
      $remainingQuota.empty();
      var quota = packageInfo.remaining_quota || {};

      // Candidate package quota
      if (quota.service !== undefined && quota.service !== 999999999999999999 && quota.service > 0) {
        $remainingQuota.append(
          '<li>' +
          quota.service +
          ' ' +
          (quota.service === 1 ? 'Service' : 'Services') +
          '</li>'
        );
      }

      if (quota.service_featured !== undefined && quota.service_featured !== 999999999999999999 && quota.service_featured > 0) {
        $remainingQuota.append(
          '<li>' +
          quota.service_featured +
          ' ' +
          (quota.service_featured === 1 ? 'Featured Service' : 'Featured Services') +
          '</li>'
        );
      }

      if (quota.jobs_apply !== undefined && quota.jobs_apply !== 999999999999999999 && quota.jobs_apply > 0) {
        $remainingQuota.append(
          '<li>' +
          quota.jobs_apply +
          ' ' +
          (quota.jobs_apply === 1 ? 'Job Application' : 'Job Applications') +
          '</li>'
        );
      }

      if (quota.jobs_wishlist !== undefined && quota.jobs_wishlist !== 999999999999999999 && quota.jobs_wishlist > 0) {
        $remainingQuota.append(
          '<li>' +
          quota.jobs_wishlist +
          ' ' +
          (quota.jobs_wishlist === 1 ? 'Wishlist Item' : 'Wishlist Items') +
          '</li>'
        );
      }

      if (quota.company_follow !== undefined && quota.company_follow !== 999999999999999999 && quota.company_follow > 0) {
        $remainingQuota.append(
          '<li>' +
          quota.company_follow +
          ' ' +
          (quota.company_follow === 1 ? 'Company Follow' : 'Company Follows') +
          '</li>'
        );
      }

      // Employer package quota
      if (quota.jobs !== undefined && quota.jobs !== 999999999999999999 && quota.jobs > 0) {
        $remainingQuota.append(
          '<li>' +
          quota.jobs +
          ' ' +
          (quota.jobs === 1 ? 'Job' : 'Jobs') +
          '</li>'
        );
      }

      if (quota.jobs_featured !== undefined && quota.jobs_featured !== 999999999999999999 && quota.jobs_featured > 0) {
        $remainingQuota.append(
          '<li>' +
          quota.jobs_featured +
          ' ' +
          (quota.jobs_featured === 1 ? 'Featured Job' : 'Featured Jobs') +
          '</li>'
        );
      }

      if (quota.candidate_follow !== undefined && quota.candidate_follow !== 999999999999999999 && quota.candidate_follow > 0) {
        $remainingQuota.append(
          '<li>' +
          quota.candidate_follow +
          ' ' +
          (quota.candidate_follow === 1 ? 'Candidate Follow' : 'Candidate Follows') +
          '</li>'
        );
      }

      if (quota.download_cv !== undefined && quota.download_cv !== 999999999999999999 && quota.download_cv > 0) {
        $remainingQuota.append(
          '<li>' +
          quota.download_cv +
          ' ' +
          (quota.download_cv === 1 ? 'CV Download' : 'CV Downloads') +
          '</li>'
        );
      }

      if ($remainingQuota.children().length === 0) {
        $remainingQuota.append('<li>' + 'No quota remaining' + '</li>');
      }

      // Store redirect URL
      $modal.data("redirect-url", redirectUrl);

      // Show modal
      $modal.fadeIn(300);
    },

    hideWarningModal: function () {
      $("#jobportal-package-warning-modal").fadeOut(300);
    },

    modalEvents: function () {
      var self = this;
      var $modal = $("#jobportal-package-warning-modal");
      var $overlay = $modal.find(".jobportal-modal-overlay");
      var $closeBtn = $modal.find(".jobportal-modal-close");
      var $cancelBtn = $modal.find(".jobportal-modal-cancel");
      var $confirmBtn = $modal.find(".jobportal-modal-confirm");

      // Close on overlay click
      $overlay.on("click", function () {
        self.hideWarningModal();
      });

      // Close on close button
      $closeBtn.on("click", function () {
        self.hideWarningModal();
      });

      // Cancel button
      $cancelBtn.on("click", function () {
        self.hideWarningModal();
      });

      // Confirm button - proceed to package page
      $confirmBtn.on("click", function () {
        var $button = $(this);
        var redirectUrl = $modal.data("redirect-url");
        if (redirectUrl) {
          // Show loading effect before redirect
          if (window.JobPortalButtonLoading) {
            window.JobPortalButtonLoading.show($button);
          } else {
            // Fallback if utility not available
            $button.addClass("loading").prop("disabled", true);
          }
          window.location.href = redirectUrl;
        } else {
          self.hideWarningModal();
        }
      });

      // Close on ESC key
      $(document).on("keydown", function (e) {
        if (e.key === "Escape" && $modal.is(":visible")) {
          self.hideWarningModal();
        }
      });
    }
  };

  $(document).ready(function () {
    JOBPORTAL_USER_PACKAGE_WARNING.init();
  });
})(jQuery);

