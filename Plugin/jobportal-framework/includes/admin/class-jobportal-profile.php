<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('JobPortal_Profile')) {

    /**
     * Class JobPortal_Profile
     */
    class JobPortal_Profile
    {

        public function custom_user_profile_fields($user)
        {
            $enable_paypal = jobportal_get_option('enable_payout_paypal');
            $enable_stripe = jobportal_get_option('enable_payout_stripe');
            $enable_bank = jobportal_get_option('enable_payout_bank_transfer');
            $enable_status = jobportal_get_option('enable_status_user');
            $custom_payout = jobportal_get_option('custom_payout_setting');
?>
            <h3><?php esc_html_e('Profile Info', 'jobportal-framework'); ?></h3>
            <table class="form-table">
                <tbody>
                    <tr class="author-avatar-image-wrap">
                        <th><label for="author_avatar_image_url"><?php echo esc_html__('Avatar', 'jobportal-framework'); ?></label></th>
                        <td>
                            <img class="show_author_avatar_image_url" src="<?php echo esc_attr(get_the_author_meta('author_avatar_image_url', $user->ID)); ?>" style="width: 96px;height: 96px; object-fit: cover;display: block;margin-bottom: 10px;">
                            <input type="text" name="author_avatar_image_url" id="author_avatar_image_url" value="<?php echo esc_attr(get_the_author_meta('author_avatar_image_url', $user->ID)); ?>" style="display: block;margin-bottom: 10px;max-width: 350px;width: 100%;">
                            <input type="hidden" name="author_avatar_image_id" id="author_avatar_image_id" value="<?php echo esc_attr(get_the_author_meta('author_avatar_image_id', $user->ID)); ?>">
                            <input type='button' class="button-primary" value="Upload Image" id="uploadimage" />
                        </td>
                    </tr>
                    <tr class="author-phone-number-wrap">
                        <th><label for="<?php echo esc_attr(JOBPORTAL_METABOX_PREFIX . 'author_mobile_number'); ?>"><?php echo esc_html__('Phone', 'jobportal-framework'); ?></label></th>
                        <td><input type="text" name="<?php echo esc_attr(JOBPORTAL_METABOX_PREFIX . 'author_mobile_number'); ?>" id="<?php echo esc_attr(JOBPORTAL_METABOX_PREFIX . 'author_mobile_number'); ?>" value="<?php echo esc_attr(get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'author_mobile_number', $user->ID)); ?>" class="regular-text"></td>
                    </tr>
                    <?php if ($enable_paypal === '1') : ?>
                        <tr class="author-payout-paypal-wrap">
                            <th><label for="<?php echo esc_attr(JOBPORTAL_METABOX_PREFIX . 'author_payout_paypal'); ?>"><?php echo esc_html__('Paypal email', 'jobportal-framework'); ?></label></th>
                            <td><input type="text" name="<?php echo esc_attr(JOBPORTAL_METABOX_PREFIX . 'author_payout_paypal'); ?>" id="<?php echo esc_attr(JOBPORTAL_METABOX_PREFIX . 'author_payout_paypal'); ?>" value="<?php echo esc_attr(get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'author_payout_paypal', $user->ID)); ?>" class="regular-text"></td>
                        </tr>
                    <?php endif; ?>
                    <?php if ($enable_stripe === '1') : ?>
                        <tr class="author-payout-stripe-wrap">
                            <th><label for="<?php echo esc_attr(JOBPORTAL_METABOX_PREFIX . 'author_payout_stripe'); ?>"><?php echo esc_html__('Stripe account', 'jobportal-framework'); ?></label></th>
                            <td><input type="text" name="<?php echo esc_attr(JOBPORTAL_METABOX_PREFIX . 'author_payout_stripe'); ?>" id="<?php echo esc_attr(JOBPORTAL_METABOX_PREFIX . 'author_payout_stripe'); ?>" value="<?php echo esc_attr(get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'author_payout_stripe', $user->ID)); ?>" class="regular-text"></td>
                        </tr>
                    <?php endif; ?>
                    <?php if ($enable_bank === '1') : ?>
                        <tr class="author-payout-card-number-wrap">
                            <th><label for="<?php echo esc_attr(JOBPORTAL_METABOX_PREFIX . 'author_payout_card_number'); ?>"><?php echo esc_html__('Card Number (Bank Transfer)', 'jobportal-framework'); ?></label></th>
                            <td><input type="text" name="<?php echo esc_attr(JOBPORTAL_METABOX_PREFIX . 'author_payout_card_number'); ?>" id="<?php echo esc_attr(JOBPORTAL_METABOX_PREFIX . 'author_payout_card_number'); ?>" value="<?php echo esc_attr(get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'author_payout_card_number', $user->ID)); ?>" class="regular-text"></td>
                        </tr>
                        <tr class="author-payout-card-name-wrap">
                            <th><label for="<?php echo esc_attr(JOBPORTAL_METABOX_PREFIX . 'author_payout_card_name'); ?>"><?php echo esc_html__('Card Name (Bank Transfer)', 'jobportal-framework'); ?></label></th>
                            <td><input type="text" name="<?php echo esc_attr(JOBPORTAL_METABOX_PREFIX . 'author_payout_card_name'); ?>" id="<?php echo esc_attr(JOBPORTAL_METABOX_PREFIX . 'author_payout_card_name'); ?>" value="<?php echo esc_attr(get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'author_payout_card_name', $user->ID)); ?>" class="regular-text"></td>
                        </tr>
                        <tr class="author-payout-bank-transfer-name-wrap">
                            <th><label for="<?php echo esc_attr(JOBPORTAL_METABOX_PREFIX . 'author_payout_bank_transfer_name'); ?>"><?php echo esc_html__('Bank Name (Bank Transfer)', 'jobportal-framework'); ?></label></th>
                            <td><input type="text" name="<?php echo esc_attr(JOBPORTAL_METABOX_PREFIX . 'author_payout_bank_transfer_name'); ?>" id="<?php echo esc_attr(JOBPORTAL_METABOX_PREFIX . 'author_payout_bank_transfer_name'); ?>" value="<?php echo esc_attr(get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'author_payout_bank_transfer_name', $user->ID)); ?>" class="regular-text"></td>
                        </tr>
                        <?php endif;
                    if (!empty($custom_payout)) :
                        foreach ($custom_payout as $field) :
                            if (!empty($field['name'])) : ?>
                                <tr class="author-payout-cusom">
                                    <th><label for="<?php echo esc_attr($field['id']); ?>"><?php echo sprintf(__('%1s (%2s)', 'jobportal-framework'), $field['label'], $field['name']); ?></label></th>
                                    <td><input type="text" name="<?php echo esc_attr($field['id']); ?>" id="<?php echo esc_attr($field['id']); ?>" value="<?php echo esc_attr(get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'author_payout_custom_' . $field['id'], $user->ID)); ?>" class="regular-text"></td>
                                </tr>
                    <?php endif;
                        endforeach;
                    endif; ?>
                    <?php if ($enable_status === '1') : ?>
                        <tr class="author-user-demo-wrap">
                            <?php $user_selected = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'user_status', $user->ID); ?>
                            <th><label for="<?php echo esc_attr(JOBPORTAL_METABOX_PREFIX . 'user_status'); ?>"><?php echo esc_html__('User Status', 'jobportal-framework'); ?></label></th>
                            <td>
                                <select name="<?php echo esc_attr(JOBPORTAL_METABOX_PREFIX . 'user_status'); ?>">
                                    <option <?php if ($user_selected == 'pending') { ?> selected <?php } ?> value="pending"><?php esc_html_e('Pending', 'jobportal-framework'); ?></option>
                                    <option <?php if ($user_selected == 'approve') { ?> selected <?php } ?> value="approve"><?php esc_html_e('Approve', 'jobportal-framework'); ?></option>
                                </select>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr class="author-user-demo-wrap">
                        <?php $user_selected = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'user_demo', $user->ID); ?>
                        <th><label for="<?php echo esc_attr(JOBPORTAL_METABOX_PREFIX . 'user_demo'); ?>"><?php echo esc_html__('User Demo', 'jobportal-framework'); ?></label></th>
                        <td>
                            <select name="<?php echo esc_attr(JOBPORTAL_METABOX_PREFIX . 'user_demo'); ?>">
                                <option <?php if ($user_selected == '') { ?> selected <?php } ?> value=""><?php esc_html_e('No', 'jobportal-framework'); ?></option>
                                <option <?php if ($user_selected == 'yes') { ?> selected <?php } ?> value="yes"><?php esc_html_e('Yes', 'jobportal-framework'); ?></option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php
        }

        public function user_package_available($user_id)
        {
            $package_id = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'package_id', $user_id);

            if (empty($package_id)) {
                return 0;
            }

            // Follow the exact logic from templates/dashboard/employer/user-package.php:113-138
            $args_invoice = array(
                'post_type'           => 'invoice',
                'posts_per_page'      => 1,
                'meta_query'          => array(
                    'relation' => 'AND',
                    array(
                        'key'     => JOBPORTAL_METABOX_PREFIX . 'invoice_user_id',
                        'value'   => $user_id,
                        'compare' => '='
                    ),
                    array(
                        'key'     => JOBPORTAL_METABOX_PREFIX . 'invoice_item_id',
                        'value'   => $package_id,
                        'compare' => '='
                    ),
                    array(
                        'key'     => JOBPORTAL_METABOX_PREFIX . 'invoice_payment_status',
                        'value'   => '1',
                        'compare' => '='
                    )
                ),
            );

            $args_invoice = apply_filters('jobportal_modify_author_query_args', $args_invoice);

            $data_invoice = new WP_Query($args_invoice);
            $invoice_status = '1';
            $invoice_id = 0;

            if (!empty($data_invoice->post)) {
                $invoice_id = $data_invoice->post->ID;
                $invoice_status = get_post_meta($invoice_id, JOBPORTAL_METABOX_PREFIX . 'invoice_payment_status', true);
            } else {
                return 0;
            }

            // Follow exact logic from user-package.php:139 - if invoice_status == 0, return 0
            if ($invoice_status == 0) {
                return 0;
            }

            $jobportal_package = new JobPortal_Package();
            $package_unlimited_time = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'package_unlimited_time', true);
            if ($package_unlimited_time == 0) {
                $expired_date = $jobportal_package->get_expired_time($package_id, $user_id);
                $today = time();
                if ($today > $expired_date) {
                    return -1;
                }
            }
            return 1;
        }

        public function update_custom_user_profile_fields($user_id)
        {
            global $current_user;
            wp_get_current_user();

            if (current_user_can('edit_user', $user_id)) {

                $author_avatar_image_url = isset($_POST['author_avatar_image_url']) ? jobportal_clean(wp_unslash($_POST['author_avatar_image_url'])) : '';
                $author_avatar_image_id  = isset($_POST['author_avatar_image_id']) ? jobportal_clean(wp_unslash($_POST['author_avatar_image_id'])) : '';
                $author_mobile_number    = isset($_POST[JOBPORTAL_METABOX_PREFIX . 'author_mobile_number']) ? jobportal_clean(wp_unslash($_POST[JOBPORTAL_METABOX_PREFIX . 'author_mobile_number'])) : '';
                $author_payout_paypal   = isset($_POST[JOBPORTAL_METABOX_PREFIX . 'author_payout_paypal']) ? jobportal_clean(wp_unslash($_POST[JOBPORTAL_METABOX_PREFIX . 'author_payout_paypal'])) : '';
                $author_payout_stripe   = isset($_POST[JOBPORTAL_METABOX_PREFIX . 'author_payout_stripe']) ? jobportal_clean(wp_unslash($_POST[JOBPORTAL_METABOX_PREFIX . 'author_payout_stripe'])) : '';
                $author_payout_card_number    = isset($_POST[JOBPORTAL_METABOX_PREFIX . 'author_payout_card_number']) ? jobportal_clean(wp_unslash($_POST[JOBPORTAL_METABOX_PREFIX . 'author_payout_card_number'])) : '';
                $author_payout_card_name   = isset($_POST[JOBPORTAL_METABOX_PREFIX . 'author_payout_card_name']) ? jobportal_clean(wp_unslash($_POST[JOBPORTAL_METABOX_PREFIX . 'author_payout_card_name'])) : '';
                $author_payout_bank_transfer_name  = isset($_POST[JOBPORTAL_METABOX_PREFIX . 'author_payout_bank_transfer_name']) ? jobportal_clean(wp_unslash($_POST[JOBPORTAL_METABOX_PREFIX . 'author_payout_bank_transfer_name'])) : '';
                $user_demo    = isset($_POST[JOBPORTAL_METABOX_PREFIX . 'user_demo']) ? jobportal_clean(wp_unslash($_POST[JOBPORTAL_METABOX_PREFIX . 'user_demo'])) : '';
                $user_status    = isset($_POST[JOBPORTAL_METABOX_PREFIX . 'user_status']) ? jobportal_clean(wp_unslash($_POST[JOBPORTAL_METABOX_PREFIX . 'user_status'])) : '';

                $old_user_status = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'user_status', true);

                update_user_meta($user_id, 'author_avatar_image_url', $author_avatar_image_url);
                update_user_meta($user_id, 'author_avatar_image_id', $author_avatar_image_id);
                update_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'author_mobile_number', $author_mobile_number);
                update_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'author_payout_paypal', $author_payout_paypal);
                update_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'author_payout_stripe', $author_payout_stripe);
                update_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'author_payout_card_number', $author_payout_card_number);
                update_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'author_payout_card_name', $author_payout_card_name);
                update_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'author_payout_bank_transfer_name', $author_payout_bank_transfer_name);
                update_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'user_demo', $user_demo);
                update_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'user_status', $user_status);
                if ($user_status !== $old_user_status) {
                    $user = get_userdata($user_id);
                    if ($user) {
                        $user_roles = (array) $user->roles;
                        $is_employer = in_array('jobportal_user_employer', $user_roles, true);
                        $candidate_post_id = get_user_meta($user_id, 'jobportal-cpt_id', true);
                        $company_post_id = get_user_meta($user_id, 'jobportal-company_id', true);

                        $this->sync_candidate_post_status($user_id, $user_status);
                        $this->sync_company_post_status($user_id, $user_status);

                        if ($user_status === 'approve') {
                            $user_email = $user->user_email;

                            if ($is_employer) {
                                if (empty($company_post_id)) {
                                    $args = array(
                                        'user_login' => $user->user_login,
                                        'company_title' => '',
                                        'company_url' => ''
                                    );
                                    jobportal_send_email($user_email, 'mail_approved_user_status_company', $args);
                                }
                            } else {
                                if (empty($candidate_post_id)) {
                                    $args = array(
                                        'user_login' => $user->user_login,
                                        'candidate_title' => '',
                                        'candidate_url' => ''
                                    );
                                    jobportal_send_email($user_email, 'mail_approved_user_status', $args);
                                }
                            }
                        }
                    }
                }
            }
        }

        private function sync_candidate_post_status($user_id, $user_status)
        {
            $candidate_post_id = get_user_meta($user_id, 'jobportal-cpt_id', true);
            if (!empty($candidate_post_id)) {
                $post_status = ($user_status === 'approve') ? 'publish' : 'pending';
                $post_data = array(
                    'ID' => $candidate_post_id,
                    'post_status' => $post_status
                );
                wp_update_post($post_data);
                update_post_meta($candidate_post_id, JOBPORTAL_METABOX_PREFIX . 'candidate_approval_status', $user_status);

                if ($user_status === 'approve') {
                    $user = get_userdata($user_id);
                    if ($user) {
                        $user_email = $user->user_email;
                        $user_roles = (array) $user->roles;
                        $is_employer = in_array('jobportal_user_employer', $user_roles, true);

                        if (!$is_employer) {
                            $args = array(
                                'user_login' => $user->user_login,
                                'candidate_title' => get_the_title($candidate_post_id),
                                'candidate_url' => get_permalink($candidate_post_id)
                            );
                            jobportal_send_email($user_email, 'mail_approved_user_status', $args);
                        }
                    }
                }

                error_log("Synced candidate post {$candidate_post_id} status to {$post_status} for user {$user_id}");
            }
        }

        private function sync_company_post_status($user_id, $user_status)
        {
            $company_post_id = get_user_meta($user_id, 'jobportal-company_id', true);
            if (!empty($company_post_id)) {
                $post_status = ($user_status === 'approve') ? 'publish' : 'pending';
                $post_data = array(
                    'ID' => $company_post_id,
                    'post_status' => $post_status
                );
                wp_update_post($post_data);
                update_post_meta($company_post_id, JOBPORTAL_METABOX_PREFIX . 'company_approval_status', $user_status);

                if ($user_status === 'approve') {
                    $user = get_userdata($user_id);
                    if ($user) {
                        $user_email = $user->user_email;
                        $args = array(
                            'user_login' => $user->user_login,
                            'company_title' => get_the_title($company_post_id),
                            'company_url' => get_permalink($company_post_id)
                        );
                        jobportal_send_email($user_email, 'mail_approved_user_status_company', $args);
                    }
                }

                error_log("Synced company post {$company_post_id} status to {$post_status} for user {$user_id}");
            }
        }

        function my_profile_upload_js()
        {
            wp_enqueue_media();
        ?>
            <script type="text/javascript">
                jQuery(document).ready(function() {

                    jQuery(document).find("input[id^='uploadimage']").on('click', function(e) {
                        e.preventDefault();

                        var button = jQuery(this),
                            custom_uploader = wp.media({
                                title: 'Insert image',
                                library: {
                                    type: 'image'
                                },
                                button: {
                                    text: 'Use this image'
                                },
                                multiple: false
                            }).on('select', function() {
                                var attachment = custom_uploader.state().get('selection').first().toJSON();
                                jQuery(button).removeClass('button').html('<img class="true_pre_image" src="' + attachment.url + '" style="max-width:95%;display:block;" />').next().val(attachment.id).next().show();
                                jQuery('#author_avatar_image_url').val(attachment.url);
                                jQuery('#author_avatar_image_id').val(attachment.id);
                                jQuery('.show_author_avatar_image_url').attr('src', attachment.url);
                            })
                            .open();
                    });
                });
            </script>
<?php
        }
    }
}
