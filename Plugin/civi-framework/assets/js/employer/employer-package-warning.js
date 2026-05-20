/**
 * Employer Package Warning Handler
 *
 * Handles the warning modal display when employer tries to switch packages
 * while having an active package with remaining quotas or resources that will be affected.
 *
 * @package civi-framework
 */

(function($) {
    'use strict';

    var CiviEmployerPackageWarning = {
        modal: null,
        targetUrl: null,
        isProcessing: false,

        /**
         * Initialize the module
         */
        init: function() {
            this.modal = $('#civi-employer-package-impact-modal');
            this.bindEvents();
        },

        /**
         * Bind all event handlers
         */
        bindEvents: function() {
            var self = this;

            // Intercept package selection clicks on ALL packages (including current)
            // Target any link inside .civi-package-choose
            $(document).on('click', '.civi-package-wrap .civi-package-item .civi-package-choose a[href*="package_id"]', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (self.isProcessing) {
                    return false;
                }

                var $button = $(this);
                var targetUrl = $button.attr('href');
                var packageId = self.extractPackageId(targetUrl);

                if (!packageId) {
                    // Can't extract package ID, proceed normally
                    window.location.href = targetUrl;
                    return;
                }

                // Show loading on button
                if (window.CiviButtonLoading) {
                    window.CiviButtonLoading.show($button);
                }

                self.targetUrl = targetUrl;
                self.checkPackageImpact(packageId, $button);
            });

            // Modal close handlers (only if modal exists)
            if (this.modal.length > 0) {
                this.modal.on('click', '.civi-modal-close, .civi-modal-cancel, .civi-modal-overlay', function(e) {
                    e.preventDefault();
                    self.hideModal();
                });

                // Modal confirm handler
                this.modal.on('click', '.civi-modal-confirm', function(e) {
                    e.preventDefault();

                    // Show loading on confirm button
                    if (window.CiviButtonLoading) {
                        window.CiviButtonLoading.show($(this));
                    }

                    self.hideModal();
                    if (self.targetUrl) {
                        window.location.href = self.targetUrl;
                    }
                });
            }

            // Close on escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && self.modal && self.modal.is(':visible')) {
                    self.hideModal();
                }
            });
        },

        /**
         * Extract package ID from payment URL
         * @param {string} url - The payment URL
         * @returns {string|null} - Package ID or null
         */
        extractPackageId: function(url) {
            if (!url) return null;
            var match = url.match(/package_id=(\d+)/);
            return match ? match[1] : null;
        },

        /**
         * Check package impact via AJAX
         * @param {string} packageId - The package ID to check
         * @param {jQuery} $button - The button element
         */
        checkPackageImpact: function(packageId, $button) {
            var self = this;

            console.log('CiviEmployerPackageWarning: Checking package impact for ID:', packageId);

            self.isProcessing = true;

            $.ajax({
                url: civiEmployerPackageWarning.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'civi_check_employer_package_impact',
                    security: civiEmployerPackageWarning.nonce,
                    package_id: packageId
                },
                success: function(response) {
                    self.isProcessing = false;

                    if (response.success && response.data) {
                        var impact = response.data;

                        if (impact.needs_warning) {
                            // Hide loading on package button before showing modal
                            if (window.CiviButtonLoading && $button) {
                                window.CiviButtonLoading.hide($button);
                            }

                            // Show modal with impact details
                            self.populateModal(impact);
                            self.showModal();
                        } else {
                            // No warning needed, proceed to payment
                            window.location.href = self.targetUrl;
                        }
                    } else {
                        // Error or no active package, proceed normally
                        window.location.href = self.targetUrl;
                    }
                },
                error: function(xhr, status, error) {
                    self.isProcessing = false;

                    // Hide loading on button
                    if (window.CiviButtonLoading && $button) {
                        window.CiviButtonLoading.hide($button);
                    }

                    // On error, proceed to payment (fail-safe)
                    window.location.href = self.targetUrl;
                }
            });
        },

        /**
         * Populate modal with impact data
         * @param {object} impact - Impact data from server
         */
        populateModal: function(impact) {

            // Current package info
            this.modal.find('.civi-impact-package-name').text(impact.current_package.package_name);
            this.modal.find('.civi-impact-package-expiry').text(
                civiEmployerPackageWarning.i18n.expires + ': ' + impact.current_package.expired_date_format
            );

            // New package info
            this.modal.find('.civi-impact-new-package-name').text(impact.new_package_name);

            // Cleanup warnings
            var $cleanupSection = this.modal.find('.civi-impact-cleanup');
            var $cleanupList = this.modal.find('.civi-impact-cleanup-list');
            $cleanupList.empty();

            if (impact.cleanup_required && Object.keys(impact.cleanup_required).length > 0) {
                $cleanupSection.show();

                $.each(impact.cleanup_required, function(key, cleanup) {
                    var message = '';

                    if (key === 'jobs') {
                        message = civiEmployerPackageWarning.i18n.jobs + ': ' +
                                cleanup.current + '/' + cleanup.new_limit + ' (' + civiEmployerPackageWarning.i18n.exceeded + ') - ' +
                                civiEmployerPackageWarning.i18n.will_expire + ' ' + cleanup.to_remove + ' ' + civiEmployerPackageWarning.i18n.jobs_oldest;
                    } else if (key === 'jobs_featured') {
                        message = civiEmployerPackageWarning.i18n.featured_jobs + ': ' +
                                cleanup.current + '/' + cleanup.new_limit + ' (' + civiEmployerPackageWarning.i18n.exceeded + ') - ' +
                                civiEmployerPackageWarning.i18n.will_unfeature + ' ' + cleanup.to_remove + ' ' + civiEmployerPackageWarning.i18n.jobs_newest;
                    } else if (key === 'candidate_follow') {
                        message = civiEmployerPackageWarning.i18n.candidate_follow + ': ' +
                                cleanup.current + '/' + cleanup.new_limit + ' (' + civiEmployerPackageWarning.i18n.exceeded + ') - ' +
                                civiEmployerPackageWarning.i18n.will_unfollow + ' ' + cleanup.to_remove + ' ' + civiEmployerPackageWarning.i18n.candidates_newest;
                    }

                    if (message) {
                        $cleanupList.append('<li>' + message + '</li>');
                    }
                });
            } else {
                $cleanupSection.hide();
            }

            // Remaining quota warnings
            var $quotaSection = this.modal.find('.civi-impact-quota');
            var $quotaList = this.modal.find('.civi-impact-quota-list');
            $quotaList.empty();

            var hasRemainingQuota = false;
            if (impact.current_package.remaining_quota) {
                $.each(impact.current_package.remaining_quota, function(key, value) {
                    if (value > 0 && value < 999999999999999999) {
                        hasRemainingQuota = true;
                        var label = '';

                        if (key === 'jobs') label = civiEmployerPackageWarning.i18n.jobs;
                        else if (key === 'jobs_featured') label = civiEmployerPackageWarning.i18n.featured_jobs;
                        else if (key === 'candidate_follow') label = civiEmployerPackageWarning.i18n.candidate_follow;
                        else if (key === 'download_cv') label = civiEmployerPackageWarning.i18n.download_cv;

                        if (label) {
                            $quotaList.append('<li>' + label + ': ' + value + ' ' + civiEmployerPackageWarning.i18n.remaining + '</li>');
                        }
                    }
                });
            }

            if (hasRemainingQuota) {
                $quotaSection.show();
            } else {
                $quotaSection.hide();
            }
        },

        /**
         * Show the modal
         */
        showModal: function() {
            console.log('CiviEmployerPackageWarning: showModal called');
            console.log('CiviEmployerPackageWarning: Modal element:', this.modal);
            console.log('CiviEmployerPackageWarning: Modal length:', this.modal.length);

            if (this.modal.length > 0) {
                console.log('CiviEmployerPackageWarning: Showing modal');
                this.modal.addClass('active').fadeIn(300);
                $('body').addClass('modal-open');
            } else {
                console.error('CiviEmployerPackageWarning: Modal element not found in DOM!');
            }
        },

        /**
         * Hide the modal
         */
        hideModal: function() {
            if (this.modal.length > 0) {
                console.log('CiviEmployerPackageWarning: Hiding modal');
                this.modal.removeClass('active').fadeOut(300);
                $('body').removeClass('modal-open');
            }
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        CiviEmployerPackageWarning.init();
    });

})(jQuery);
