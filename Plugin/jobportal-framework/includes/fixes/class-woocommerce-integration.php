<?php
/**
 * WooCommerce Integration Fix
 *
 * Override WooCommerce price formatting to use JobPortal theme options
 *
 * @package JobPortal_Framework
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class JobPortal_WooCommerce_Integration
 */
class JobPortal_WooCommerce_Integration
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('init', array($this, 'init_hooks'));
    }

    /**
     * Initialize hooks
     */
    public function init_hooks()
    {
        if (class_exists('WooCommerce')) {
            // Override WooCommerce price formatting
            add_filter('woocommerce_price_format', array($this, 'woocommerce_price_format'), 10, 2);
            add_filter('woocommerce_currency', array($this, 'woocommerce_currency'));
            add_filter('woocommerce_price_decimal_separator', array($this, 'woocommerce_decimal_separator'));
            add_filter('woocommerce_price_thousand_separator', array($this, 'woocommerce_thousand_separator'));
            add_filter('woocommerce_price_num_decimals', array($this, 'woocommerce_num_decimals'));

            // Override WooCommerce price display in templates
            add_filter('woocommerce_get_price_html', array($this, 'woocommerce_price_html'), 10, 2);
            add_filter('woocommerce_cart_item_price', array($this, 'woocommerce_cart_item_price'), 10, 3);
            add_filter('woocommerce_cart_item_subtotal', array($this, 'woocommerce_cart_item_subtotal'), 10, 3);
        }
    }

    /**
     * Override WooCommerce price format
     */
    public function woocommerce_price_format($format, $currency_pos)
    {
        if (function_exists('jobportal_get_option')) {
            $currency_position = jobportal_get_option('currency_position', 'before');
            if ($currency_position === 'after') {
                return '%2$s&nbsp;%1$s';
            } else {
                return '%1$s&nbsp;%2$s';
            }
        }
        return $format;
    }

    /**
     * Override WooCommerce currency
     */
    public function woocommerce_currency($currency)
    {
        if (function_exists('jobportal_get_option')) {
            $currency_code = jobportal_get_option('currency_type_default', 'USD');
            if (!empty($currency_code)) {
                return $currency_code;
            }
        }
        return $currency;
    }

    /**
     * Override WooCommerce decimal separator
     */
    public function woocommerce_decimal_separator($separator)
    {
        if (function_exists('jobportal_get_option')) {
            $decimal_separator = jobportal_get_option('decimal_separator', '.');
            if (empty($decimal_separator)) {
                $decimal_separator = '.';
            }
            return $decimal_separator;
        }
        return $separator;
    }

    /**
     * Override WooCommerce thousand separator
     */
    public function woocommerce_thousand_separator($separator)
    {
        if (function_exists('jobportal_get_option')) {
            $thousand_separator = jobportal_get_option('thousand_separator', ',');
            if (empty($thousand_separator)) {
                $thousand_separator = ' ';
            }
            return $thousand_separator;
        }
        return $separator;
    }

    /**
     * Override WooCommerce number of decimals
     */
    public function woocommerce_num_decimals($decimals)
    {
        if (function_exists('jobportal_get_option')) {
            $decimal_separator = jobportal_get_option('decimal_separator', '.');
            if (empty($decimal_separator)) {
                return 0;
            }
        }
        return $decimals;
    }

    /**
     * Override WooCommerce price HTML
     */
    public function woocommerce_price_html($price, $product)
    {
        if (function_exists('jobportal_get_format_money')) {
            $price_value = $product->get_price();
            if ($price_value > 0) {
                return jobportal_get_format_money($price_value, '', null, false);
            }
        }
        return $price;
    }

    /**
     * Override WooCommerce cart item price
     */
    public function woocommerce_cart_item_price($price, $cart_item, $cart_item_key)
    {
        if (function_exists('jobportal_get_format_money')) {
            $price_value = $cart_item['data']->get_price();
            if ($price_value > 0) {
                return jobportal_get_format_money($price_value, '', null, false);
            }
        }
        return $price;
    }

    /**
     * Override WooCommerce cart item subtotal
     */
    public function woocommerce_cart_item_subtotal($subtotal, $cart_item, $cart_item_key)
    {
        if (function_exists('jobportal_get_format_money')) {
            $price_value = $cart_item['data']->get_price() * $cart_item['quantity'];
            if ($price_value > 0) {
                return jobportal_get_format_money($price_value, '', null, false);
            }
        }
        return $subtotal;
    }
}

// Initialize the class
new JobPortal_WooCommerce_Integration();
