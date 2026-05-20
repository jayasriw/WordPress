<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$jobportal_candidate_payment = new JobPortal_candidate_payment();
$candidate_payment_method = isset($_GET['payment_method']) ? absint(wp_unslash($_GET['payment_method'])) : -1;
if ($candidate_payment_method == 1) {
    $jobportal_candidate_payment->paypal_payment_completed();
} elseif ($candidate_payment_method == 2) {
    $jobportal_candidate_payment->stripe_payment_completed();
}
elseif ($candidate_payment_method == 4) {
    $jobportal_candidate_payment->razor_candidate_payment_completed();
}
?>
<div class="jobportal-payment-completed-wrap">
    <div class="inner-payment-completed">
        <?php
        do_action('jobportal_before_candidate_payment_completed');
        if (isset($_GET['order_id']) && $_GET['order_id'] != '') :
            $order_id = absint(wp_unslash($_GET['order_id']));
            $jobportal_candidate_order = new JobPortal_candidate_order();
            $order_meta = $jobportal_candidate_order->get_candidate_order_meta($order_id);

            // Check if order exists and has valid meta
            if (empty($order_meta) || !is_array($order_meta)) {
                $order_meta = array();
            }

            // Only show wire transfer info if payment method is wire transfer
            $payment_method = isset($order_meta['candidate_order_payment_method']) ? $order_meta['candidate_order_payment_method'] : '';
            $is_wire_transfer = ($payment_method === 'Wire_Transfer' || $payment_method === 'wire_transfer');

            $candidate_wire_transfer_card_number = $is_wire_transfer ? jobportal_get_option('candidate_wire_transfer_card_number', '') : '';
            $candidate_wire_transfer_card_name = $is_wire_transfer ? jobportal_get_option('candidate_wire_transfer_card_name', '') : '';
            $candidate_wire_transfer_bank_name = $is_wire_transfer ? jobportal_get_option('candidate_wire_transfer_bank_name', '') : '';
            $candidate_wire_transfer_bank_address = $is_wire_transfer ? jobportal_get_option('candidate_wire_transfer_bank_address', '') : '';
            $candidate_wire_transfer_swift_bic = $is_wire_transfer ? jobportal_get_option('candidate_wire_transfer_swift_bic', '') : '';
            $candidate_wire_transfer_iban = $is_wire_transfer ? jobportal_get_option('candidate_wire_transfer_iban', '') : '';
            $candidate_wire_transfer_routing_number = $is_wire_transfer ? jobportal_get_option('candidate_wire_transfer_routing_number', '') : '';
            $candidate_wire_transfer_info = $is_wire_transfer ? jobportal_get_option('candidate_wire_transfer_info', '') : '';
            ?>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2><?php esc_html_e('Thank you for your purchase!', 'jobportal-framework'); ?></h2>
                </div>
                <?php if ($is_wire_transfer) : ?>
                <p><?php esc_html_e('Please transfer to our account number with the "Order Number" and wait for us to confirm.', 'jobportal-framework'); ?></p>
                <?php endif; ?>

                <?php if ($is_wire_transfer && ($candidate_wire_transfer_card_number || $candidate_wire_transfer_card_name || $candidate_wire_transfer_bank_name || $candidate_wire_transfer_bank_address || $candidate_wire_transfer_swift_bic || $candidate_wire_transfer_iban || $candidate_wire_transfer_routing_number)) : ?>
                    <div class="card-info">
                        <h3><?php esc_html_e('Bank Account Information', 'jobportal-framework'); ?></h3>
                        <table>
                            <?php if ($candidate_wire_transfer_card_number) : ?>
                            <tr>
                                <th><?php esc_html_e('Account Number', 'jobportal-framework'); ?></th>
                                <td><?php echo esc_html($candidate_wire_transfer_card_number); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($candidate_wire_transfer_card_name) : ?>
                            <tr>
                                <th><?php esc_html_e('Account Holder Name', 'jobportal-framework'); ?></th>
                                <td><?php echo esc_html($candidate_wire_transfer_card_name); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($candidate_wire_transfer_bank_name) : ?>
                            <tr>
                                <th><?php esc_html_e('Bank Name', 'jobportal-framework'); ?></th>
                                <td><?php echo esc_html($candidate_wire_transfer_bank_name); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($candidate_wire_transfer_bank_address) : ?>
                            <tr>
                                <th><?php esc_html_e('Bank Address', 'jobportal-framework'); ?></th>
                                <td><?php echo nl2br(esc_html($candidate_wire_transfer_bank_address)); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($candidate_wire_transfer_swift_bic) : ?>
                            <tr>
                                <th><?php esc_html_e('SWIFT/BIC Code', 'jobportal-framework'); ?></th>
                                <td><?php echo esc_html($candidate_wire_transfer_swift_bic); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($candidate_wire_transfer_iban) : ?>
                            <tr>
                                <th><?php esc_html_e('IBAN', 'jobportal-framework'); ?></th>
                                <td><?php echo esc_html($candidate_wire_transfer_iban); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($candidate_wire_transfer_routing_number) : ?>
                            <tr>
                                <th><?php esc_html_e('Routing Number / ABA', 'jobportal-framework'); ?></th>
                                <td><?php echo esc_html($candidate_wire_transfer_routing_number); ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if ($candidate_wire_transfer_info) : ?>
                    <div class="wire-transfer-instructions">
                        <h3><?php esc_html_e('Additional Instructions', 'jobportal-framework'); ?></h3>
                        <div class="instructions-content">
                            <?php echo wp_kses_post($candidate_wire_transfer_info); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="entry-title">
                    <h3><?php esc_html_e('Order Detail', 'jobportal-framework'); ?></h3>
                </div>
                <ul class="list-group">
                    <li class="list-group-item">
                        <span><?php esc_html_e('Order Number', 'jobportal-framework'); ?></span>
                        <strong class="pull-right"><?php echo esc_html($order_id); ?></strong>
                    </li>
                    <li class="list-group-item">
                        <span><?php esc_html_e('Date', 'jobportal-framework'); ?></span>
                        <strong class="pull-right"><?php echo esc_html(get_the_date('', $order_id)); ?></strong>
                    </li>
                    <li class="list-group-item">
                        <span><?php esc_html_e('Payment Method', 'jobportal-framework'); ?></span>
                        <strong class="pull-right">
                            <?php
                            $payment_method_display = isset($order_meta['candidate_order_payment_method'])
                                ? JobPortal_candidate_order::get_candidate_order_payment_method($order_meta['candidate_order_payment_method'])
                                : esc_html__('N/A', 'jobportal-framework');
                            echo esc_html($payment_method_display);
                            ?>
                        </strong>
                    </li>
                    <li class="list-group-item">
                        <span><?php esc_html_e('Total', 'jobportal-framework'); ?></span>
                        <strong class="pull-right"><?php echo isset($order_meta['candidate_order_item_price']) ? jobportal_get_format_money($order_meta['candidate_order_item_price']) : ''; ?></strong>
                    </li>
                </ul>
            </div>
            <a href="<?php echo jobportal_get_permalink('candidate_dashboard'); ?>" class="jobportal-button"><?php esc_html_e('Go to Dashboard', 'jobportal-framework'); ?></a>
        <?php else : ?>
            <div class="jobportal-heading">
                <h2><?php esc_html_e('Thank you for your purchase', 'jobportal-framework'); ?></h2>
            </div>
            <div class="jobportal-thankyou-content">
                <?php $html_info = esc_html_e('I wanted to take a moment to express my heartfelt gratitude for the wonderful package you sent me. It truly brightened up my day and brought a smile to my face.', 'jobportal-framework');
                echo wpautop($html_info); ?>
            </div>
            <a href="<?php echo jobportal_get_permalink('candidate_dashboard'); ?>" class="jobportal-button"> <?php esc_html_e('Go to Dashboard', 'jobportal-framework'); ?> </a>
        <?php endif;
        do_action('jobportal_after_candidate_payment_completed');
        ?>
    </div>
</div>
