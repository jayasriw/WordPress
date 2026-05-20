<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$logo_email = jobportal_get_option('logo_email');
$title_email = jobportal_get_option('title_email');
$font = get_theme_mod('body_font_type');
$font_family = $font && isset($font['font-family']) ? $font['font-family'] : '';
?>
<?php if (!empty($logo_email) && !empty($title_email)) { ?>
    <table align="center"
           style="text-align: center;font-family: <?php echo $font_family; ?>; border-bottom:1px solid #eee; vertical-align: top; width: 600px; max-width: 600px; background-color: #ffffff;"
           width="600">
        <tbody>
        <tr>
            <td style="width: 596px; vertical-align: top; padding: 30px;" width="596">
                <?php if (!empty($logo_email)) { ?>
                    <img style="text-align: center; margin-bottom: 10px;margin-top: 20px;max-width: 80px;" alt="Logo"
                         src="<?php echo $logo_email; ?>" align="center">
                <?php } ?>
                <?php if (!empty($title_email)) { ?>
                    <h1><?php echo $title_email . ' ' . get_option('blogname'); ?></h1>
                <?php } ?>
            </td>
        </tr>
        </tbody>
    </table>
<?php } ?>


