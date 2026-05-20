<?php
/**
 * Employer Package Impact Warning Modal Template
 *
 * This template displays a modal warning when employer tries to switch packages
 * while having an active package with remaining quotas or resources that will be affected.
 *
 * @package jobportal-framework
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="jobportal-employer-package-impact-modal" class="jobportal-modal jobportal-employer-package-impact-modal" style="display: none;">
    <div class="jobportal-modal-overlay"></div>
    <div class="jobportal-modal-content">
        <div class="jobportal-modal-header">
            <h3 class="jobportal-modal-title">
                <?php esc_html_e('Package Change Warning', 'jobportal-framework'); ?>
            </h3>
            <button type="button" class="jobportal-modal-close" aria-label="<?php esc_attr_e('Close', 'jobportal-framework'); ?>">
                &times;
            </button>
        </div>

        <div class="jobportal-modal-body">
            <!-- Current Package Info -->
            <div class="jobportal-impact-section jobportal-impact-current">
                <h4><?php esc_html_e('Current Package', 'jobportal-framework'); ?></h4>
                <div class="jobportal-impact-package-info">
                    <span class="jobportal-impact-package-name"></span>
                    <span class="jobportal-impact-package-expiry"></span>
                </div>
            </div>

            <!-- Cleanup Warning -->
            <div class="jobportal-impact-section jobportal-impact-cleanup" style="display: none;">
                <h4><?php esc_html_e('Changes That Will Occur', 'jobportal-framework'); ?></h4>
                <ul class="jobportal-impact-cleanup-list"></ul>
                <p class="jobportal-impact-cleanup-note">
                    <?php esc_html_e('Jobs will be expired (oldest first), featured jobs will be un-featured (newest first), and candidate follows will be removed (newest first).', 'jobportal-framework'); ?>
                </p>
            </div>

            <!-- Remaining Quota Warning -->
            <div class="jobportal-impact-section jobportal-impact-quota" style="display: none;">
                <h4><?php esc_html_e('Remaining Quota Will Be Lost', 'jobportal-framework'); ?></h4>
                <ul class="jobportal-impact-quota-list"></ul>
            </div>

            <!-- New Package Info -->
            <div class="jobportal-impact-section jobportal-impact-new">
                <h4><?php esc_html_e('New Package', 'jobportal-framework'); ?></h4>
                <div class="jobportal-impact-package-info">
                    <span class="jobportal-impact-new-package-name"></span>
                </div>
            </div>
        </div>

        <div class="jobportal-modal-footer">
            <button type="button" class="jobportal-button button-outline jobportal-modal-cancel">
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
