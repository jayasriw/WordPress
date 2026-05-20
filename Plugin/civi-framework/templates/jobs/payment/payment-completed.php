<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$civi_payment = new Civi_Payment();
$payment_method = isset($_GET['payment_method']) ? absint(wp_unslash($_GET['payment_method'])) : -1;
if ($payment_method == 1) {
    $civi_payment->paypal_payment_completed();
} elseif ($payment_method == 2) {
    $civi_payment->stripe_payment_completed();
}elseif ($payment_method == 4) {
    $civi_payment->razor_payment_completed();
}
?>
<div class="civi-payment-completed-wrap">
    <div class="inner-payment-completed">
        <?php
        do_action('civi_before_payment_completed');
        if (isset($_GET['order_id']) && $_GET['order_id'] != '') :
            $order_id = absint(wp_unslash($_GET['order_id']));
            $civi_invoice = new Civi_Invoice();
            $invoice_meta = $civi_invoice->get_invoice_meta($order_id);

            // Check if invoice exists and has valid meta
            if (empty($invoice_meta) || !is_array($invoice_meta)) {
                $invoice_meta = array();
            }

            // Only show wire transfer info if payment method is wire transfer
            $payment_method = isset($invoice_meta['invoice_payment_method']) ? $invoice_meta['invoice_payment_method'] : '';
            $is_wire_transfer = ($payment_method === 'Wire_Transfer' || $payment_method === 'wire_transfer');

            $wire_transfer_card_number = $is_wire_transfer ? civi_get_option('wire_transfer_card_number', '') : '';
            $wire_transfer_card_name = $is_wire_transfer ? civi_get_option('wire_transfer_card_name', '') : '';
            $wire_transfer_bank_name = $is_wire_transfer ? civi_get_option('wire_transfer_bank_name', '') : '';
            $wire_transfer_bank_address = $is_wire_transfer ? civi_get_option('wire_transfer_bank_address', '') : '';
            $wire_transfer_swift_bic = $is_wire_transfer ? civi_get_option('wire_transfer_swift_bic', '') : '';
            $wire_transfer_iban = $is_wire_transfer ? civi_get_option('wire_transfer_iban', '') : '';
            $wire_transfer_routing_number = $is_wire_transfer ? civi_get_option('wire_transfer_routing_number', '') : '';
            $wire_transfer_info = $is_wire_transfer ? civi_get_option('wire_transfer_info', '') : '';
        ?>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2><?php esc_html_e('Thank you for your purchase!', 'civi-framework'); ?></h2>
                </div>
                <?php if ($is_wire_transfer) : ?>
                <p><?php esc_html_e('Please transfer to our account number with the "Order Number" and wait for us to confirm.', 'civi-framework'); ?></p>
                <?php endif; ?>

                <?php if ($is_wire_transfer && ($wire_transfer_card_number || $wire_transfer_card_name || $wire_transfer_bank_name || $wire_transfer_bank_address || $wire_transfer_swift_bic || $wire_transfer_iban || $wire_transfer_routing_number)) : ?>
                    <div class="card-info">
                        <h3><?php esc_html_e('Bank Account Information', 'civi-framework'); ?></h3>
                        <table>
                            <?php if ($wire_transfer_card_number) : ?>
                            <tr>
                                <th><?php esc_html_e('Account Number', 'civi-framework'); ?></th>
                                <td><?php echo esc_html($wire_transfer_card_number); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($wire_transfer_card_name) : ?>
                            <tr>
                                <th><?php esc_html_e('Account Holder Name', 'civi-framework'); ?></th>
                                <td><?php echo esc_html($wire_transfer_card_name); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($wire_transfer_bank_name) : ?>
                            <tr>
                                <th><?php esc_html_e('Bank Name', 'civi-framework'); ?></th>
                                <td><?php echo esc_html($wire_transfer_bank_name); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($wire_transfer_bank_address) : ?>
                            <tr>
                                <th><?php esc_html_e('Bank Address', 'civi-framework'); ?></th>
                                <td><?php echo nl2br(esc_html($wire_transfer_bank_address)); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($wire_transfer_swift_bic) : ?>
                            <tr>
                                <th><?php esc_html_e('SWIFT/BIC Code', 'civi-framework'); ?></th>
                                <td><?php echo esc_html($wire_transfer_swift_bic); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($wire_transfer_iban) : ?>
                            <tr>
                                <th><?php esc_html_e('IBAN', 'civi-framework'); ?></th>
                                <td><?php echo esc_html($wire_transfer_iban); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($wire_transfer_routing_number) : ?>
                            <tr>
                                <th><?php esc_html_e('Routing Number / ABA', 'civi-framework'); ?></th>
                                <td><?php echo esc_html($wire_transfer_routing_number); ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if ($wire_transfer_info) : ?>
                    <div class="wire-transfer-instructions">
                        <h3><?php esc_html_e('Additional Instructions', 'civi-framework'); ?></h3>
                        <div class="instructions-content">
                            <?php echo wp_kses_post($wire_transfer_info); ?>
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
                            $payment_method_display = isset($invoice_meta['invoice_payment_method'])
                                ? Civi_Invoice::get_invoice_payment_method($invoice_meta['invoice_payment_method'])
                                : esc_html__('N/A', 'civi-framework');
                            echo esc_html($payment_method_display);
                            ?>
                        </strong>
                    </li>
                    <li class="list-group-item">
                        <span><?php esc_html_e('Payment Type', 'civi-framework'); ?></span>
                        <strong class="pull-right">
                            <?php echo esc_html(isset($invoice_meta['invoice_payment_type']) ? $invoice_meta['invoice_payment_type'] : ''); ?>
                        </strong>
                    </li>
                    <li class="list-group-item">
                        <span><?php esc_html_e('Total', 'civi-framework'); ?></span>
                        <strong class="pull-right"><?php echo isset($invoice_meta['invoice_item_price']) ? civi_get_format_money($invoice_meta['invoice_item_price']) : ''; ?></strong>
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
        do_action('civi_after_payment_completed');
        ?>
    </div>
</div>
