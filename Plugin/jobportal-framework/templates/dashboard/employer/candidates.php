<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<div class="entry-my-page my-candidate">
    <div class="entry-title">
        <h4><?php esc_html_e('My Following', 'jobportal-framework'); ?></h4>
    </div>
    <div class="tab-dashboard">
        <ul class="tab-list">
            <li class="tab-item tab-my-follow"><a href="#tab-my-follow" data-text="<?php esc_attr_e('Following', 'jobportal-framework'); ?>"><?php esc_html_e('Following', 'jobportal-framework'); ?></a></li>
            <li class="tab-item tab-candidates-dashboard"><a href="#tab-candidates-dashboard" data-text="<?php esc_attr_e('Followers', 'jobportal-framework'); ?>"><?php esc_html_e('Followers', 'jobportal-framework'); ?></a></li>
            <li class="tab-item tab-invite"><a href="#tab-invite" data-text="<?php esc_attr_e('Invite', 'jobportal-framework'); ?>"><?php esc_html_e('Invite', 'jobportal-framework'); ?></a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-info" id="tab-my-follow">
                <?php jobportal_get_template('dashboard/employer/candidates/my-follow.php'); ?>
            </div>
            <div class="tab-info" id="tab-candidates-dashboard">
                <?php jobportal_get_template('dashboard/employer/candidates/candidates-follow.php'); ?>
            </div>
            <div class="tab-info" id="tab-invite">
                <?php jobportal_get_template('dashboard/employer/candidates/candidates-invite.php'); ?>
            </div>
        </div>
    </div>
</div>