/**
 * Global Button Loading Utility
 *
 * Provides a reusable way to show/hide loading states on buttons
 *
 * Usage:
 * - Add class 'has-loading' to any button that needs loading state
 * - Button must contain: <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
 * - Call JobPortalButtonLoading.show($button) to show loading
 * - Call JobPortalButtonLoading.hide($button) to hide loading
 *
 * @package jobportal-framework
 */

(function($) {
    'use strict';

    window.JobPortalButtonLoading = {
        /**
         * Show loading state on button
         * @param {jQuery} $button - The button element
         */
        show: function($button) {
            if (!$button || !$button.length) {
                console.warn('JobPortalButtonLoading.show: Invalid button element');
                return;
            }

            $button.addClass('loading').prop('disabled', true);

            // If button doesn't have btn-loading span, add it
            if ($button.find('.btn-loading').length === 0) {
                $button.append('<span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>');
            } else {
                // Force show if already exists
                $button.find('.btn-loading').show();
            }
        },

        /**
         * Hide loading state on button
         * @param {jQuery} $button - The button element
         */
        hide: function($button) {
            if (!$button || !$button.length) {
                console.warn('JobPortalButtonLoading.hide: Invalid button element');
                return;
            }

            $button.removeClass('loading').prop('disabled', false);
        },

        /**
         * Initialize auto-loading for links with data-loading attribute
         * When clicked, automatically shows loading state
         */
        init: function() {
            $(document).on('click', 'a[data-loading], button[data-loading]', function(e) {
                var $this = $(this);

                // Don't show loading if link is disabled or already loading
                if ($this.hasClass('loading') || $this.prop('disabled')) {
                    e.preventDefault();
                    return false;
                }

                // Show loading
                JobPortalButtonLoading.show($this);
            });
        }
    };

    // Auto-initialize on document ready
    $(document).ready(function() {
        JobPortalButtonLoading.init();
    });

})(jQuery);
