<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<!-- Modal Warning for Active Package -->
<div id="jobportal-package-warning-modal" class="jobportal-modal" style="display: none;">
    <div class="jobportal-modal-overlay"></div>
    <div class="jobportal-modal-content">
        <div class="jobportal-modal-header">
            <h3><?php esc_html_e('Warning: Active Package Detected', 'jobportal-framework'); ?></h3>
            <button class="jobportal-modal-close" type="button">&times;</button>
        </div>
        <div class="jobportal-modal-body">
            <p><?php esc_html_e('You have an active package that hasn\'t expired yet.', 'jobportal-framework'); ?></p>
            <div class="package-info-warning">
                <ul>
                    <li><strong><?php esc_html_e('Current Package:', 'jobportal-framework'); ?></strong> <span id="warning-package-name"></span></li>
                    <li><strong><?php esc_html_e('Expires:', 'jobportal-framework'); ?></strong> <span id="warning-expired-date"></span></li>
                    <li><strong><?php esc_html_e('Remaining Quota:', 'jobportal-framework'); ?></strong>
                        <ul id="warning-remaining-quota"></ul>
                    </li>
                </ul>
            </div>
            <div class="package-warning-notice">
                <p><strong><?php esc_html_e('Important:', 'jobportal-framework'); ?></strong></p>
                <p><?php esc_html_e('Activating a new package will reset all quotas to the new package values. Your current featured jobs will be deducted from the new package quota.', 'jobportal-framework'); ?></p>
            </div>
        </div>
        <div class="jobportal-modal-footer">
            <button type="button" class="jobportal-button button-outline-accent jobportal-modal-cancel">
                <?php esc_html_e('Cancel', 'jobportal-framework'); ?>
                <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
            </button>
            <button type="button" class="jobportal-button jobportal-modal-confirm">
                <?php esc_html_e('Continue', 'jobportal-framework'); ?>
                <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
            </button>
        </div>
    </div>
</div>

