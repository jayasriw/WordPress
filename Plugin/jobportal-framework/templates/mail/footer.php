<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$mail_footer_user = jobportal_get_option('mail_footer_user');
$font = get_theme_mod('body_font_type');
$font_family = $font && isset($font['font-family']) ? $font['font-family'] : '';
?>
<?php if (!empty($mail_footer_user)) { ?>
    <table class="mail-footer" align="center"
           style="text-align: center;font-family: <?php echo $font_family; ?>;border-top:1px solid #eee; vertical-align: top; width: 600px; max-width: 600px;background-color: #ffffff;"
           width="600">
        <tbody>
        <tr>
            <td style="width: 596px; vertical-align: top; padding-left: 30px; padding-right: 30px; padding-top: 30px; padding-bottom: 30px;"
                width="596">
                <div class="content"><?php echo wp_kses_post($mail_footer_user); ?></div>
            </td>
        </tr>
        </tbody>
    </table>
<?php } ?>


