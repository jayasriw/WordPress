<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (empty($type)) {
    return;
}

// Define message configurations
$messages = array(
    'not_login' => array(
        'alert_type' => 'success',
        'icon' => 'fal fa-thumbs-up large',
        'has_actions' => true
    ),
    'warning' => array(
        'alert_type' => 'warning',
        'icon' => 'fal fa-exclamation-circle large',
        'has_actions' => false
    ),
    'error' => array(
        'alert_type' => 'error',
        'icon' => 'far fa-times large',
        'has_actions' => false
    ),
    'free_submit' => array(
        'alert_type' => 'warning',
        'icon' => 'fal fa-exclamation-circle large',
        'has_actions' => true
    )
);

// Check if type exists
if (!isset($messages[$type])) {
    return;
}

$config = $messages[$type];
?>
<div class="access-denied not-login">
    <div class="container">
        <div class="civi-my-page">
            <div class="entry-my-page">
                <div class="account logged-out civi-message alert-<?php echo esc_attr($config['alert_type']); ?>">
                    <div class="icon-message">
                        <i class="<?php echo esc_attr($config['icon']); ?>"></i>
                    </div>

                    <div class="entry-message">
                        <?php
                        switch ($type) {
                            case 'not_login':
                        ?>
                                <span><?php esc_html_e('You need login to continue.', 'civi-framework'); ?></span>
                                <a href="#popup-form" class="btn-login"><?php esc_html_e('Login Here', 'civi-framework'); ?></a>
                                <span><?php esc_html_e('or', 'civi-framework'); ?></span>
                                <a href="#popup-form" class="btn-register"><?php esc_html_e('Sign Up Now', 'civi-framework'); ?></a>
                            <?php
                                break;

                            case 'warning':
                            ?>
                                <p><?php esc_html_e('You are now a Premium Member.', 'civi-framework'); ?></p>
                            <?php
                                break;

                            case 'error':
                            ?>
                                <p><?php esc_html_e('An error occurred. Please try again.', 'civi-framework'); ?></p>
                            <?php
                                break;

                            case 'free_submit':
                                global $current_user;
                                wp_get_current_user();
                                $user_id = $current_user->ID;
                            ?>
                                <p>
                                    <?php esc_html_e("You are on free submit active", 'civi-framework'); ?>
                                    <?php if (in_array("civi_user_employer", (array)$current_user->roles)) { ?>
                                        <a href="<?php echo esc_url(civi_get_permalink('jobs_submit')); ?>">
                                            <?php esc_html_e('Add Jobs', 'civi-framework'); ?>
                                        </a>
                                    <?php } else { ?>
                                        <?php if (civi_get_option('enable_post_type_service') === '1') { ?>
                                            <a href="<?php echo esc_url(civi_get_permalink('submit_service')); ?>">
                                                <?php esc_html_e('Add Service', 'civi-framework'); ?>
                                            </a>
                                        <?php } ?>
                                    <?php } ?>
                                </p>
                        <?php
                                break;
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
