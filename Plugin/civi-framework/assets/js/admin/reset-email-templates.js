(function($) {
    'use strict';

    $(document).ready(function() {
        $('#civi-reset-email-templates-btn').on('click', function(e) {
            e.preventDefault();

            if (!confirm(civiResetEmailTemplates.confirm_message)) {
                return;
            }

            var $button = $(this);
            var $message = $('#civi-reset-email-templates-message');
            var originalText = $button.html();

            // Disable button and show loading
            $button.prop('disabled', true);
            $button.html('<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite; margin-top: 3px;"></span> ' + (civiResetEmailTemplates.processing || 'Processing...'));
            $message.hide().removeClass('notice-success notice-error');

            $.ajax({
                url: civiResetEmailTemplates.ajax_url,
                type: 'POST',
                data: {
                    action: 'civi_reset_email_templates',
                    nonce: civiResetEmailTemplates.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $message
                            .addClass('notice notice-success is-dismissible')
                            .html('<p><strong>' + civiResetEmailTemplates.success_message + '</strong> ' + (response.data.message || '') + '</p>')
                            .fadeIn();

                        // Reload page after 2 seconds to show updated templates
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        $message
                            .addClass('notice notice-error is-dismissible')
                            .html('<p><strong>' + civiResetEmailTemplates.error_message + '</strong> ' + (response.data.message || '') + '</p>')
                            .fadeIn();

                        $button.prop('disabled', false);
                        $button.html(originalText);
                    }
                },
                error: function() {
                    $message
                        .addClass('notice notice-error is-dismissible')
                        .html('<p><strong>' + civiResetEmailTemplates.error_message + '</strong></p>')
                        .fadeIn();

                    $button.prop('disabled', false);
                    $button.html(originalText);
                }
            });
        });
    });

})(jQuery);

