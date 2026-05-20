<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $current_user;
$user_id = $current_user->ID;
?>
<div class="civi-employer-service entry-my-page">
    <div class="entry-title">
        <h4><?php esc_html_e('My Service', 'civi-framework'); ?></h4>
    </div>
    <div class="tab-dashboard">
        <ul class="tab-list">
            <li class="tab-item tab-orders-item"><a
                        href="#tab-orders"><?php esc_html_e('My Orders', 'civi-framework'); ?></a></li>
            <li class="tab-item tab-service-item"><a
                    href="#tab-wishlist"><?php esc_html_e('My Wishlist', 'civi-framework'); ?></a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-info" id="tab-wishlist">
                <?php civi_get_template('dashboard/employer/service/my-wishlist.php'); ?>
            </div>
            <div class="tab-info" id="tab-orders">
                <?php civi_get_template('dashboard/employer/service/my-orders.php'); ?>
            </div>
        </div>
    </div>
</div>