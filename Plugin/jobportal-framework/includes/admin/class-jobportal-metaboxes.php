<?php
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('JobPortal_Metaboxes')) {
    /**
     * Class JobPortal_Metaboxes
     */
    class JobPortal_Metaboxes
    {
        /**
         * Meta boxes setup
         */
        public function meta_boxes_setup()
        {
            global $typenow;

            if ($typenow == 'user_package') {
                add_action('add_meta_boxes', array($this, 'render_user_package_meta_boxes'));
            }

            if ($typenow == 'invoice') {
                add_action('add_meta_boxes', array($this, 'render_invoice_meta_boxes'));
                add_action('save_post', array($this, 'save_invoices_metaboxes'), 10, 2);
            }

            if ($typenow == 'post') {
                add_action('save_post', array($this, 'save_post_metaboxes'), 10, 2);
            }
        }

        /**
         * Render agent package meta boxes
         */
        public function render_user_package_meta_boxes()
        {
            add_meta_box(
                JOBPORTAL_METABOX_PREFIX . 'user_package_metaboxes',
                esc_html__('Package Details', 'jobportal-framework'),
                array($this, 'user_package_meta'),
                array('user_package'),
                'normal',
                'default'
            );
        }


        /**
         * Agent package meta
         * @param $object
         */
        public function user_package_meta($object)
        {
            $postID = $object->ID;
            $package_user_id = get_post_meta($postID, JOBPORTAL_METABOX_PREFIX . 'package_user_id', true);
            $package_id = get_user_meta($package_user_id, JOBPORTAL_METABOX_PREFIX . 'package_id', true);
            $package_number_job = get_user_meta($package_user_id, JOBPORTAL_METABOX_PREFIX . 'package_number_job', true);
            $package_number_featured = get_user_meta($package_user_id, JOBPORTAL_METABOX_PREFIX . 'package_number_featured', true);
            $package_activate_date = get_user_meta($package_user_id, JOBPORTAL_METABOX_PREFIX . 'package_activate_date', true);
            $package_name = get_the_title($package_id);
            $user_info = get_userdata($package_user_id);
            $jobportal_package = new JobPortal_Package();
            $get_expired_date = $jobportal_package->get_expired_date($package_id, $package_user_id);
?>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label><?php esc_html_e('Buyer:', 'jobportal-framework'); ?></label></th>
                        <td><strong><?php if ($user_info) echo esc_attr($user_info->display_name); ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label><?php esc_html_e('Package:', 'jobportal-framework'); ?></label></th>
                        <td><strong><?php echo esc_attr($package_name); ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label><?php esc_html_e('Number Listings:', 'jobportal-framework'); ?></label>
                        </th>
                        <td><strong><?php echo esc_attr($package_number_job); ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label><?php esc_html_e('Number Featured Listings:', 'jobportal-framework'); ?></label>
                        </th>
                        <td><strong><?php echo esc_attr($package_number_featured); ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label><?php esc_html_e('Activate Date:', 'jobportal-framework'); ?></label></th>
                        <td><strong><?php echo esc_attr($package_activate_date); ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label><?php esc_html_e('Expires Date:', 'jobportal-framework'); ?></label></th>
                        <td><strong><?php echo esc_attr($get_expired_date); ?></strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php
        }

        /**
         * Verified Listing
         * @param $object
         */
        public function verified_listing($object)
        {
            wp_nonce_field(plugin_basename(__FILE__), 'jobportal_verified_listing_nonce_field');
            $verified_listing = get_post_meta($object->ID, JOBPORTAL_METABOX_PREFIX . 'verified_listing', true);
        ?>
            <div class="jobportal_meta_control custom_sidebar_js">
                <?php
                if ($verified_listing == 0 || $verified_listing == '') {
                    echo '<span class="label jobportal-label-red notice inline notice-warning notice-alt">' . esc_html__('Not verify', 'jobportal-framework') . '</span>';
                } else {
                    echo '<span class=" notice inline notice-success notice-alt">' . esc_html__('Verified', 'jobportal-framework') . '</span>';
                }

                ?>

                <?php if ($verified_listing == 0 || $verified_listing == '') { ?>
                    <div class="jobportal-set-item-paid">
                        <input type="checkbox" id="jobportal[jobportal_verified_listing]" name="jobportal[jobportal_verified_listing]" value="0" />
                        <label class="" for="jobportal[jobportal_verified_listing]"><?php esc_html_e('Tick the checkbox to mark it as Verified', 'jobportal-framework'); ?></label>

                    </div>
                <?php } ?>
            </div>
        <?php
        }

        /**
         * Render invoice meta boxes
         */
        public function render_invoice_meta_boxes()
        {
            add_meta_box(
                JOBPORTAL_METABOX_PREFIX . 'invoice_metaboxes',
                esc_html__('Invoice Details', 'jobportal-framework'),
                array($this, 'invoice_meta'),
                array('invoice'),
                'normal',
                'default'
            );

            add_meta_box(
                JOBPORTAL_METABOX_PREFIX . 'invoice_payment_status',
                esc_html__('Payment Status', 'jobportal-framework'),
                array($this, 'invoice_payment_status'),
                array('invoice'),
                'side',
                'high'
            );
        }

        /**
         * Invoice meta
         * @param $object
         */
        public function invoice_meta($object)
        {
            $jobportal_invoice = new JobPortal_Invoice();
            $jobportal_meta = $jobportal_invoice->get_invoice_meta($object->ID);
            $invoice_pdf_id = get_post_meta($object->ID, JOBPORTAL_METABOX_PREFIX . 'invoice_pdf_id', true);
            $attachment_url = wp_get_attachment_url($invoice_pdf_id);
        ?>
            <table class="form-table">
                <tbody>
                    <?php
                    if ($invoice_pdf_id) {
                    ?>
                        <tr>
                            <th scope="row"><?php esc_html_e('PDF Link:', 'jobportal-framework'); ?></th>
                            <td><a href="<?php echo esc_url($attachment_url); ?>" target="_blank"><?php esc_html_e('Download', 'jobportal-framework'); ?></a></td>
                        </tr>
                    <?php
                    }
                    ?>
                    <tr>
                        <th scope="row"><?php esc_html_e('Invoice ID:', 'jobportal-framework'); ?></th>
                        <td><strong><?php echo intval($object->ID); ?></strong></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Payment Method:', 'jobportal-framework'); ?></th>
                        <td>
                            <strong>
                                <?php echo JobPortal_Invoice::get_invoice_payment_method($jobportal_meta['invoice_payment_method']); ?>
                            </strong>
                        </td>
                    </tr>
                    <?php if (($jobportal_meta['invoice_payment_method'] == 'Stripe') || ($jobportal_meta['invoice_payment_method'] == 'Paypal')) : ?>
                        <tr>
                            <th scope="row"><?php esc_html_e('PaymentID (PayPal,Stripe):', 'jobportal-framework'); ?></th>
                            <td>
                                <strong>
                                    <?php echo esc_attr($jobportal_meta['trans_payment_id']); ?>
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('PayerID (PayPal,Stripe):', 'jobportal-framework'); ?></th>
                            <td>
                                <strong>
                                    <?php echo esc_attr($jobportal_meta['trans_payer_id']); ?>
                                </strong>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <th scope="row"><?php esc_html_e('Payment Type:', 'jobportal-framework'); ?></th>
                        <td>
                            <strong><?php echo $jobportal_meta['invoice_payment_type']; ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?php
                            if ($jobportal_meta['invoice_payment_type'] == 'Package') {
                                esc_html_e('Package ID:', 'jobportal-framework');
                            } else {
                                esc_html_e('Place ID:', 'jobportal-framework');
                            }
                            ?>
                        </th>
                        <td>
                            <strong><?php echo esc_attr($jobportal_meta['invoice_item_id']); ?></strong>
                            <?php
                            if ($jobportal_meta['invoice_payment_type'] == 'Package') {
                            ?>
                                <a href="<?php echo get_edit_post_link($jobportal_meta['invoice_item_id']) ?>"><?php esc_html_e('(Edit)', 'jobportal-framework'); ?></a>
                                <?php
                            } else {
                                if (current_user_can('read_place', $jobportal_meta['invoice_item_id'])) {
                                ?>
                                    <a href="<?php echo get_permalink($jobportal_meta['invoice_item_id']) ?>"><?php esc_html_e('(View)', 'jobportal-framework'); ?></a>
                                <?php
                                }
                                if (current_user_can('edit_place', $jobportal_meta['invoice_item_id'])) {
                                ?>
                                    <a href="<?php echo get_edit_post_link($jobportal_meta['invoice_item_id']) ?>"><?php esc_html_e('(Edit)', 'jobportal-framework'); ?></a>
                            <?php
                                }
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Item Price:', 'jobportal-framework'); ?></th>
                        <td>
                            <strong>
                                <?php
                                $item_price = jobportal_get_format_money($jobportal_meta['invoice_item_price']);
                                echo esc_attr($item_price);
                                ?>
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Purchase Date:', 'jobportal-framework'); ?>
                        </th>
                        <td>
                            <strong><?php echo esc_attr($jobportal_meta['invoice_purchase_date']); ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Buyer Name:', 'jobportal-framework'); ?></th>
                        <td>
                            <strong>
                                <?php
                                $user_info = get_userdata($jobportal_meta['invoice_user_id']);
                                if (current_user_can('edit_users') && $user_info) {
                                    echo '<a href="' . get_edit_user_link($jobportal_meta['invoice_user_id']) . '">' . esc_attr($user_info->display_name) . '</a>';
                                } else {
                                    if ($user_info) echo esc_attr($user_info->display_name);
                                }
                                ?>
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Buyer Email:', 'jobportal-framework'); ?></th>
                        <td>
                            <strong>
                                <?php if ($user_info) echo esc_attr($user_info->user_email); ?>
                            </strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php
        }

        /**
         * Invoice payment status
         * @param $object
         */
        public function invoice_payment_status($object)
        {
            wp_nonce_field(plugin_basename(__FILE__), 'jobportal_invoice_nonce_field');
            $payment_status = get_post_meta($object->ID, JOBPORTAL_METABOX_PREFIX . 'invoice_payment_status', true);
        ?>
            <div class="jobportal_meta_control custom_sidebar_js">
                <?php
                if ($payment_status == 0) {
                    echo '<span class="label jobportal-label-red notice inline notice-warning notice-alt">' . esc_html__('Pending', 'jobportal-framework') . '</span>';
                } else {
                    echo '<span class="label jobportal-label-blue notice inline notice-success notice-alt">' . esc_html__('Active', 'jobportal-framework') . '</span>';
                }
                if ($payment_status == 0) {
                ?>
                    <div class="jobportal-set-item-paid">
                        <input type="checkbox" id="jobportal[jobportal_payment_status]" name="jobportal[jobportal_payment_status]" value="0" />
                        <label class="label jobportal-label-blue" for="jobportal[jobportal_payment_status]"><?php esc_html_e('Set item active', 'jobportal-framework'); ?></label>
                    </div>
                <?php } ?>
            </div>
<?php
        }

        /**
         * Save property metaboxes
         * @param $post_id
         * @return bool
         */
        public function save_post_metaboxes($post_id)
        {
            if (!is_admin()) return false;
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
                return false;
            if (isset($_POST['jobportal']['jobportal_post_city'])) {
                $post_city = jobportal_clean(wp_unslash($_POST['jobportal']['jobportal_post_city']));
                update_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'post_city', $post_city);
            }
            return true;
        }

        /**
         * Save property metaboxes
         * @param $post_id
         * @param $post
         * @return bool
         */
        public function save_claim_listing_metaboxes($post_id, $post)
        {
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
                return false;
            if (!isset($_POST['jobportal_verified_listing_nonce_field']) || !wp_verify_nonce($_POST['jobportal_verified_listing_nonce_field'], plugin_basename(__FILE__))) {
                return false;
            }
            if ($post->post_type == 'place' && isset($_POST['jobportal'])) {
                if (isset($_POST['jobportal']['jobportal_verified_listing'])) {
                    update_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'verified_listing', 1);
                }
            }

            return true;
        }

        /**
         * Save invoices metaboxes
         * @param $post_id
         * @param $post
         * @return bool
         */
        public function save_invoices_metaboxes($post_id, $post)
        {
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
                return false;
            if (!isset($_POST['jobportal_invoice_nonce_field']) || !wp_verify_nonce($_POST['jobportal_invoice_nonce_field'], plugin_basename(__FILE__))) {
                return false;
            }
            if ($post->post_type == 'invoice' && isset($_POST['jobportal'])) {
                $post_type = get_post_type_object($post->post_type);
                if (!current_user_can($post_type->cap->edit_post, $post_id))
                    return false;
                if (isset($_POST['jobportal']['jobportal_payment_status'])) {
                    $jobportal_invoice = new JobPortal_Invoice();
                    $jobportal_meta = $jobportal_invoice->get_invoice_meta($post_id);
                    $user_id = $jobportal_meta['invoice_user_id'];
                    $user = get_user_by('id', $user_id);
                    $user_email = $user->user_email;
                    $admin_email = get_bloginfo('admin_email');
					if ($jobportal_meta['invoice_payment_type'] == 'Package') {
						$package_id = $jobportal_meta['invoice_item_id'];
						$jobportal_package = new JobPortal_Package();
						$invoice_pdf_id = get_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'invoice_pdf_id', true);
						$jobportal_package->insert_user_package($user_id, $package_id, $invoice_pdf_id);
						update_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'invoice_payment_status', 1);

						$package_price = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'package_price', true);
						$package_free = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'package_free', true);
						$payment_method = isset($jobportal_meta['invoice_payment_method']) ? $jobportal_meta['invoice_payment_method'] : '';

						if (floatval($package_price) > 0 && $package_free != 1 && $payment_method != 'Free_Package') {
							$attachment_url = wp_get_attachment_url($invoice_pdf_id);
							$args = array(
								'pdf_file' => $attachment_url,
							);
							jobportal_send_email($user_email, 'mail_activated_package', $args);
						}

                        $package_admin_args = array(
                            'user_login' => $user->user_login,
                            'user_email' => $user_email,
                            'package_id' => $package_id,
                        );

                        jobportal_send_email($admin_email, 'admin_mail_activated_package', $package_admin_args);
                    } else {
                        $jobs_id = $jobportal_meta['invoice_item_id'];
                        if ($jobportal_meta['invoice_payment_type'] == 'Listing') {
                            update_post_meta($jobs_id, JOBPORTAL_METABOX_PREFIX . 'payment_status', 'paid');
                            wp_update_post(array(
                                'ID' => $jobs_id,
                                'post_status' => 'publish',
                                'post_date' => current_time('mysql'),
                                'post_date_gmt' => current_time('mysql'),
                            ));
                            jobportal_send_email($user_email, 'mail_activated_listing');
                        } else if ($jobportal_meta['invoice_payment_type'] == 'Upgrade_To_Featured') {
                            update_post_meta($jobs_id, JOBPORTAL_METABOX_PREFIX . 'jobs_featured', 1);
                        } else if ($jobportal_meta['invoice_payment_type'] == 'Listing_With_Featured') {
                            update_post_meta($jobs_id, JOBPORTAL_METABOX_PREFIX . 'payment_status', 'paid');
                            update_post_meta($jobs_id, JOBPORTAL_METABOX_PREFIX . 'jobs_featured', 1);
                            wp_update_post(array(
                                'ID' => $jobs_id,
                                'post_status' => 'publish',
                                'post_date' => current_time('mysql'),
                                'post_date_gmt' => current_time('mysql'),
                            ));
                            jobportal_send_email($user_email, 'mail_activated_listing');
                        }
                        update_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'invoice_payment_status', 1);
                    }

                    $jobportal_admin_invoice = new JobPortal_Admin_Invoice();
                    $product_package = $jobportal_admin_invoice->get_product_by_name(get_the_title($package_id));
                    if ($product_package) {
                        wp_delete_post($product_package->ID);
                    }
                }
            }
            return true;
        }
    }
}
