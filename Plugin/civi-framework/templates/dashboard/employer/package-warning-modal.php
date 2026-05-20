<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<!-- Modal Warning for Active Package -->
<div id="civi-package-warning-modal" class="civi-modal" style="display: none;">
    <div class="civi-modal-overlay"></div>
    <div class="civi-modal-content">
        <div class="civi-modal-header">
            <h3><?php esc_html_e('Warning: Active Package Detected', 'civi-framework'); ?></h3>
            <button class="civi-modal-close" type="button">&times;</button>
        </div>
        <div class="civi-modal-body">
            <p><?php esc_html_e('You have an active package that hasn\'t expired yet.', 'civi-framework'); ?></p>
            <div class="package-info-warning">
                <ul>
                    <li><strong><?php esc_html_e('Current Package:', 'civi-framework'); ?></strong> <span id="warning-package-name"></span></li>
                    <li><strong><?php esc_html_e('Expires:', 'civi-framework'); ?></strong> <span id="warning-expired-date"></span></li>
                    <li><strong><?php esc_html_e('Remaining Quota:', 'civi-framework'); ?></strong>
                        <ul id="warning-remaining-quota"></ul>
                    </li>
                </ul>
            </div>
            <div class="package-warning-notice">
                <p><strong><?php esc_html_e('Important:', 'civi-framework'); ?></strong></p>
                <p><?php esc_html_e('Activating a new package will reset all quotas to the new package values. Your current featured jobs will be deducted from the new package quota.', 'civi-framework'); ?></p>
            </div>
        </div>
        <div class="civi-modal-footer">
            <button type="button" class="civi-button button-outline-accent civi-modal-cancel">
                <?php esc_html_e('Cancel', 'civi-framework'); ?>
                <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
            </button>
            <button type="button" class="civi-button civi-modal-confirm">
                <?php esc_html_e('Continue', 'civi-framework'); ?>
                <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
            </button>
        </div>
    </div>
</div>

