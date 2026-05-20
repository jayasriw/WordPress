/**
 * Package Warning Handler
 *
 * Handles the warning modal display when user tries to switch packages
 * while having an active package with remaining quotas.
 *
 * @package civi-framework
 */

(function($) {
    'use strict';

    var CiviPackageWarning = {
        modal: null,
        targetUrl: null,
        isProcessing: false,

        /**
         * Initialize the module
         */
        init: function() {
            this.modal = $('#civi-package-impact-modal');
            this.bindEvents();
        },

        /**
         * Bind all event handlers
         */
        bindEvents: function() {
            var self = this;

            $(document).on('click', '.civi-package-wrap .civi-package-item .civi-package-choose a[href*="candidate_package_id"]', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (self.isProcessing) {
                    return false;
                }

                var $button = $(this);
                var targetUrl = $button.attr('href');
                var packageId = self.extractPackageId(targetUrl);

                if (!packageId) {
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

            if (this.modal.length > 0) {
                this.modal.on('click', '.civi-modal-close, .civi-modal-cancel, .civi-modal-overlay', function(e) {
                    e.preventDefault();
                    self.hideModal();
                });

                // Modal confirm handler
                this.modal.on('click', '.civi-modal-confirm', function(e) {
                    e.preventDefault();

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
            var match = url.match(/candidate_package_id=(\d+)/);
            return match ? match[1] : null;
        },

        /**
         * Check package impact via AJAX
         * @param {string} packageId - The new package ID
         * @param {jQuery} $button - The clicked button element
         */
        checkPackageImpact: function(packageId, $button) {
            var self = this;

            if (typeof civi_package_warning_vars === 'undefined') {
                window.location.href = self.targetUrl;
                return;
            }

            self.isProcessing = true;
            $button.addClass('loading');

            $.ajax({
                url: civi_package_warning_vars.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'civi_check_candidate_package_impact',
                    new_package_id: packageId,
                    nonce: civi_package_warning_vars.nonce
                },
                success: function(response) {
                    self.isProcessing = false;

                    if (window.CiviButtonLoading && $button) {
                        window.CiviButtonLoading.hide($button);
                    }

                    if (!response.success) {
                        window.location.href = self.targetUrl;
                        return;
                    }

                    var data = response.data;

                    if (!data.has_active_package || !data.needs_warning) {
                        window.location.href = self.targetUrl;
                        return;
                    }
                    self.populateModal(data);
                    self.showModal();
                },
                error: function(xhr, status, error) {
                    self.isProcessing = false;

                    if (window.CiviButtonLoading && $button) {
                        window.CiviButtonLoading.hide($button);
                    }
                    window.location.href = self.targetUrl;
                }
            });
        },

        /**
         * Populate modal with impact data
         * @param {object} data - Impact data from AJAX response
         */
        populateModal: function(data) {
            var self = this;
            var currentPackage = data.current_package;

            this.modal.find('.civi-impact-package-name').text(currentPackage.package_name);

            var expiryText = currentPackage.is_unlimited_time
                ? civi_package_warning_vars.i18n.never_expires
                : civi_package_warning_vars.i18n.expires_on + ' ' + currentPackage.expired_date_format;
            this.modal.find('.civi-impact-package-expiry').text(expiryText);

            // New package info
            this.modal.find('.civi-impact-new-package-name').text(data.new_package_name);

            // Cleanup warnings
            var $cleanupSection = this.modal.find('.civi-impact-cleanup');
            var $cleanupList = this.modal.find('.civi-impact-cleanup-list');
            $cleanupList.empty();

            if (data.cleanup_required && Object.keys(data.cleanup_required).length > 0) {
                $.each(data.cleanup_required, function(key, item) {
                    var actionText = self.getActionText(key, item);
                    $cleanupList.append(
                        '<li class="civi-impact-cleanup-item civi-impact-cleanup-' + key + '">' +
                            actionText +
                        '</li>'
                    );
                });
                $cleanupSection.show();
            } else {
                $cleanupSection.hide();
            }

            // Remaining quota warnings
            var $quotaSection = this.modal.find('.civi-impact-quota');
            var $quotaList = this.modal.find('.civi-impact-quota-list');
            $quotaList.empty();

            if (currentPackage.remaining_quota) {
                var hasRemainingQuota = false;
                $.each(currentPackage.remaining_quota, function(key, value) {
                    if (value > 0 && value < 999999999999999999) {
                        hasRemainingQuota = true;
                        var label = self.getQuotaLabel(key);
                        $quotaList.append(
                            '<li class="civi-impact-quota-item">' +
                                '<span class="quota-label">' + label + ':</span> ' +
                                '<span class="quota-value">' + value + ' ' + civi_package_warning_vars.i18n.remaining + '</span>' +
                            '</li>'
                        );
                    }
                });

                if (hasRemainingQuota) {
                    $quotaSection.show();
                } else {
                    $quotaSection.hide();
                }
            } else {
                $quotaSection.hide();
            }
        },

        /**
         * Get action text for cleanup item
         * @param {string} key - Cleanup type key
         * @param {object} item - Cleanup item data
         * @returns {string} - Formatted action text
         */
        getActionText: function(key, item) {
            var i18n = civi_package_warning_vars.i18n;
            var text = '';

            switch (key) {
                case 'featured_services':
                    text = item.to_remove + ' ' + i18n.featured_services_will_unfeature;
                    break;
                case 'jobs_wishlist':
                    text = item.to_remove + ' ' + i18n.wishlist_items_will_remove;
                    break;
                case 'company_follow':
                    text = item.to_remove + ' ' + i18n.companies_will_unfollow;
                    break;
                default:
                    text = item.to_remove + ' items will be removed';
            }

            text += ' (' + item.current + ' → ' + item.new_limit + ')';
            return text;
        },

        /**
         * Get label for quota type
         * @param {string} key - Quota type key
         * @returns {string} - Localized label
         */
        getQuotaLabel: function(key) {
            var i18n = civi_package_warning_vars.i18n;
            var labels = {
                'service': i18n.services,
                'service_featured': i18n.featured_services,
                'jobs_apply': i18n.jobs_apply,
                'jobs_wishlist': i18n.jobs_wishlist,
                'company_follow': i18n.company_follow
            };
            return labels[key] || key;
        },

        /**
         * Show the modal
         */
        showModal: function() {
            if (this.modal && this.modal.length > 0) {
                this.modal.fadeIn(200);
                $('body').addClass('civi-modal-open');
            }
        },

        /**
         * Hide the modal
         */
        hideModal: function() {
            if (this.modal && this.modal.length > 0) {
                this.modal.fadeOut(200);
                $('body').removeClass('civi-modal-open');
            }
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        CiviPackageWarning.init();
    });

})(jQuery);
