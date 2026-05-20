<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$show_candidate_payout = jobportal_get_option('show_candidate_payout', 1);
$enable_identity_verification  = JobPortal_Helper::jobportal_get_option('enable_identity_verification');
?>
<div id="candidate-settings-dashboard" class=" entry-my-page settings-dashboard">
    <div class="entry-title">
        <h4><?php esc_html_e('Settings', 'jobportal-framework') ?></h4>
    </div>
    <div class="tab-dashboard">
        <ul class="tab-list">
            <li class="tab-item tab-change-password"><a href="#tab-change-password"><?php esc_html_e('Change Password', 'jobportal-framework'); ?></a></li>
            <?php if ($show_candidate_payout) { ?>
                <li class="tab-item tab-payout"><a href="#tab-payout"><?php esc_html_e('Payout', 'jobportal-framework'); ?></a></li>
            <?php } ?>
            <?php if ($enable_identity_verification) { ?>
                <li class="tab-item tab-verify-id"><a href="#tab-verify-id"><?php esc_html_e('Identity Verification', 'jobportal-framework'); ?></a></li>
            <?php } ?>
        </ul>
        <div class="tab-content">
            <div class="tab-info" id="tab-change-password">
                <?php jobportal_get_template('dashboard/candidate/settings/change-password.php'); ?>
            </div>
            <?php if ($show_candidate_payout) { ?>
                <div class="tab-info" id="tab-payout">
                    <?php jobportal_get_template('dashboard/payout/payout.php'); ?>
                </div>
            <?php } ?>
            <?php if ($enable_identity_verification) { ?>
                <div class="tab-info" id="tab-verify-id">
                    <?php jobportal_get_template('dashboard/candidate/settings/verify-id.php'); ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
