<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$civi_service_payment = new Civi_service_payment();
$service_payment_method = isset($_GET['payment_method']) ? absint(wp_unslash($_GET['payment_method'])) : -1;
if ($service_payment_method == 1) {
    $civi_service_payment->paypal_payment_completed();
} elseif ($service_payment_method == 2) {
    $civi_service_payment->stripe_payment_completed();
}elseif ($service_payment_method == 4) {
    $civi_service_payment->razor_payment_completed();
}
?>
<div class="civi-payment-completed-wrap">
    <div class="inner-payment-completed">
        <?php
        do_action('civi_before_service_payment_completed');
        if (isset($_GET['order_id']) && $_GET['order_id'] != '') :
            $order_id = absint(wp_unslash($_GET['order_id']));
            $civi_service_order = new Civi_service_order();
            $order_meta = $civi_service_order->get_service_order_meta($order_id);

            // Check if order exists and has valid meta
            if (empty($order_meta) || !is_array($order_meta)) {
                $order_meta = array();
            }

            // Only show wire transfer info if payment method is wire transfer
            $payment_method = isset($order_meta['service_order_payment_method']) ? $order_meta['service_order_payment_method'] : '';
            $is_wire_transfer = ($payment_method === 'Wire_Transfer' || $payment_method === 'wire_transfer');

            $service_wire_transfer_card_number = $is_wire_transfer ? civi_get_option('service_wire_transfer_card_number', '') : '';
            $service_wire_transfer_card_name = $is_wire_transfer ? civi_get_option('service_wire_transfer_card_name', '') : '';
            $service_wire_transfer_bank_name = $is_wire_transfer ? civi_get_option('service_wire_transfer_bank_name', '') : '';
            $service_wire_transfer_bank_address = $is_wire_transfer ? civi_get_option('service_wire_transfer_bank_address', '') : '';
            $service_wire_transfer_swift_bic = $is_wire_transfer ? civi_get_option('service_wire_transfer_swift_bic', '') : '';
            $service_wire_transfer_iban = $is_wire_transfer ? civi_get_option('service_wire_transfer_iban', '') : '';
            $service_wire_transfer_routing_number = $is_wire_transfer ? civi_get_option('service_wire_transfer_routing_number', '') : '';
            $service_wire_transfer_info = $is_wire_transfer ? civi_get_option('service_wire_transfer_info', '') : '';
            ?>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2><?php esc_html_e('Thank you for your purchase!', 'civi-framework'); ?></h2>
                </div>
                <?php if ($is_wire_transfer) : ?>
                <p><?php esc_html_e('Please transfer to our account number with the "Order Number" and wait for us to confirm.', 'civi-framework'); ?></p>
                <?php endif; ?>

                <?php if ($is_wire_transfer && ($service_wire_transfer_card_number || $service_wire_transfer_card_name || $service_wire_transfer_bank_name || $service_wire_transfer_bank_address || $service_wire_transfer_swift_bic || $service_wire_transfer_iban || $service_wire_transfer_routing_number)) : ?>
                    <div class="card-info">
                        <h3><?php esc_html_e('Bank Account Information', 'civi-framework'); ?></h3>
                        <table>
                            <?php if ($service_wire_transfer_card_number) : ?>
                            <tr>
                                <th><?php esc_html_e('Account Number', 'civi-framework'); ?></th>
                                <td><?php echo esc_html($service_wire_transfer_card_number); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($service_wire_transfer_card_name) : ?>
                            <tr>
                                <th><?php esc_html_e('Account Holder Name', 'civi-framework'); ?></th>
                                <td><?php echo esc_html($service_wire_transfer_card_name); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($service_wire_transfer_bank_name) : ?>
                            <tr>
                                <th><?php esc_html_e('Bank Name', 'civi-framework'); ?></th>
                                <td><?php echo esc_html($service_wire_transfer_bank_name); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($service_wire_transfer_bank_address) : ?>
                            <tr>
                                <th><?php esc_html_e('Bank Address', 'civi-framework'); ?></th>
                                <td><?php echo nl2br(esc_html($service_wire_transfer_bank_address)); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($service_wire_transfer_swift_bic) : ?>
                            <tr>
                                <th><?php esc_html_e('SWIFT/BIC Code', 'civi-framework'); ?></th>
                                <td><?php echo esc_html($service_wire_transfer_swift_bic); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($service_wire_transfer_iban) : ?>
                            <tr>
                                <th><?php esc_html_e('IBAN', 'civi-framework'); ?></th>
                                <td><?php echo esc_html($service_wire_transfer_iban); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($service_wire_transfer_routing_number) : ?>
                            <tr>
                                <th><?php esc_html_e('Routing Number / ABA', 'civi-framework'); ?></th>
                                <td><?php echo esc_html($service_wire_transfer_routing_number); ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if ($service_wire_transfer_info) : ?>
                    <div class="wire-transfer-instructions">
                        <h3><?php esc_html_e('Additional Instructions', 'civi-framework'); ?></h3>
                        <div class="instructions-content">
                            <?php echo wp_kses_post($service_wire_transfer_info); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="entry-title">
                    <h3><?php esc_html_e('Order Detail', 'civi-framework'); ?></h3>
                </div>
                <ul class="list-group">
                    <li class="list-group-item">
                        <span><?php esc_html_e('Order Number', 'civi-framework'); ?></span>
                        <strong class="pull-right"><?php echo esc_html($order_id); ?></strong>
                    </li>
                    <li class="list-group-item">
                        <span><?php esc_html_e('Date', 'civi-framework'); ?></span>
                        <strong class="pull-right"><?php echo esc_html(get_the_date('', $order_id)); ?></strong>
                    </li>
                    <li class="list-group-item">
                        <span><?php esc_html_e('Payment Method', 'civi-framework'); ?></span>
                        <strong class="pull-right">
                            <?php
                            $payment_method_display = isset($order_meta['service_order_payment_method'])
                                ? Civi_service_order::get_service_order_payment_method($order_meta['service_order_payment_method'])
                                : esc_html__('N/A', 'civi-framework');
                            echo esc_html($payment_method_display);
                            ?>
                        </strong>
                    </li>
                    <li class="list-group-item">
                        <span><?php esc_html_e('Total', 'civi-framework'); ?></span>
                        <strong class="pull-right"><?php echo isset($order_meta['service_order_item_price']) ? civi_get_format_money($order_meta['service_order_item_price']) : ''; ?></strong>
                    </li>
                </ul>
            </div>
            <a href="<?php echo civi_get_permalink('dashboard'); ?>" class="civi-button"><?php esc_html_e('Go to Dashboard', 'civi-framework'); ?></a>
        <?php else : ?>
            <div class="civi-heading">
                <h2><?php esc_html_e('Thank you for your purchase', 'civi-framework'); ?></h2>
            </div>
            <div class="civi-thankyou-content">
                <?php $html_info = esc_html_e('I wanted to take a moment to express my heartfelt gratitude for the wonderful package you sent me. It truly brightened up my day and brought a smile to my face.', 'civi-framework');
                echo wpautop($html_info); ?>
            </div>
            <a href="<?php echo civi_get_permalink('dashboard'); ?>" class="civi-button"> <?php esc_html_e('Go to Dashboard', 'civi-framework'); ?> </a>
        <?php endif;
        do_action('civi_after_service_payment_completed');
        ?>
    </div>
</div>
