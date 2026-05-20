<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $current_user;
$user_id = $current_user->ID;
$custom_payout = jobportal_get_option('custom_payout_setting');
wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'payout');
wp_localize_script(
    JOBPORTAL_PLUGIN_PREFIX . 'payout',
    'jobportal_payout_vars',
    array(
        'custom_field_payout' => $custom_payout,
    )
);
$payout_paypal = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'author_payout_paypal', $user_id);
$payout_stripe = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'author_payout_stripe', $user_id);
$payout_card_number = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'author_payout_card_number', $user_id);
$payout_card_name = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'author_payout_card_name', $user_id);
$payout_bank_transfer_name = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'author_payout_bank_transfer_name', $user_id);
$payout_paypal = !empty($payout_paypal) ? $payout_paypal : '';
$payout_stripe = !empty($payout_stripe) ? $payout_stripe : '';
$payout_card_number = !empty($payout_card_number) ? $payout_card_number : '';
$payout_card_name = !empty($payout_card_name) ? $payout_card_name : '';
$payout_bank_transfer_name = !empty($payout_bank_transfer_name) ? $payout_bank_transfer_name : '';
$enable_paypal = jobportal_get_option('enable_payout_paypal');
$enable_stripe = jobportal_get_option('enable_payout_stripe');
$enable_bank = jobportal_get_option('enable_payout_bank_transfer');
$name_same = $name_nsame = array();
?>

<form action="#" method="post" class="jobportal-payout-dashboard" enctype="multipart/form-data">
    <ul>
        <?php if($enable_paypal === '1') : ?>
            <li class="payout-item">
                <h5 class="title"><?php esc_html_e('Paypal', 'jobportal-framework') ?></h5>
                <div class="content">
                    <div class="form-group">
                        <label><?php esc_html_e('Paypal email', 'jobportal-framework'); ?></label>
                        <input type="email" id="payout-paypal" name="payout_paypal"
                               placeholder="<?php esc_attr_e('Enter your email', 'jobportal-framework') ?>"
                               value="<?php echo esc_attr($payout_paypal)?>">
                    </div>
                </div>
            </li>
        <?php endif; ?>
        <?php if($enable_stripe === '1') : ?>
            <li class="payout-item">
                <h5 class="title"><?php esc_html_e('Stripe', 'jobportal-framework') ?></h5>
                <div class="content">
                    <div class="form-group payout-content">
                        <label><?php esc_html_e('Stripe account', 'jobportal-framework'); ?></label>
                        <input type="text" id="payout-stripe" name="payout_stripe"
                               placeholder="<?php esc_attr_e('Enter your account', 'jobportal-framework') ?>"
                               value="<?php echo esc_attr($payout_stripe)?>">
                    </div>
                </div>
            </li>
        <?php endif; ?>
        <?php if($enable_bank === '1') : ?>
            <li class="payout-item">
                <h5 class="title"><?php esc_html_e('Bank Transfer', 'jobportal-framework') ?></h5>
                <div class="content">
                    <div class="form-group payout-content">
                        <label><?php esc_html_e('Card Number', 'jobportal-framework'); ?></label>
                        <input type="text" id="payout-card-number" name="payout_card_number"
                               placeholder="<?php esc_attr_e('Enter card number', 'jobportal-framework') ?>"
                               value="<?php echo esc_attr($payout_card_number)?>">
                    </div>
                    <div class="form-group payout-content">
                        <label><?php esc_html_e('Card Name', 'jobportal-framework'); ?></label>
                        <input type="text" id="payout-card-name" name="payout_card_name"
                               placeholder="<?php esc_attr_e('Enter card name', 'jobportal-framework') ?>"
                               value="<?php echo esc_attr($payout_card_name)?>">
                    </div>
                    <div class="form-group payout-content">
                        <label><?php esc_html_e('Bank Name', 'jobportal-framework'); ?></label>
                        <input type="text" id="payout-bank-transfer-name" name="payout_bank_transfer_name"
                               placeholder="<?php esc_attr_e('Enter bank name', 'jobportal-framework') ?>"
                               value="<?php echo esc_attr($payout_bank_transfer_name)?>">
                    </div>
                </div>
            </li>
        <?php endif;

        if(!empty($custom_payout)) :
            foreach ($custom_payout as $field) :
                if(!empty($field['name'])) :
                    $field_id = str_replace(' ', '-', $field['name']);
                    $author_payout_custom = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'author_payout_custom_' . $field['id'], $user_id);
                    $author_payout_custom = !empty($author_payout_custom) ? $author_payout_custom : '';
                    if (in_array($field['name'], $name_nsame)) {
                        if (!in_array($field['name'], $name_same)) { ?>
                            <div class="form-group payout-content <?php echo esc_attr($field_id); ?>">
                                <label><?php echo esc_html($field['label']); ?></label>
                                <input type="<?php echo esc_attr($field['type']); ?>" id="<?php echo esc_attr($field['id']); ?>" name="<?php echo esc_attr($field['id']); ?>"
                                       placeholder="<?php echo sprintf(__('Enter %s', 'jobportal-framework'), $field['label']); ?>"
                                       value="<?php echo esc_attr($author_payout_custom); ?>">
                            </div>
                        <?php }
                    } else { ?>
                        <li class="payout-item" id="<?php echo esc_attr($field_id); ?>">
                            <h5 class="title"><?php echo esc_html($field['name']); ?></h5>
                            <div class="content">
                                <div class="form-group payout-content">
                                    <label><?php echo esc_html($field['label']); ?></label>
                                    <input type="<?php echo esc_attr($field['type']); ?>" id="<?php echo esc_attr($field['id']); ?>" name="<?php echo esc_attr($field['id']); ?>"
                                           placeholder="<?php echo sprintf(__('Enter %s', 'jobportal-framework'), $field['label']); ?>"
                                           value="<?php echo esc_attr($author_payout_custom); ?>">
                                </div>
                            </div>
                        </li>
                        <?php $name_nsame[] = $field['name'];
                    }
                endif;
            endforeach;
        endif; ?>
    </ul>
    <a href="#" class="jobportal-button" id="btn-submit-payout">
        <?php esc_html_e('Save', 'jobportal-framework') ?>
        <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
    </a>
</form>



