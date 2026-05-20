<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$show_employer_payout = civi_get_option('show_employer_payout', 1);
$enable_identity_verification  = Civi_Helper::civi_get_option('enable_identity_verification');
?>
<div class="entry-my-page settings-dashboard">
    <div class="entry-title">
        <h4><?php esc_html_e('Settings', 'civi-framework') ?></h4>
    </div>
    <div class="tab-dashboard">
        <ul class="tab-list">
            <li class="tab-item tab-profile"><a href="#tab-profile"><?php esc_html_e('Personal info', 'civi-framework'); ?></a></li>
            <?php if ($show_employer_payout) { ?>
                <li class="tab-item tab-payout"><a href="#tab-payout"><?php esc_html_e('Payout', 'civi-framework'); ?></a></li>
            <?php } ?>
            <?php if ($enable_identity_verification) { ?>
                <li class="tab-item tab-verify-id"><a href="#tab-verify-id"><?php esc_html_e('Identity Verification', 'civi-framework'); ?></a></li>
            <?php } ?>
        </ul>
        <div class="tab-content">
            <div class="tab-info" id="tab-profile">
                <?php civi_get_template('dashboard/employer/settings/profile.php'); ?>
            </div>
            <?php if ($show_employer_payout) { ?>
                <div class="tab-info" id="tab-payout">
                    <?php civi_get_template('dashboard/payout/payout.php'); ?>
                </div>
            <?php } ?>
            <?php if ($enable_identity_verification) { ?>
                <div class="tab-info" id="tab-verify-id">
                    <?php civi_get_template('dashboard/employer/settings/verify-id.php'); ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
