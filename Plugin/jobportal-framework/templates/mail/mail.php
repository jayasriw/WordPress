<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$font = get_theme_mod('body_font_type');
$font_family = $font && isset($font['font-family']) ? $font['font-family'] : 'inherit';
// Get accent color from theme settings, fallback to default green
$accent_color = get_theme_mod('accent_color', '#007456');
if (empty($accent_color)) {
    $accent_color = '#007456';
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <xml>
        <o:OfficeDocumentSettings>
            <o:AllowPNG />
            <o:PixelsPerInch></o:PixelsPerInch>
        </o:OfficeDocumentSettings>
    </xml>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <title>Newsletter</title>

    <style type="text/css">
        a,
        a[href],
        a:hover,
        a:link,
        a:visited {
            text-decoration: none !important;
            color: <?php echo esc_attr($accent_color); ?> !important;
        }

        .link {
            text-decoration: underline !important;
        }

        p,
        p:visited {
            text-align: center;
            font-size: 15px;
            margin-bottom: 5px;
            margin-top: 0;
            text-decoration: none;
            color: #555555;
            line-height: 2;
        }

        h1 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 0;
            text-decoration: none;
            color: <?php echo esc_attr($accent_color); ?>;
        }

        .mail-footer p {
            font-size: 14px;
        }

        .ExternalClass p,
        .ExternalClass span,
        .ExternalClass font,
        .ExternalClass td {
            line-height: 100%;
        }

        .ExternalClass {
            width: 100%;
        }
    </style>
</head>

<body style="text-align: center; margin: 0; padding-top: 10px; padding-bottom: 10px; padding-left: 0; padding-right: 0;
      -webkit-text-size-adjust: 100%;background-color: #f2f4f6; color: #000000;">
    <div style="text-align: center;">

        <?php jobportal_get_template('mail/header.php'); ?>

        <table align="center"
            style="text-align: left; font-family: <?php echo $font_family; ?>; vertical-align: top; width: 600px; max-width: 600px; background-color: #ffffff;"
            width="600">
            <tbody>
                <tr>
                    <td style="width: 596px; vertical-align: top;padding:30px;" width="596">
                        <?php if (!empty($content)) {
                            echo $content;
                        } ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php jobportal_get_template('mail/footer.php'); ?>

    </div>
</body>

</html>
