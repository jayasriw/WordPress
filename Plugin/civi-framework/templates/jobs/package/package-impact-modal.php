<?php
/**
 * Employer Package Impact Warning Modal Template
 *
 * This template displays a modal warning when employer tries to switch packages
 * while having an active package with remaining quotas or resources that will be affected.
 *
 * @package civi-framework
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="civi-employer-package-impact-modal" class="civi-modal civi-employer-package-impact-modal" style="display: none;">
    <div class="civi-modal-overlay"></div>
    <div class="civi-modal-content">
        <div class="civi-modal-header">
            <h3 class="civi-modal-title">
                <?php esc_html_e('Package Change Warning', 'civi-framework'); ?>
            </h3>
            <button type="button" class="civi-modal-close" aria-label="<?php esc_attr_e('Close', 'civi-framework'); ?>">
                &times;
            </button>
        </div>

        <div class="civi-modal-body">
            <!-- Current Package Info -->
            <div class="civi-impact-section civi-impact-current">
                <h4><?php esc_html_e('Current Package', 'civi-framework'); ?></h4>
                <div class="civi-impact-package-info">
                    <span class="civi-impact-package-name"></span>
                    <span class="civi-impact-package-expiry"></span>
                </div>
            </div>

            <!-- Cleanup Warning -->
            <div class="civi-impact-section civi-impact-cleanup" style="display: none;">
                <h4><?php esc_html_e('Changes That Will Occur', 'civi-framework'); ?></h4>
                <ul class="civi-impact-cleanup-list"></ul>
                <p class="civi-impact-cleanup-note">
                    <?php esc_html_e('Jobs will be expired (oldest first), featured jobs will be un-featured (newest first), and candidate follows will be removed (newest first).', 'civi-framework'); ?>
                </p>
            </div>

            <!-- Remaining Quota Warning -->
            <div class="civi-impact-section civi-impact-quota" style="display: none;">
                <h4><?php esc_html_e('Remaining Quota Will Be Lost', 'civi-framework'); ?></h4>
                <ul class="civi-impact-quota-list"></ul>
            </div>

            <!-- New Package Info -->
            <div class="civi-impact-section civi-impact-new">
                <h4><?php esc_html_e('New Package', 'civi-framework'); ?></h4>
                <div class="civi-impact-package-info">
                    <span class="civi-impact-new-package-name"></span>
                </div>
            </div>
        </div>

        <div class="civi-modal-footer">
            <button type="button" class="civi-button button-outline civi-modal-cancel">
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
